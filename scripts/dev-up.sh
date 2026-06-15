#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
RUN_DIR="$ROOT_DIR/.vscode/.run"
WP_PID_FILE="$RUN_DIR/wp-serve.pid"
SASS_PID_FILE="$RUN_DIR/sass-watch.pid"
WP_LOG_FILE="$RUN_DIR/wp-serve.log"
SASS_LOG_FILE="$RUN_DIR/sass-watch.log"
SASS_BIN="$ROOT_DIR/node_modules/.bin/sass"

mkdir -p "$RUN_DIR"

is_wp_ready() {
	curl -fsI http://127.0.0.1:8080 >/dev/null 2>&1
}

is_sass_running() {
	pgrep -f "sass --watch .*assets/scss/style.scss:.*assets/css/style.css" >/dev/null 2>&1
}

start_wp_server() {
	if is_wp_ready; then
		echo "WordPress server already running on http://127.0.0.1:8080"
		return
	fi

	echo "Starting WordPress server..."
	nohup php -S 127.0.0.1:8080 -t "$ROOT_DIR/.wp-local" "$ROOT_DIR/scripts/wp-router.php" >"$WP_LOG_FILE" 2>&1 &
	echo $! >"$WP_PID_FILE"

	for _ in $(seq 1 40); do
		if is_wp_ready; then
			echo "WordPress server ready."
			return
		fi

		if ! kill -0 "$(cat "$WP_PID_FILE")" >/dev/null 2>&1; then
			break
		fi

		sleep 0.25
	done

	echo "WordPress server did not become ready. Check $WP_LOG_FILE" >&2
	exit 1
}

start_sass_watch() {
	if is_sass_running; then
		echo "Sass watcher already running."
		return
	fi

	if [[ ! -x "$SASS_BIN" ]]; then
		echo "Sass binary not found. Run npm install first." >&2
		exit 1
	fi

	echo "Starting Sass watcher..."
	nohup "$SASS_BIN" --watch "$ROOT_DIR/assets/scss/style.scss:$ROOT_DIR/assets/css/style.css" --style=expanded --no-source-map >"$SASS_LOG_FILE" 2>&1 &
	echo $! >"$SASS_PID_FILE"
	sleep 1

	if ! kill -0 "$(cat "$SASS_PID_FILE")" >/dev/null 2>&1; then
		echo "Sass watcher did not stay running. Check $SASS_LOG_FILE" >&2
		exit 1
	fi

	echo "Sass watcher ready."
}

start_wp_server
start_sass_watch

echo "N:UN dev environment is ready."
