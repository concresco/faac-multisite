#!/usr/bin/env bash
# Multisite WP-Cron runner (bozza) — non applica modifiche ai siti, esegue solo gli eventi dovuti.
# Uso: BATCH_SIZE=5 TIMEOUT=120 /bin/bash run-wp-cron-multisite.sh
# Requisiti: wp-cli, php, permessi utente "runcloud".

set -Eeuo pipefail
umask 022

WP_PATH="/home/runcloud/webapps/FAAC"
PHP_BIN="/usr/local/lsws/lsphp81/bin/php"
WP_BIN="/usr/local/bin/wp"
LOCK_FILE="/tmp/faac-wpcron.lock"
OFFSET_FILE="/tmp/faac-wpcron.offset"

# Parametri con default sovrascrivibili da ambiente
BATCH_SIZE="${BATCH_SIZE:-5}"
TIMEOUT_SECS="${TIMEOUT:-120}"
NICE_VAL="${NICE:-10}"
IONICE_CLASS="${IONICE_CLASS:-2}"
IONICE_PRIO="${IONICE_PRIO:-7}"

# Comandi ausiliari
has_timeout=0
if command -v timeout >/dev/null 2>&1; then has_timeout=1; fi

# Lock per evitare overlap
if command -v flock >/dev/null 2>&1; then
  exec 9>"${LOCK_FILE}"
  if ! flock -n 9; then
    echo "[info] Another run is active; exiting"
    exit 0
  fi
else
  if [ -e "${LOCK_FILE}" ] && kill -0 "$(cat "${LOCK_FILE}" 2>/dev/null)" 2>/dev/null; then
    echo "[warn] Lock in place and process alive; exiting"
    exit 0
  fi
  echo $$ > "${LOCK_FILE}"
  trap 'rm -f "${LOCK_FILE}"' EXIT
fi

# Elenco siti
mapfile -t SITE_URLS < <("${PHP_BIN}" "${WP_BIN}" --path="${WP_PATH}" site list --field=url --format=csv 2>/dev/null | tail -n +2)
TOTAL=${#SITE_URLS[@]}
if [ "${TOTAL}" -eq 0 ]; then
  echo "[info] No sites found"
  exit 0
fi

OFFSET=0
if [ -f "${OFFSET_FILE}" ]; then
  OFFSET=$(cat "${OFFSET_FILE}" 2>/dev/null || echo 0)
fi
if ! [[ "${OFFSET}" =~ ^[0-9]+$ ]]; then OFFSET=0; fi

START=${OFFSET}
END=$(( START + BATCH_SIZE ))
if [ "${START}" -ge "${TOTAL}" ]; then START=0; END=${BATCH_SIZE}; fi

echo "[info] Total sites=${TOTAL} batch=${BATCH_SIZE} range=${START}..$((END-1)) timeout=${TIMEOUT_SECS}s"

for (( i=START; i<END && i<TOTAL; i++ )); do
  URL="${SITE_URLS[$i]}"
  echo "[run] ${URL}"
  if [ "${has_timeout}" -eq 1 ]; then
    nice -n "${NICE_VAL}" ionice -c "${IONICE_CLASS}" -n "${IONICE_PRIO}" \
      timeout "${TIMEOUT_SECS}" "${PHP_BIN}" "${WP_BIN}" --path="${WP_PATH}" cron event run --due-now --quiet --url="${URL}" || true
  else
    nice -n "${NICE_VAL}" ionice -c "${IONICE_CLASS}" -n "${IONICE_PRIO}" \
      "${PHP_BIN}" "${WP_BIN}" --path="${WP_PATH}" cron event run --due-now --quiet --url="${URL}" || true
  fi
done

# Aggiorna offset per round-robin
NEW_OFFSET=$(( END ))
if [ "${NEW_OFFSET}" -ge "${TOTAL}" ]; then NEW_OFFSET=0; fi
echo "${NEW_OFFSET}" > "${OFFSET_FILE}"

echo "[done] offset=${NEW_OFFSET}"


