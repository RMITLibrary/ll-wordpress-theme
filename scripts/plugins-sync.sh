#!/usr/bin/env bash
set -euo pipefail

usage() {
  cat <<USAGE
Usage: $(basename "$0") <environment> [--dry-run]

Sync remote WordPress plugins to the local environment via rsync over SSH.

Arguments:
  <environment>   Target environment key matching DBSYNC_<ENV>_* vars in .env
  --dry-run       Perform rsync dry run without modifying local files

Environment configuration (read from .env):
  DBSYNC_<ENV>_SSH_HOST            Remote SSH host (required)
  DBSYNC_<ENV>_SSH_PORT            Remote SSH port (optional, default 22)
  DBSYNC_<ENV>_REMOTE_USER         Remote SSH user (optional)
  DBSYNC_<ENV>_WP_PATH             Remote WordPress root path (required)
  DBSYNC_<ENV>_PLUGIN_PATH         Remote plugins path override (optional)
  DBSYNC_PLUGIN_LOCAL_PATH         Local plugins path (default "wp-content/plugins")
  DBSYNC_PLUGIN_DELETE             Set 1 to pass --delete to rsync (default 0)
  DBSYNC_PLUGIN_EXCLUDES           Space-separated rsync exclude patterns (optional)
  DBSYNC_PLUGIN_RSYNC_FLAGS        Extra flags appended to rsync command (optional)
  DBSYNC_PLUGIN_RUN_WP_CMDS        Set 1 (default) to flush caches/permalinks after sync

Note: This will overwrite local plugin files to match remote. Avoid using it if
local-only plugin customisations exist unless you have backups.
USAGE
}

if [[ ${1:-} == "--help" || ${1:-} == "-h" ]]; then
  usage
  exit 0
fi

ENV_FILE=${ENV_FILE:-.env}
if [[ ! -f "$ENV_FILE" ]]; then
  echo "[plugin-sync] Missing environment file: $ENV_FILE" >&2
  exit 1
fi

set -a
# shellcheck disable=SC1090
source "$ENV_FILE"
set +a

target=${1:-}
if [[ -z "$target" ]]; then
  echo "[plugin-sync] Missing environment argument." >&2
  usage
  exit 1
fi

shift
DRY_RUN=0
if [[ ${1:-} == "--dry-run" ]]; then
  DRY_RUN=1
  shift
fi

upper_env=$(printf '%s' "$target" | tr '[:lower:]' '[:upper:]')

fetch_env() {
  local suffix=$1
  local key="DBSYNC_${upper_env}_${suffix}"
  printf '%s' "${!key-}"
}

require_env() {
  local value=$1
  local message=$2
  if [[ -z "$value" ]]; then
    echo "[plugin-sync] $message" >&2
    exit 1
  fi
}

ssh_host=$(fetch_env SSH_HOST)
ssh_port=$(fetch_env SSH_PORT)
ssh_user=$(fetch_env REMOTE_USER)
remote_wp_path=$(fetch_env WP_PATH)
remote_plugins_override=$(fetch_env PLUGIN_PATH)
plugin_delete=${DBSYNC_PLUGIN_DELETE:-0}
plugin_local_path=${DBSYNC_PLUGIN_LOCAL_PATH:-wp-content/plugins}
plugin_excludes=${DBSYNC_PLUGIN_EXCLUDES:-}
plugin_rsync_flags=${DBSYNC_PLUGIN_RSYNC_FLAGS:-}
run_wp_cmds=${DBSYNC_PLUGIN_RUN_WP_CMDS:-1}
change_lines=0
declare -a touched_plugins
declare -a deleted_plugins

plugin_in_list() {
  local needle=$1
  shift || true
  local item
  for item in "$@"; do
    if [[ "$item" == "$needle" ]]; then
      return 0
    fi
  done
  return 1
}

