#!/bin/bash
# Deploy catalog.beer to server
#
# Usage:
#   ./deploy.sh              Interactive mode (prompts for environment)
#   ./deploy.sh staging      Deploy to staging
#   ./deploy.sh production   Deploy to production
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

# Warn if there are uncommitted changes
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
if ! git diff --quiet HEAD 2>/dev/null; then
	echo "WARNING: You have uncommitted changes."
	git status --short
	echo ""
	if [[ -n "$1" ]]; then
		echo "Aborting non-interactive deploy due to uncommitted changes."
		exit 1
	fi
	read -p "Deploy anyway? (y/n): " confirm
	if [[ $confirm != "y" ]]; then
		echo "Aborted."
		exit 0
	fi
fi

# --- What does and does not ship ---
#
# One list, used twice: the preflight below asks rsync what this list would
# actually send, and the real transfer at the bottom uses the same array.
# Defining it once is what lets the preflight tell "this file will be
# published" apart from "this file is excluded" without reimplementing rsync's
# pattern matching -- see the preflight for why imitating it is unsafe.
#
# Comments are legal inside an array literal. They are NOT legal inside a
# backslash-continued command: the continuation joins the comment onto the
# command and the '#' then swallows the rest of it, silently dropping every
# remaining argument including the source and destination. `bash -n` does not
# catch that. This array shape removes the footgun.
#
# Notes on why individual patterns are here live at the rsync call at the
# bottom, alongside the rest of the transfer commentary.
#
# Project-specific excludes belong in this array too. An exclude that can only
# be built later (one that probes the remote, say) has to be appended at the
# rsync call instead; the preflight then over-reports for those paths, which is
# the safe direction to be wrong in.
EXCLUDES=(
	--exclude '.git'
	--exclude '.claude'
	--exclude '.editorconfig'
	--exclude '.nova'
	--exclude '.gitignore'
	--exclude '.gitattributes'
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
	--exclude 'sitemap*.xml'
	--exclude '*.p8'
	--exclude 'php-errors-*.txt'
)

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
# Scratch work belongs in scratch/ (gitignored, and excluded from rsync above).
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
# are the ones excluded from rsync on purpose (the secrets include). If you add
# a .gitignore entry, add a matching exclude above or it will deploy.
if git rev-parse --git-dir >/dev/null 2>&1; then
	UNTRACKED=$(git ls-files --others --exclude-standard -- . || true)
	if [[ -n "$UNTRACKED" ]]; then
		DRYRUN_DEST=$(mktemp -d)
		DRYRUN_STATUS=0
		DRYRUN_RAW=$(rsync -an --itemize-changes "${EXCLUDES[@]}" ./ "$DRYRUN_DEST/" 2>&1) || DRYRUN_STATUS=$?
		rm -rf "$DRYRUN_DEST"

		if [[ $DRYRUN_STATUS -ne 0 ]]; then
			echo "WARNING: could not work out what would ship (rsync dry run failed):"
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
			echo "Untracked, but excluded from deploy (will NOT be published):"
			echo "$WONT_SHIP" | sed 's/^/  /'
			echo ""
		fi

		if [[ -n "$WILL_SHIP" ]]; then
			echo "WARNING: these files are NOT in git but WILL be deployed:"
			echo "$WILL_SHIP" | sed 's/^/  /'
			echo ""
			if [[ -n "$1" ]]; then
				echo "Aborting non-interactive deploy. Commit them, delete them, or move them to scratch/."
				exit 1
			fi
			read -p "Deploy them anyway? (y/n): " confirm
			if [[ $confirm != "y" ]]; then
				echo "Aborted."
				exit 0
			fi
		fi
	fi
fi

# Determine environment
if [[ -n "$1" ]]; then
	# CLI argument mode
	ENV="$1"
else
	# Interactive mode
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
		echo "Deploying to Staging..."
		;;
	production)
		HOST="$PRODUCTION_HOST"
		DEST="$PRODUCTION_DEST"
		if [[ -z "$1" ]]; then
			read -p "Are you sure you want to deploy to Production? (y/n): " confirm
			if [[ $confirm != "y" ]]; then
				echo "Aborted."
				exit 0
			fi
		fi
		echo "Deploying to Production..."
		;;
	*)
		echo "Unknown environment: $ENV"
		echo "Usage: $0 [staging|production]"
		exit 1
		;;
esac

REMOTE="$DEPLOY_USER@$HOST"
REMOTE_PATH="/var/www/html/$DEST/public_html"
SOCKET="/tmp/deploy-ssh-$$"

