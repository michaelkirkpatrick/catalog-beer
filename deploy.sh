#!/bin/bash
# Deploy catalog.beer to server
#
# Usage:
#   ./deploy.sh                       Interactive mode (prompts for environment)
#   ./deploy.sh staging               Deploy to staging
#   ./deploy.sh production            Deploy to production
#   ./deploy.sh production --dirty    Production from the working tree (emergency)
#
# Staging deploys the WORKING TREE: staging is the test surface, and code
# under test is routinely uncommitted.
#
# Production deploys the COMMITTED TREE (git archive HEAD): what is on
# production always corresponds to an exact commit, and a file that was never
# committed cannot be published. --dirty falls back to working-tree behavior
# for emergencies; the working-tree warnings below still apply to it.
#
# What ships, and where (the vhost-root layout, adopted here 2026-09-25):
#
#   public_html/  ->  /var/www/html/<vhost>/public_html/   Apache DocumentRoot
#   cron/         ->  /var/www/html/<vhost>/cron/          CLI only, never served
#
# Everything else in the repo -- the secrets common/, tests/, docs, this
# script -- lives beside those directories and is structurally unable to
# ship. common/passwords.php in particular is never deployed: it already exists
# on each server at /var/www/html/<vhost>/common/passwords.php, outside the web
# root, and deploys never touch it.
#
# ---------------------------------------------------------------------------
# Derived from a shared deploy template kept in the server-provisioning repo.
#
# If you improve something here that every project should have -- a safety
# check, a universal exclude, a bug fix -- update that template too, and flag
# the other projects for the same change. A fix that lives in only one project
# is how these scripts drift apart in the first place.
#
# Project-specific content (HOST/DEST, per-project excludes, vendored-library
# handling, extra deploy users) belongs here and NOT in the template.
# ---------------------------------------------------------------------------

# --- Deploy targets ---
#
# Server addresses, destination hostnames and the SSH user live in deploy.conf,
# which is gitignored. This script is public; the targets are not. Publishing a
# server IP next to a valid SSH username hands over half a credential.
#
# Copy deploy.conf.example to deploy.conf and fill it in.
CONF="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/deploy.conf"
if [[ ! -f "$CONF" ]]; then
	echo "ERROR: deploy.conf not found next to this script."
	echo "Copy deploy.conf.example to deploy.conf and fill in your targets."
	exit 1
fi
# shellcheck source=/dev/null
source "$CONF"
if [[ -z "$DEPLOY_USER" ]]; then
	echo "ERROR: DEPLOY_USER is not set in deploy.conf."
	exit 1
fi

# Run from the repo root regardless of where the script was invoked from: every
# path below (public_html/, cron/, the git calls) is relative to it.
cd "$(dirname "${BASH_SOURCE[0]}")" || exit 1

# The directories that ship, in deploy order. Each is mirrored to the same
# name under the vhost root on the server.
SHIP_DIRS=(public_html cron)

# --- Arguments ---
#
# Environment comes first so the source-selection logic below knows which
# rules apply. NONINTERACTIVE mirrors the old "$1 was given" convention:
# an explicit environment argument means no prompts, only aborts.
ENV=""
DIRTY=0
for arg in "$@"; do
	case "$arg" in
		--dirty) DIRTY=1 ;;
		staging|production) ENV="$arg" ;;
		*)
			echo "Unknown argument: $arg"
			echo "Usage: $0 [staging|production] [--dirty]"
			exit 1
			;;
	esac
done
NONINTERACTIVE=0
[[ -n "$ENV" ]] && NONINTERACTIVE=1

if [[ -z "$ENV" ]]; then
	echo "Deploy to which environment?"
	echo "  1) Staging"
	echo "  2) Production"
	read -p "Select (1 or 2): " choice
	case $choice in
		1) ENV="staging" ;;
		2) ENV="production" ;;
		*)
			echo "Invalid selection. Aborted."
			exit 1
			;;
	esac
fi

case $ENV in
	staging)
		HOST="$STAGING_HOST"
		DEST="$STAGING_DEST"
		;;
	production)
		HOST="$PRODUCTION_HOST"
		DEST="$PRODUCTION_DEST"
		if [[ $NONINTERACTIVE -eq 0 ]]; then
			read -p "Are you sure you want to deploy to Production? (y/n): " confirm
			if [[ $confirm != "y" ]]; then
				echo "Aborted."
				exit 0
			fi
		fi
		;;
esac
if [[ -z "$HOST" || -z "$DEST" ]]; then
	echo "ERROR: deploy.conf has no host/destination for '$ENV'."
	exit 1
fi

