#!/usr/bin/env bash
set -euo pipefail

HOST="127.0.0.1"
PORT="${RZX_DEV_PORT:-${1:-8000}}"

FREE_PORT="$(PORT="$PORT" php -r '$host="127.0.0.1"; $port=(int)getenv("PORT"); if ($port < 1) { $port = 8000; } for ($i = 0; $i < 20; $i++) { $candidate = $port + $i; $sock = @stream_socket_server("tcp://" . $host . ":" . $candidate, $errno, $errstr); if ($sock !== false) { fclose($sock); echo $candidate; exit(0); } } fwrite(STDERR, "No free port found\n"); exit(1);')"

echo "Starting PHP development server on ${HOST}:${FREE_PORT}"
exec php -S "${HOST}:${FREE_PORT}" -t public public/dev-server.php
