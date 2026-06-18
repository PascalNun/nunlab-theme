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

REMOTE_SCRIPT="/usr/local/bin/nunlab-analytics-summary"
REMOTE_SCRIPT_STAGING="/tmp/nunlab-analytics-summary"
REMOTE_OUTPUT_DIR="${NUNLAB_ANALYTICS_DIR:-/var/lib/nunlab-analytics}"
REMOTE_OUTPUT="${REMOTE_OUTPUT_DIR}/summary.json"
DAYS="${NUNLAB_ANALYTICS_DAYS:-30}"
SITES_STRING="${NUNLAB_ANALYTICS_SITES:-pascalnun.eu www.pascalnun.eu nun.archi www.nun.archi ${NUNLAB_VPS_HOST}}"
SITE_ARGS=()
SSH_OPTIONS=(-o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null)
REMOTE_SUDO=""

if [[ -n "${NUNLAB_VPS_SSH_KEY:-}" ]]; then
	SSH_OPTIONS+=(-i "$NUNLAB_VPS_SSH_KEY")
fi

if [[ "$NUNLAB_VPS_USER" != "root" ]]; then
	REMOTE_SUDO="sudo -n"
fi

for site in $SITES_STRING; do
	SITE_ARGS+=( "--site" "$site" )
done

printf -v SITE_ARGS_QUOTED ' %q' "${SITE_ARGS[@]}"
GENERATOR_COMMAND="${REMOTE_SCRIPT} --log-dir /var/log/nginx --output $(printf '%q' "$REMOTE_OUTPUT") --days $(printf '%q' "$DAYS")${SITE_ARGS_QUOTED} --owner-user root --owner-group www-data"
CRON_LINE="*/30 * * * * root ${GENERATOR_COMMAND} >/dev/null 2>&1"

ssh_command() {
	ssh "${SSH_OPTIONS[@]}" "${NUNLAB_VPS_USER}@${NUNLAB_VPS_HOST}" "$@"
}

scp_command() {
	scp "${SSH_OPTIONS[@]}" "$@"
}

run_with_password() {
	local command_string="$1"

	if [[ -z "${NUNLAB_VPS_ROOT_PASSWORD:-}" ]]; then
		/bin/sh -lc "$command_string"
		return
	fi

	EXPECT_COMMAND="$command_string" EXPECT_PASSWORD="$NUNLAB_VPS_ROOT_PASSWORD" expect <<'EOF'
log_user 1
set timeout -1
set command_string $env(EXPECT_COMMAND)
set password $env(EXPECT_PASSWORD)

spawn /bin/sh -lc $command_string

expect {
	-re "(?i)password:" {
		send "$password\r"
		exp_continue
	}
	eof
}

catch wait result
set exit_status [lindex $result 3]
exit $exit_status
EOF
}

echo "Installing analytics generator on the VPS..."
scp_command "${ROOT_DIR}/scripts/analytics/generate-summary.py" "${NUNLAB_VPS_USER}@${NUNLAB_VPS_HOST}:${REMOTE_SCRIPT_STAGING}"

REMOTE_PROVISION="$(
	cat <<EOF
set -euo pipefail
apt-get update
DEBIAN_FRONTEND=noninteractive apt-get install -y geoip-bin geoip-database
install -o root -g root -m 0755 $(printf '%q' "$REMOTE_SCRIPT_STAGING") $(printf '%q' "$REMOTE_SCRIPT")
rm -f $(printf '%q' "$REMOTE_SCRIPT_STAGING")
install -d -o root -g www-data -m 0750 $(printf '%q' "$REMOTE_OUTPUT_DIR")
${GENERATOR_COMMAND}
cat > /etc/cron.d/nunlab-analytics <<'CRON'
SHELL=/bin/bash
PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin
${CRON_LINE}
CRON
chmod 0644 /etc/cron.d/nunlab-analytics
EOF
)"

REMOTE_PROVISION_B64="$(printf '%s' "$REMOTE_PROVISION" | base64 | tr -d '\n')"

echo "Provisioning cron and generating the first summary..."
ssh_command "printf '%s' '${REMOTE_PROVISION_B64}' | base64 -d | ${REMOTE_SUDO} bash -s"

echo "Analytics setup complete. WordPress will read ${REMOTE_OUTPUT}."
