#!/usr/bin/env bash
set -euo pipefail

usage() {
  cat <<USAGE
Usage: $(basename "$0") <environment>

Pull a remote WordPress database using SSH + WP-CLI, import it locally, and run
search-replace so URLs match the local environment.

Arguments:
  <environment>   Environment key defined in your .env file (e.g. prod, dev).

Environment configuration (read from .env):
  LOCAL_URL                        Local site URL after import (required)
  DBSYNC_<ENV>_SSH_HOST            SSH host for the remote server (required)
  DBSYNC_<ENV>_SSH_PORT            SSH port (optional, default 22)
  DBSYNC_<ENV>_REMOTE_USER         SSH username if not embedded in host (optional)
  DBSYNC_<ENV>_WP_PATH             Absolute path to the WordPress install on remote (required)
  DBSYNC_<ENV>_URL                 Canonical remote URL to replace (required)
  DBSYNC_<ENV>_REMOTE_EXPORT_ARGS  Extra flags for remote wp db export (optional)

Optional global toggles:
  DBSYNC_LOCAL_WP_PATH             Local WordPress path (default ".")
  DBSYNC_LOCAL_BACKUP_DIR          Where to store local pre-import backups (default "./db-sync/backups")
  DBSYNC_SKIP_LOCAL_BACKUP         Set to 1 to skip creating a local backup
  DBSYNC_KEEP_TEMP_DUMP            Set to 1 to keep the downloaded SQL dump
  DBSYNC_REMOTE_WP_CLI             Remote WP-CLI binary name (default "wp")
  DBSYNC_LOCAL_EXTRA_SEARCH        Space-separated list of extra search=>replace pairs
  DBSYNC_IS_MULTISITE              Set to 1 to run search-replace with --network

Search-replace automatically covers https/http/protocol-relative/bare host variants
and skips GUID + user identity columns and the users table.
Post-import steps flush caches and rewrite rules.
USAGE
}

if [[ ${1:-} == "--help" || ${1:-} == "-h" ]]; then
  usage
  exit 0
fi

ENV_FILE=${ENV_FILE:-.env}
if [[ ! -f "$ENV_FILE" ]]; then
  echo "\n[db-sync] Missing environment file: $ENV_FILE" >&2
  exit 1
fi

# shellcheck disable=SC1090
set -a
source "$ENV_FILE"
set +a

target=${1:-}
if [[ -z "$target" ]]; then
  echo "\n[db-sync] Missing environment argument." >&2
  usage
  exit 1
fi

upper_env=$(printf '%s' "$target" | tr '[:lower:]' '[:upper:]')

error() {
  echo "[db-sync] $1" >&2
  exit 1
}

fetch_env() {
  local suffix=$1
  local key="DBSYNC_${upper_env}_${suffix}"
  printf '%s' "${!key-}"
}

require_env() {
  local value=$1
  local message=$2
  if [[ -z "$value" ]]; then
    error "$message"
  fi
}

ssh_host=$(fetch_env SSH_HOST)
ssh_port=$(fetch_env SSH_PORT)
ssh_user=$(fetch_env REMOTE_USER)
remote_wp_path=$(fetch_env WP_PATH)
remote_url=$(fetch_env URL)
remote_export_args=$(fetch_env REMOTE_EXPORT_ARGS)
remote_wp_cli=${DBSYNC_REMOTE_WP_CLI:-wp}
local_url=${LOCAL_URL:-}
local_wp_path=${DBSYNC_LOCAL_WP_PATH:-.}
local_backup_dir=${DBSYNC_LOCAL_BACKUP_DIR:-./db-sync/backups}
skip_local_backup=${DBSYNC_SKIP_LOCAL_BACKUP:-0}
keep_temp_dump=${DBSYNC_KEEP_TEMP_DUMP:-0}
is_multisite=${DBSYNC_IS_MULTISITE:-0}
extra_pairs=${DBSYNC_LOCAL_EXTRA_SEARCH:-}
backup_file=""
auto_ops=0
extra_ops=0