format_plugins() {
  local items=("$@")
  if [[ ${#items[@]} -eq 0 ]]; then
    printf 'none'
    return
  fi
  (IFS=', '; printf '%s' "${items[*]}")
}

add_touched() {
  local plugin=$1
  if [[ -z "$plugin" ]]; then
    return
  fi
  if plugin_in_list "$plugin" "${touched_plugins[@]-}"; then
    return
  fi
  touched_plugins+=("$plugin")
}

add_deleted() {
  local plugin=$1
  if [[ -z "$plugin" ]]; then
    return
  fi
  if plugin_in_list "$plugin" "${deleted_plugins[@]-}"; then
    return
  fi
  deleted_plugins+=("$plugin")
}

require_env "$ssh_host" "DBSYNC_${upper_env}_SSH_HOST is not set"
require_env "$remote_wp_path" "DBSYNC_${upper_env}_WP_PATH is not set"

ssh_port=${ssh_port:-22}

if ! command -v rsync >/dev/null 2>&1; then
  echo "[plugin-sync] rsync command not found" >&2
  exit 1
fi

if [[ -n "$ssh_user" ]]; then
  ssh_target="${ssh_user}@${ssh_host}"
else
  ssh_target="$ssh_host"
fi

remote_plugins_path=${remote_plugins_override:-$remote_wp_path/wp-content/plugins}

mkdir -p "$plugin_local_path"

rsync_cmd=(rsync -az)

if [[ $DRY_RUN -eq 1 ]]; then
  rsync_cmd+=(--dry-run)
fi

if [[ $plugin_delete -eq 1 ]]; then
  rsync_cmd+=(--delete)
fi

if [[ -n "$plugin_excludes" ]]; then
  for pattern in $plugin_excludes; do
    rsync_cmd+=(--exclude "$pattern")
  done
fi

if [[ -n "$plugin_rsync_flags" ]]; then
  # shellcheck disable=SC2206
  rsync_cmd+=($plugin_rsync_flags)
fi

remote_rsync_path=$(printf "mkdir -p %q && rsync" "$remote_plugins_path")
remote_source=$(printf '%s' "$ssh_target:$(printf '%q' "$remote_plugins_path")/")

rsync_cmd+=(--itemize-changes)
rsync_cmd+=(-e)
rsync_cmd+=("ssh -p $ssh_port")
rsync_cmd+=(--rsync-path "$remote_rsync_path")
rsync_cmd+=("$remote_source")
rsync_cmd+=("$plugin_local_path/")

echo "[plugin-sync] Syncing plugins from $target"

if ! output=$("${rsync_cmd[@]}" 2>&1); then
  status=$?
  echo "$output" >&2
  echo "[plugin-sync] rsync failed" >&2
  exit $status
fi

if [[ -n "$output" ]]; then
  while IFS= read -r line; do
    [[ -z "$line" ]] && continue
    ((change_lines++))
    if [[ "$line" == *deleting* ]]; then
      path=${line#*deleting }
      plugin=${path%%/*}
      add_touched "$plugin"
      add_deleted "$plugin"
    else
      path=${line#* }
      plugin=${path%%/*}
      add_touched "$plugin"
    fi
  done <<< "$output"
fi

if [[ $DRY_RUN -eq 1 ]]; then
  echo "$output"
  touched_summary=$(format_plugins "${touched_plugins[@]-}")
  deleted_summary=$(format_plugins "${deleted_plugins[@]-}")
  printf '[plugin-sync] Summary (dry-run):\n'
  printf '  files changed: %d\n' "$change_lines"
  printf '  plugins touched: %s\n' "$touched_summary"
  printf '  plugins deleted: %s\n' "$deleted_summary"
  printf '  delete flag: %s\n' "$([[ $plugin_delete -eq 1 ]] && printf 'on' || printf 'off')"
  echo "[plugin-sync] Plugin sync dry-run complete"
  exit 0
fi

if [[ -n "$output" ]]; then
  echo "$output"
else
  echo "[plugin-sync] No plugin changes detected"
fi

if [[ $run_wp_cmds -eq 1 ]]; then
  wp_path=$(cd "$plugin_local_path/../.." && pwd)
  if [[ -n "$wp_path" ]]; then
    echo "[plugin-sync] Flushing caches"
    wp --path="$wp_path" cache flush 2>/dev/null || true
    echo "[plugin-sync] Deleting transients"
    wp --path="$wp_path" transient delete --all 2>/dev/null || true
    echo "[plugin-sync] Flushing rewrite rules"
    wp --path="$wp_path" rewrite flush --hard 2>/dev/null || true
  fi
fi

touched_summary=$(format_plugins "${touched_plugins[@]-}")
deleted_summary=$(format_plugins "${deleted_plugins[@]-}")
printf '[plugin-sync] Summary:\n'
printf '  files changed: %d\n' "$change_lines"
printf '  plugins touched: %s\n' "$touched_summary"
printf '  plugins deleted: %s\n' "$deleted_summary"
printf '  delete flag: %s\n' "$([[ $plugin_delete -eq 1 ]] && printf 'on' || printf 'off')"
printf '  cache flush: %s\n' "$([[ $run_wp_cmds -eq 1 ]] && printf 'on' || printf 'off')"

echo "[plugin-sync] Plugin sync complete"