# Generate version file
DIRTY=false
if ! git diff --quiet HEAD 2>/dev/null; then
	DIRTY=true
fi
cat > config/version.php <<VEOF
<?php
// Generated by deploy.sh at $(date -u '+%Y-%m-%d %H:%M:%S UTC')
define('VERSION_COMMIT', '$(git rev-parse HEAD)');
define('VERSION_COMMIT_SHORT', '$(git rev-parse --short HEAD)');
define('VERSION_BRANCH', '$(git rev-parse --abbrev-ref HEAD)');
define('VERSION_TIMESTAMP', '$(date -u '+%Y-%m-%d %H:%M:%S UTC')');
define('VERSION_DIRTY', $DIRTY);
?>
VEOF
echo "Generated config/version.php ($(git rev-parse --short HEAD))"

# Open a shared SSH connection to avoid multiple password prompts
ssh -fNM -S "$SOCKET" "$REMOTE"
trap 'ssh -S "$SOCKET" -O exit "$REMOTE" 2>/dev/null' EXIT

# A note on '*.sh': it covers deploy.sh itself plus any smoke-test or utility
# script. The root .htaccess also denies .sh, but excluding here keeps them off
# the server in the first place.
#
# Excludes live in the EXCLUDES array near the top of this script, so the
# preflight and this transfer are guaranteed to agree on what ships. Add
# project-specific excludes there, not here.
#
# Still true of the line below: comments cannot go inside a backslash-continued
# command. The continuation joins the comment onto the command and the '#' then
# swallows the rest of it, silently dropping every remaining argument including
# the source and destination. `bash -n` does not catch this.
RSYNC_OUTPUT=$(rsync -azOi --no-perms --delete \
	-e "ssh -S '$SOCKET'" \
	"${EXCLUDES[@]}" \
	--filter 'P classes/htmlpurifier/HTMLPurifier/DefinitionCache/Serializer/***' \
	./ "$REMOTE:$REMOTE_PATH/" 2>&1)

RSYNC_STATUS=$?

if [ "$RSYNC_STATUS" -ne 0 ]; then
	echo "rsync FAILED (exit $RSYNC_STATUS):"
	echo "$RSYNC_OUTPUT"
	exit "$RSYNC_STATUS"
fi

# Parse rsync --itemize-changes output. Works on GPL rsync 3+, rsync 2.6.9, and
# Apple's openrsync. Each interesting line begins with an 11-char itemize string:
#   >f.......... filename   (file being transferred to remote)
#   *deleting    filename   (file being deleted on remote)
#   .d..t...... ./          (unchanged directory — skip)
TRANSFERRED=$(echo "$RSYNC_OUTPUT" | awk '
	/^[<>]f/ { sub(/^[^ ]+ +/, ""); if ($0 !~ /\/$/) print }
')
DELETED=$(echo "$RSYNC_OUTPUT" | awk '
	/^\*deleting/ { sub(/^\*deleting +/, ""); if ($0 !~ /\/$/) print }
')

TRANSFER_COUNT=$(echo -n "$TRANSFERRED" | grep -c . || true)
DELETE_COUNT=$(echo -n "$DELETED" | grep -c . || true)

if [ "$TRANSFER_COUNT" -gt 0 ]; then
	echo "Transferred $TRANSFER_COUNT files:"
	echo "$TRANSFERRED"
fi
if [ "$DELETE_COUNT" -gt 0 ]; then
	echo "Deleted $DELETE_COUNT files:"
	echo "$DELETED"
fi
if [ "$TRANSFER_COUNT" -eq 0 ] && [ "$DELETE_COUNT" -eq 0 ]; then
	# Parser found nothing. If rsync produced any output beyond the headers,
	# surface it so silent reporting bugs don't lie about what happened.
	if [ -n "$RSYNC_OUTPUT" ]; then
		echo "No transfers/deletions parsed. Raw rsync output:"
		echo "$RSYNC_OUTPUT"
	else
		echo "No files changed."
	fi
fi

# Set ownership and permissions so Apache can read/serve and michael can deploy.
# config/passwords.php is locked down to 600 (owner-only) since it holds secrets.
ssh -S "$SOCKET" -t "$REMOTE" "sudo chown -R www-data:developers $REMOTE_PATH/ && sudo find $REMOTE_PATH/ -type d -exec chmod 2775 {} + && sudo find $REMOTE_PATH/ -type f -exec chmod 664 {} + && sudo chmod 600 $REMOTE_PATH/config/passwords.php"

echo "Deploy to $DEST complete."