require_env "$ssh_host" "DBSYNC_${upper_env}_SSH_HOST is not set in $ENV_FILE"
require_env "$remote_wp_path" "DBSYNC_${upper_env}_WP_PATH is not set in $ENV_FILE"
require_env "$remote_url" "DBSYNC_${upper_env}_URL is not set in $ENV_FILE"
require_env "$local_url" "LOCAL_URL is not set in $ENV_FILE"

ssh_port=${ssh_port:-22}

if ! command -v wp >/dev/null 2>&1; then
  error "wp CLI not found on PATH. Install WP-CLI before running this script."
fi

if ! command -v ssh >/dev/null 2>&1; then
  error "ssh command not available."
fi

if [[ -n "$ssh_user" ]]; then
  ssh_target="${ssh_user}@${ssh_host}"
else
  ssh_target="$ssh_host"
fi

printf '[db-sync] Pulling database from %s (remote URL: %s)\n' "$target" "$remote_url"

local_dump=$(mktemp "dbsync-${target}-XXXXXX.sql")
cleanup() {
  if [[ $keep_temp_dump -ne 1 ]]; then
    rm -f "$local_dump"
  else
    printf '[db-sync] Temporary dump kept at %s\n' "$local_dump"
  fi
}
trap cleanup EXIT

quote() {
  printf '%q' "$1"
}

remote_command="cd $(quote "$remote_wp_path") && $(quote "$remote_wp_cli") db export -"
if [[ -n "$remote_export_args" ]]; then
  remote_command+=" $remote_export_args"
fi

printf '[db-sync] Running remote export over SSH...\n'
ssh -p "$ssh_port" "$ssh_target" "$remote_command" > "$local_dump"

printf '[db-sync] Remote export complete → %s\n' "$local_dump"

if [[ "$skip_local_backup" != "1" ]]; then
  mkdir -p "$local_backup_dir"
  ts=$(date +%Y%m%d-%H%M%S)
  backup_file="$local_backup_dir/local-before-${ts}.sql"
  printf '[db-sync] Creating local backup at %s\n' "$backup_file"
  wp --path="$local_wp_path" db export "$backup_file"
fi

printf '[db-sync] Resetting local database...\n'
wp --path="$local_wp_path" db reset --yes

printf '[db-sync] Importing downloaded dump...\n'
wp --path="$local_wp_path" db import "$local_dump"

trim_trailing_slash() {
  local value=$1
  value=${value%/}
  printf '%s' "$value"
}

declare -a search_pairs
declare -a base_pairs
declare -a extra_pairs_summary