# Cleanup runs on every exit path: close the SSH master if one was opened,
# remove the archive export if one was made. Guarded so it is safe to call
# before either exists.
SOCKET=""
TMPSRC=""
cleanup() {
	[[ -n "$SOCKET" ]] && ssh -S "$SOCKET" -O exit "$REMOTE" 2>/dev/null
	[[ -n "$TMPSRC" ]] && rm -rf "$TMPSRC"
}
trap cleanup EXIT

# --- What does and does not ship ---
#
# One list, used twice: the preflight below asks rsync what this list would
# actually send, and the real transfers at the bottom use the same array.
# Defining it once is what lets the preflight tell "this file will be
# published" apart from "this file is excluded" without reimplementing rsync's
# pattern matching -- see the preflight for why imitating it is unsafe.
#
# The list applies to BOTH deploy modes and to EVERY transfer. Since the
# public_html/ split the rsync sources are the repo's public_html/ and cron/
# directories, so repo-level material (the secrets common/, tests/, scratch/,
# CLAUDE.md, deploy.sh itself) can no longer ship at all -- it lives outside
# the sources. What remains below is defense-in-depth for things that live or
# could stray INSIDE them. Patterns are matched relative to each transfer's
# root (public_html/ or cron/), not the repo root.
#
# Comments are legal inside an array literal. They are NOT legal inside a
# backslash-continued command: the continuation joins the comment onto the
# command and the '#' then swallows the rest of it, silently dropping every
# remaining argument including the source and destination. `bash -n` does not
# catch that. This array shape removes the footgun.
#
# Project-specific excludes belong in this array too. An exclude that can only
# be built later (one that probes the remote, say) has to be appended at the
# rsync call instead; the preflight then over-reports for those paths, which is
# the safe direction to be wrong in.
EXCLUDES=(
	--exclude '.git'
	--exclude '.claude'
	--exclude '.nova'
	--exclude '.gitignore'
	--exclude '.gitattributes'
	--exclude '.editorconfig'
	--exclude '.DS_Store'
	--exclude 'scratch/'
	--exclude 'deploy.sh'
	--exclude 'deploy.conf'
	--exclude 'deploy.conf.example'
	--exclude '*.sh'
	--exclude '*.sql'
	--exclude 'migrations/'
	--exclude 'maintenance.html'
	# The agent skill IS web content: it is served as markdown at
	# https://catalog.beer/skills/catalog-beer/SKILL.md, and the skill itself
	# advertises that URL as the place to fetch its current copy. This include
	# must stay ABOVE the *.md exclude -- rsync takes the first matching rule,
	# so an include below never fires. The failure is silent rather than loud:
	# excluded files are protected from --delete, so the copies already on the
	# server keep being served at whatever version they last deployed at.
	# That happened on 2026-08-03 -- the *.md exclude landed and catalog.beer
	# went on serving skill 1.4.0 while this repo held 1.5.0, with nothing
	# 404ing to give it away.
	--include 'skills/***'
	# Documentation is never web content. Excluding the extension rather than
	# naming each file means a doc added later is covered without anyone
	# remembering to come here. Anything that must be readable at runtime needs
	# its own --include above this line, the way skills/ does.
	--exclude '*.md'
	# The secrets file lives at the vhost root, outside both sources, so this
	# cannot match anything that exists. It stays as a tripwire: if a copy ever
	# strays back into public_html/config/, it still does not ship.
	--exclude 'config/passwords.php'
	# Generated ON the server by cron/generate-sitemap.php into the DocumentRoot.
	# A local copy is always staler than the server's; shipping it would silently
	# roll the live sitemap back, and without the exclude --delete would remove
	# it on every deploy. Excluding a path also protects it from --delete, which
	# is the point.
	--exclude 'sitemap*.xml'
	--exclude '*.p8'
	--exclude 'php-errors-*.txt'
)

