#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
RUN_DIR="$ROOT_DIR/.vscode/.run"
WP_PID_FILE="$RUN_DIR/wp-serve.pid"
SASS_PID_FILE="$RUN_DIR/sass-watch.pid"

stop_pid_file() {
	local pid_file="$1"

	if [[ ! -f "$pid_file" ]]; then
		return
	fi

	local pid
	pid="$(cat "$pid_file")"

	if kill -0 "$pid" >/dev/null 2>&1; then
		kill "$pid" >/dev/null 2>&1 || true
	fi

	rm -f "$pid_file"
}

stop_pid_file "$WP_PID_FILE"
stop_pid_file "$SASS_PID_FILE"

for pid in $(lsof -tiTCP:8080 -sTCP:LISTEN 2>/dev/null); do
	kill "$pid" >/dev/null 2>&1 || true
done

pkill -f "php -S 127.0.0.1:8080" >/dev/null 2>&1 || true
pkill -f "sass --watch .*assets/scss/style.scss:.*assets/css/style.css" >/dev/null 2>&1 || true

echo "NUNLab dev environment stopped."