base_pair_exists() {
  local target=$1
  local entry
  if [[ ${#base_pairs[@]} -gt 0 ]]; then
    for entry in "${base_pairs[@]}"; do
      if [[ "$entry" == "$target" ]]; then
        return 0
      fi
    done
  fi
  return 1
}

format_pair_list() {
  local pairs=("$@")
  local formatted=()
  local pair old new
  if [[ ${#pairs[@]} -eq 0 ]]; then
    printf 'none'
    return
  fi
  for pair in "${pairs[@]}"; do
    old=${pair%%|*}
    new=${pair#*|}
    formatted+=("${old}→${new}")
  done
  (IFS=', '; printf '%s' "${formatted[*]}")
}

pair_exists() {
  local target=$1
  local entry
  if [[ ${#search_pairs[@]} -gt 0 ]]; then
    for entry in "${search_pairs[@]}"; do
      if [[ "$entry" == "$target" ]]; then
        return 0
      fi
    done
  fi
  return 1
}

add_pair() {
  local old=$(trim_trailing_slash "$1")
  local new=$(trim_trailing_slash "$2")
  local base="$old|$new"
  local trailing="${old}/|${new}/"

  if [[ -n "$old" && -n "$new" && "$old" != "$new" ]]; then
    if ! pair_exists "$base"; then
      search_pairs+=("$base")
    fi
    if base_pair_exists "$base"; then :; else base_pairs+=("$base"); fi
    if [[ ${old} != */ ]]; then
      if ! pair_exists "$trailing"; then
        search_pairs+=("$trailing")
      fi
    fi
  fi
}

remote_trim=$(trim_trailing_slash "$remote_url")
local_trim=$(trim_trailing_slash "$local_url")
remote_host=${remote_trim#*://}
remote_host=${remote_host%%/*}
local_host=${local_trim#*://}
local_host=${local_host%%/*}

add_pair "$remote_trim" "$local_trim"

if [[ "$remote_trim" == https://* ]]; then
  add_pair "http://${remote_trim#https://}" "$local_trim"
fi
if [[ "$remote_trim" == http://* ]]; then
  add_pair "https://${remote_trim#http://}" "$local_trim"
fi

if [[ -n "$remote_host" && -n "$local_host" ]]; then
  add_pair "//$remote_host" "//$local_host"
  add_pair "$remote_host" "$local_host"
fi

if [[ ${#search_pairs[@]} -eq 0 ]]; then
  error "No search-replace pairs generated. Check LOCAL_URL and DBSYNC_${upper_env}_URL."
fi

table_prefix=$(wp --path="$local_wp_path" db prefix | tr -d '\n')
if [[ -z "$table_prefix" ]]; then
  error "Unable to determine database table prefix."
fi
skip_columns="guid,user_email,user_login,user_nicename"
skip_tables_arg="--skip-tables=${table_prefix}users"
sr_common_args=("--skip-columns=$skip_columns" "$skip_tables_arg" "--precise")
if [[ "$is_multisite" == "1" ]]; then
  sr_common_args+=("--network")
fi

run_search_replace() {
  local old=$1
  local new=$2
  printf '[db-sync] Search-replace: %s → %s\n' "$old" "$new"
  wp --path="$local_wp_path" search-replace "$old" "$new" "${sr_common_args[@]}"
}

run_optional_wp() {
  local description=$1
  shift
  printf '[db-sync] %s...\n' "$description"
  if ! wp --path="$local_wp_path" "$@"; then
    printf '[db-sync] Notice: %s failed (wp %s)\n' "$description" "$*"
  fi
}

if [[ ${#search_pairs[@]} -gt 0 ]]; then
  for pair in "${search_pairs[@]}"; do
    old=${pair%%|*}
    new=${pair#*|}
    run_search_replace "$old" "$new"
    ((auto_ops++))
  done
fi

if [[ -n "$extra_pairs" ]]; then
  for pair in $extra_pairs; do
    old=${pair%%=>*}
    new=${pair#*=>}
    if [[ -n "$old" && -n "$new" && "$old" != "$new" ]]; then
      run_search_replace "$old" "$new"
      ((extra_ops++))
      extra_pairs_summary+=("${old}|${new}")
    fi
  done
fi

run_optional_wp "Flushing cache" cache flush
run_optional_wp "Deleting transients" transient delete --all
run_optional_wp "Flushing rewrite rules" rewrite flush --hard

auto_summary=$(format_pair_list "${base_pairs[@]}")
extra_summary=$(format_pair_list "${extra_pairs_summary[@]-}")
if [[ -n "$backup_file" ]]; then
  backup_status="saved to ${backup_file}"
elif [[ "$skip_local_backup" == "1" ]]; then
  backup_status="skipped (flagged)"
else
  backup_status="skipped"
fi
dump_status=$([[ $keep_temp_dump -eq 1 ]] && printf 'kept (%s)' "$local_dump" || printf 'removed on exit')
mode=$([[ "$is_multisite" == "1" ]] && printf 'multisite' || printf 'single-site')

printf '[db-sync] Summary:\n'
printf '  auto replacements (%d): %s\n' "${auto_ops}" "$auto_summary"
printf '  extra replacements (%d): %s\n' "${extra_ops}" "$extra_summary"
printf '  backup: %s\n' "$backup_status"
printf '  dump: %s\n' "$dump_status"
printf '  mode: %s\n' "$mode"

printf '[db-sync] Database sync complete.\n'