# --- Source selection: committed tree for production, working tree otherwise ---
#
# Production exports `git archive HEAD` to a temp directory and rsyncs from
# that, so the deployed site always matches an exact commit and an uncommitted
# or untracked file structurally cannot be published. Staging rsyncs the
# working tree -- it is the test surface, and code under test is routinely
# uncommitted. See Project-Conventions.md § Deploy script (Linode repo).
#
# `git archive HEAD` exports the whole repo; only SHIP_DIRS are rsynced.
SRC_ROOT="."
if [[ "$ENV" == "production" && $DIRTY -eq 0 ]]; then
	if ! git rev-parse --git-dir >/dev/null 2>&1; then
		echo "ERROR: production deploys ship the committed tree, but this is not a git repo."
		exit 1
	fi
	# The trap in the test-on-staging workflow: you iterate on staging with
	# uncommitted changes, everything looks good, you deploy production --
	# and production silently gets the OLDER commit. Stop and ask.
	if [[ -n "$(git status --porcelain)" ]]; then
		echo "WARNING: your working tree differs from HEAD. Production ships HEAD:"
		git log -1 --oneline HEAD | sed 's/^/  /'
		echo "not these local changes:"
		git status --short | sed 's/^/  /'
		echo ""
		echo "If staging was tested WITH these changes, commit first -- otherwise"
		echo "production gets code older than what you tested."
		if [[ $NONINTERACTIVE -eq 1 ]]; then
			echo "Aborting non-interactive production deploy. Commit, or pass --dirty to ship the working tree."
			exit 1
		fi
		read -p "Ship HEAD anyway? (y/n): " confirm
		if [[ $confirm != "y" ]]; then
			echo "Aborted."
			exit 0
		fi
	fi
	TMPSRC=$(mktemp -d)
	if ! (set -o pipefail; git archive HEAD | tar -x -C "$TMPSRC"); then
		echo "ERROR: git archive export failed; nothing deployed."
		exit 1
	fi
	SRC_ROOT="$TMPSRC"
	echo "Deploying commit $(git rev-parse --short HEAD): $(git log -1 --format=%s HEAD)"
fi
for SRC_DIR in "${SHIP_DIRS[@]}"; do
	if [[ ! -d "$SRC_ROOT/$SRC_DIR" ]]; then
		echo "ERROR: expected $SRC_DIR/ in the deploy source; nothing deployed."
		exit 1
	fi
done

# --- Working-tree preflights (staging, and production --dirty) ---
#
# Skipped entirely in committed-tree mode: a git archive cannot contain
# uncommitted or untracked files, so there is nothing to warn about.
if [[ -z "$TMPSRC" ]]; then
	# Warn if there are uncommitted changes
	if ! git diff --quiet HEAD 2>/dev/null; then
		echo "WARNING: You have uncommitted changes."
		git status --short
		echo ""
		if [[ $NONINTERACTIVE -eq 1 ]]; then
			echo "Aborting non-interactive deploy due to uncommitted changes."
			exit 1
		fi
		read -p "Deploy anyway? (y/n): " confirm
		if [[ $confirm != "y" ]]; then
			echo "Aborted."
			exit 0
		fi
	fi

	# --- Preflight: untracked files are deployed too ---
	#
	# rsync copies the working tree, not the git index, so a file git has never
	# heard of is published exactly like a committed one. `git status` is not a
	# guide to what ships, and .gitignore has no bearing on rsync at all.
	#
	# This is not hypothetical -- a data export and an internal utility script,
	# both left in the deploy root while working on something, have ended up on
	# public URLs this way.
	#
	# Adding an --exclude afterwards does NOT clean up: rsync --delete deliberately
	# PROTECTS excluded files on the receiver, so anything already published stays
	# until someone removes it by hand. Catching it here is the cheap moment.
	#
	# Scratch work belongs in scratch/ (gitignored, and outside every source).
	#
	# Which untracked files actually ship is decided by ASKING RSYNC: a dry run
	# with the same EXCLUDES against an empty local directory, so no network and
	# no writes. Do not be tempted to filter the list with grep patterns mirroring
	# the excludes instead. That reimplements rsync's matching (anchoring, trailing
	# slashes, '**', --filter protect rules), and a bug there does not produce a
	# noisy warning -- it produces a silent one, suppressing the alert for a file
	# that really does publish. Loud and wrong is recoverable; quiet and wrong is
	# the incident this check exists to prevent. For the same reason, a dry run
	# that fails falls back to treating every untracked file as deployable.
	#
	# The dry run targets an empty directory, so it lists everything the excludes
	# allow rather than only what differs from the server. That over-reports
	# relative to a real incremental transfer, which is again the safe direction.
	#
	# Blind spot worth knowing: gitignored files are not listed here, because those
	# are the ones excluded from rsync on purpose. If you add a .gitignore entry
	# for something inside a shipped directory, add a matching exclude above or
	# it will deploy. (config/version.php is gitignored and DOES ship, on
	# purpose -- it is generated below.)
	#
	# Only the shipped directories matter. Each is checked against its own dry
	# run, with the directory prefix stripped so the paths line up with what
	# rsync reports.
	if git rev-parse --git-dir >/dev/null 2>&1; then
		for SRC_DIR in "${SHIP_DIRS[@]}"; do
			UNTRACKED=$(git ls-files --others --exclude-standard -- "$SRC_DIR" | sed "s|^$SRC_DIR/||" || true)
			[[ -z "$UNTRACKED" ]] && continue

			DRYRUN_DEST=$(mktemp -d)
			DRYRUN_STATUS=0
			DRYRUN_RAW=$(rsync -an --itemize-changes "${EXCLUDES[@]}" "$SRC_DIR/" "$DRYRUN_DEST/" 2>&1) || DRYRUN_STATUS=$?
			rm -rf "$DRYRUN_DEST"

			if [[ $DRYRUN_STATUS -ne 0 ]]; then
				echo "WARNING: could not work out what would ship from $SRC_DIR/ (rsync dry run failed):"
				echo "$DRYRUN_RAW"
				echo "Treating every untracked file as deployable."
				SHIPPABLE="$UNTRACKED"
			else
				SHIPPABLE=$(echo "$DRYRUN_RAW" | awk '/^[<>]f/ { sub(/^[^ ]+ +/, ""); print }')
			fi

			WILL_SHIP=$(comm -12 <(echo "$UNTRACKED" | LC_ALL=C sort) <(echo "$SHIPPABLE" | LC_ALL=C sort))
			WONT_SHIP=$(comm -23 <(echo "$UNTRACKED" | LC_ALL=C sort) <(echo "$SHIPPABLE" | LC_ALL=C sort))

			# Listed, not silenced: if an exclude is ever wrong, the file stays
			# visible here instead of dropping out of both checks at once.
			if [[ -n "$WONT_SHIP" ]]; then
				echo "Untracked in $SRC_DIR/, but excluded from deploy (will NOT be published):"
				echo "$WONT_SHIP" | sed "s|^|  $SRC_DIR/|"
				echo ""
			fi

			if [[ -n "$WILL_SHIP" ]]; then
				echo "WARNING: these files are NOT in git but WILL be deployed:"
				echo "$WILL_SHIP" | sed "s|^|  $SRC_DIR/|"
				echo ""
				if [[ $NONINTERACTIVE -eq 1 ]]; then
					echo "Aborting non-interactive deploy. Commit them, delete them, or move them to scratch/."
					exit 1
				fi
				read -p "Deploy them anyway? (y/n): " confirm
				if [[ $confirm != "y" ]]; then
					echo "Aborted."
					exit 0
				fi
			fi
		done
	fi
