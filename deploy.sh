#!/bin/bash
# Deploy catalog.beer to server
#
# Usage:
#   ./deploy.sh              Interactive mode (prompts for environment)
#   ./deploy.sh staging      Deploy to staging
#   ./deploy.sh production   Deploy to production
set -e

# Warn if there are uncommitted changes
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
		HOST="172.236.249.199"
		DEST="staging.catalog.beer"
		echo "Deploying to Staging..."
		;;
	production)
		HOST="172.233.129.106"
		DEST="catalog.beer"
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

REMOTE="michael@$HOST"
REMOTE_PATH="/var/www/html/$DEST/public_html"
SOCKET="/tmp/deploy-ssh-$$"

# Open a shared SSH connection to avoid multiple password prompts
ssh -fNM -S "$SOCKET" "$REMOTE"
trap 'ssh -S "$SOCKET" -O exit "$REMOTE" 2>/dev/null' EXIT

rsync -avzO --no-perms --delete \
	-e "ssh -S '$SOCKET'" \
	--exclude '.git' \
	--exclude '.claude' \
	--exclude '.nova' \
	--exclude '.gitignore' \
	--exclude '.gitattributes' \
	--exclude '.DS_Store' \
	--exclude 'CLAUDE.md' \
	--exclude 'deploy.sh' \
	--exclude '*.sql' \
	--exclude '*.p8' \
	--exclude 'classes/passwords.php' \
	--exclude 'classes/passwords.example.php' \
	--exclude 'classes/config.example.php' \
	--exclude 'maintenance.html' \
	--exclude 'README.md' \
	./ "$REMOTE:$REMOTE_PATH/"

# Set ownership and permissions so Apache can read/serve and michael can deploy
ssh -S "$SOCKET" -t "$REMOTE" "sudo chown -R www-data:developers $REMOTE_PATH/ && sudo find $REMOTE_PATH/ -type d -exec chmod 2775 {} + && sudo find $REMOTE_PATH/ -type f -exec chmod 664 {} +"

echo "Deploy to $DEST complete."
