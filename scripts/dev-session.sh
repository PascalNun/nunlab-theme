#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SASS_BIN="$ROOT_DIR/node_modules/.bin/sass"
WP_PID=""
SASS_PID=""

cleanup() {
	if [[ -n "$SASS_PID" ]] && kill -0 "$SASS_PID" >/dev/null 2>&1; then
		kill "$SASS_PID" >/dev/null 2>&1 || true
	fi

	if [[ -n "$WP_PID" ]] && kill -0 "$WP_PID" >/dev/null 2>&1; then
		kill "$WP_PID" >/dev/null 2>&1 || true
	fi

	bash "$ROOT_DIR/scripts/dev-down.sh" >/dev/null 2>&1 || true
}

trap cleanup EXIT INT TERM

bash "$ROOT_DIR/scripts/dev-down.sh" >/dev/null 2>&1 || true

if [[ ! -x "$SASS_BIN" ]]; then
	echo "Sass binary not found. Run npm install first." >&2
	exit 1
fi

echo "Starting WordPress server on http://127.0.0.1:8080"
php -S 127.0.0.1:8080 -t "$ROOT_DIR/.wp-local" "$ROOT_DIR/scripts/wp-router.php" &
WP_PID=$!

for _ in $(seq 1 40); do
	if curl -fsI http://127.0.0.1:8080 >/dev/null 2>&1; then
		break
	fi
	sleep 0.25
done

if ! curl -fsI http://127.0.0.1:8080 >/dev/null 2>&1; then
	echo "WordPress server did not become ready." >&2
	exit 1
fi

echo "Starting Sass watcher"
"$SASS_BIN" --watch "$ROOT_DIR/assets/scss/style.scss:$ROOT_DIR/assets/css/style.css" --style=expanded --no-source-map &
SASS_PID=$!

sleep 1

if ! kill -0 "$SASS_PID" >/dev/null 2>&1; then
	echo "Sass watcher did not stay running." >&2
	exit 1
fi

open "http://127.0.0.1:8080"
echo "N:UN dev session is running. Stop the debug session to shut it down."

wait "$WP_PID" "$SASS_PID"