fi

# --- Version file ---
#
# Written into the deploy SOURCE (the working tree for staging, the archive
# export for production), so it ships with whichever tree is going out and
# /build-info reports exactly what was deployed. It is gitignored, so it never
# dirties the tree; in committed-tree mode the tree is HEAD by construction, so
# VERSION_DIRTY is false there.
VERSION_DIRTY=false
if [[ -z "$TMPSRC" ]] && ! git diff --quiet HEAD 2>/dev/null; then
	VERSION_DIRTY=true
fi
cat > "$SRC_ROOT/public_html/config/version.php" <<VEOF
<?php
// Generated by deploy.sh at $(date -u '+%Y-%m-%d %H:%M:%S UTC')
define('VERSION_COMMIT', '$(git rev-parse HEAD)');
define('VERSION_COMMIT_SHORT', '$(git rev-parse --short HEAD)');
define('VERSION_BRANCH', '$(git rev-parse --abbrev-ref HEAD)');
define('VERSION_TIMESTAMP', '$(date -u '+%Y-%m-%d %H:%M:%S UTC')');
define('VERSION_DIRTY', $VERSION_DIRTY);
?>
VEOF
echo "Generated config/version.php ($(git rev-parse --short HEAD))"

# Plain $ENV, not ${ENV^}: capitalization via ${var^} is bash 4 syntax and
# macOS ships bash 3.2 -- the substitution errors and kills this echo there.
echo "Deploying to $ENV..."

REMOTE="$DEPLOY_USER@$HOST"
VHOST_ROOT="/var/www/html/$DEST"
SOCKET="/tmp/deploy-ssh-$$"

# Open a shared SSH connection to avoid multiple password prompts
ssh -fNM -S "$SOCKET" "$REMOTE"

# The secrets file must already be at the vhost root, or every page will fail
# at the bootstrap the moment the transfer lands. On a 24.04 box that is still
# on the legacy layout (public_html/config/passwords.php), it has to be moved up
# by hand first (see common/Secrets.md § Server layout). Refusing before the
# transfer keeps a half-converted server off the table.
if ! ssh -S "$SOCKET" "$REMOTE" "test -f '$VHOST_ROOT/common/passwords.php'"; then
	echo "ERROR: $VHOST_ROOT/common/passwords.php does not exist on $HOST."
	echo "The site loads its secrets from the vhost root, outside public_html/."
	echo "Move (or create) the file there before deploying -- see common/Secrets.md."
	exit 1
