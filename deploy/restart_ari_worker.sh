#!/bin/sh
set -eu

DEPLOY_PATH=${1:-}
WORKER="$DEPLOY_PATH/asterisk_ari_worker.php"
LOCK_FILE="$DEPLOY_PATH/data/asterisk_ari_worker.lock"
LOG_FILE="$DEPLOY_PATH/data/asterisk_ari_worker.log"

if [ -z "$DEPLOY_PATH" ] || [ ! -f "$WORKER" ]; then
    echo "LigFlow ARI worker deploy path is invalid." >&2
    exit 1
fi

if [ -s "$LOCK_FILE" ]; then
    PID=$(cat "$LOCK_FILE" 2>/dev/null || true)
    case "$PID" in
        ''|*[!0-9]*) PID='' ;;
    esac
    if [ -n "$PID" ] && [ -r "/proc/$PID/cmdline" ]; then
        COMMAND=$(tr '\000' ' ' < "/proc/$PID/cmdline")
        case "$COMMAND" in
            *"$WORKER"*)
                kill -TERM "$PID"
                ATTEMPTS=0
                while kill -0 "$PID" 2>/dev/null && [ "$ATTEMPTS" -lt 20 ]; do
                    sleep 1
                    ATTEMPTS=$((ATTEMPTS + 1))
                done
                if kill -0 "$PID" 2>/dev/null; then
                    echo "LigFlow ARI worker did not stop gracefully." >&2
                    exit 1
                fi
                ;;
        esac
    fi
fi

nohup env php "$WORKER" >> "$LOG_FILE" 2>&1 < /dev/null &
