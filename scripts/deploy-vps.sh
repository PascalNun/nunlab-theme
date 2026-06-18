#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

if [[ ! -f ".env.local" ]]; then
	echo "Missing .env.local. Add the VPS details there first."
	exit 1
fi

set -a
source .env.local
set +a

: "${NUNLAB_VPS_HOST:?Missing NUNLAB_VPS_HOST in .env.local}"
: "${NUNLAB_VPS_USER:?Missing NUNLAB_VPS_USER in .env.local}"
: "${NUNLAB_VPS_WP_ROOT:?Missing NUNLAB_VPS_WP_ROOT in .env.local}"

REMOTE_THEME_DIR="${NUNLAB_VPS_WP_ROOT}/wp-content/themes/nunlab-theme"
EXCLUDE_FILE="${ROOT_DIR}/scripts/deploy-vps.exclude"
SYNC_ONLY=0
SSH_OPTIONS=(-o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null)
REMOTE_SUDO=""

if [[ -n "${NUNLAB_VPS_SSH_KEY:-}" ]]; then
	SSH_OPTIONS+=(-i "$NUNLAB_VPS_SSH_KEY")
fi

if [[ "$NUNLAB_VPS_USER" != "root" ]]; then
	REMOTE_SUDO="sudo -n"
fi

if [[ "${1:-}" == "--sync-only" ]]; then
	SYNC_ONLY=1
fi

ssh_command() {
	ssh "${SSH_OPTIONS[@]}" "${NUNLAB_VPS_USER}@${NUNLAB_VPS_HOST}" "$@"
}

rsync_ssh_command() {
	printf 'ssh'

	for option in "${SSH_OPTIONS[@]}"; do
		printf ' %q' "$option"
	done
}

echo "Creating remote theme directory..."
ssh_command "${REMOTE_SUDO} mkdir -p $(printf '%q' "$REMOTE_THEME_DIR") && ${REMOTE_SUDO} chown -R $(printf '%q' "$NUNLAB_VPS_USER"):$(printf '%q' "$NUNLAB_VPS_USER") $(printf '%q' "$REMOTE_THEME_DIR")"

echo "Syncing theme files to ${REMOTE_THEME_DIR}..."
rsync -az --delete --exclude-from="${EXCLUDE_FILE}" -e "$(rsync_ssh_command)" "${ROOT_DIR}/" "${NUNLAB_VPS_USER}@${NUNLAB_VPS_HOST}:${REMOTE_THEME_DIR}/"

echo "Normalizing ownership and permissions..."
ssh_command "${REMOTE_SUDO} chown -R www-data:www-data $(printf '%q' "$REMOTE_THEME_DIR") && ${REMOTE_SUDO} find $(printf '%q' "$REMOTE_THEME_DIR") -type d -exec chmod 755 {} + && ${REMOTE_SUDO} find $(printf '%q' "$REMOTE_THEME_DIR") -type f -exec chmod 644 {} +"

if [[ "$SYNC_ONLY" -eq 1 ]]; then
	echo "Theme sync complete."
	exit 0
fi

echo "Applying WordPress live structure and activating theme..."
ssh_command "cd $(printf '%q' "$NUNLAB_VPS_WP_ROOT") && php" < "${ROOT_DIR}/scripts/bootstrap-live-wordpress.php"

echo "Deployment complete."