fi

# A note on '*.sh': it covers deploy.sh itself plus any smoke-test or utility
# script. The root .htaccess also denies .sh, but excluding here keeps them off
# the server in the first place.
#
# Excludes live in the EXCLUDES array near the top of this script, so the
# preflight and these transfers are guaranteed to agree on what ships. Add
# project-specific excludes there, not here.
#
# Still true of the lines below: comments cannot go inside a backslash-continued
# command. The continuation joins the comment onto the command and the '#' then
# swallows the rest of it, silently dropping every remaining argument including
# the source and destination. `bash -n` does not catch this.
#
# One transfer per shipped directory. Each is a mirror of its own target
# (--delete), so a file removed locally is removed on the server too; no
# transfer can see or touch another's target, or the vhost root's common/ and
# logs/.
transfer() {
	local label="$1" src="$2" dest="$3"
	local output status transferred deleted tcount dcount

	output=$(rsync -azOi --no-perms --delete \
		-e "ssh -S '$SOCKET'" \
		"${EXCLUDES[@]}" \
		"$src" "$REMOTE:$dest/" 2>&1)
	status=$?

	if [ "$status" -ne 0 ]; then
		echo "rsync FAILED for $label (exit $status):"
		echo "$output"
		exit "$status"
	fi

	# Parse rsync --itemize-changes output. Works on GPL rsync 3+, rsync 2.6.9, and
	# Apple's openrsync. Each interesting line begins with an 11-char itemize string:
	#   >f.......... filename   (file being transferred to remote)
	#   *deleting    filename   (file being deleted on remote)
	#   .d..t...... ./          (unchanged directory -- skip)
	transferred=$(echo "$output" | awk '
		/^[<>]f/ { sub(/^[^ ]+ +/, ""); if ($0 !~ /\/$/) print }
	')
	deleted=$(echo "$output" | awk '
		/^\*deleting/ { sub(/^\*deleting +/, ""); if ($0 !~ /\/$/) print }
	')
	tcount=$(echo -n "$transferred" | grep -c . || true)
	dcount=$(echo -n "$deleted" | grep -c . || true)

	if [ "$tcount" -gt 0 ]; then
		echo "$label: transferred $tcount files:"
		echo "$transferred" | sed 's/^/  /'
	fi
	if [ "$dcount" -gt 0 ]; then
		echo "$label: deleted $dcount files:"
		echo "$deleted" | sed 's/^/  /'
	fi
	if [ "$tcount" -eq 0 ] && [ "$dcount" -eq 0 ]; then
		# Parser found nothing. If rsync produced any output beyond the headers,
		# surface it so silent reporting bugs don't lie about what happened.
		if [ -n "$output" ]; then
			echo "$label: no transfers/deletions parsed. Raw rsync output:"
			echo "$output"
		else
			echo "$label: no files changed."
		fi
	fi
}

REMOTE_DIRS=()
for SRC_DIR in "${SHIP_DIRS[@]}"; do
	transfer "$SRC_DIR" "$SRC_ROOT/$SRC_DIR/" "$VHOST_ROOT/$SRC_DIR"
	REMOTE_DIRS+=("$VHOST_ROOT/$SRC_DIR/")
done

# Set ownership and permissions so Apache can read/serve and the developers
# group can keep deploying. The deployed directories, and nothing else: the
# vhost root's common/ (secrets, 0640) and logs/ are deliberately left alone.
# No chmod-640 re-lock is needed: common/passwords.php lives OUTSIDE public_html,
# so nothing here touches it.
ssh -S "$SOCKET" -t "$REMOTE" "sudo chown -R www-data:developers ${REMOTE_DIRS[*]} && sudo find ${REMOTE_DIRS[*]} -type d -exec chmod 2775 {} + && sudo find ${REMOTE_DIRS[*]} -type f -exec chmod 664 {} +"

# The legacy layout kept the secrets inside the web root, at config/. The
# exclude above protects such a copy from --delete, so a server converted by
# moving the file (rather than by a fresh add-domain.sh) can be left with a
# stale duplicate that nothing reads but Apache could still be tricked into
# serving. Say so.
if ssh -S "$SOCKET" "$REMOTE" "test -e '$VHOST_ROOT/public_html/config/passwords.php'"; then
	echo ""
	echo "WARNING: a stale $VHOST_ROOT/public_html/config/passwords.php still exists on $HOST."
	echo "Nothing reads it any more; remove it (as root) so no secrets sit inside the web root."
fi

echo "Deploy to $DEST complete."
