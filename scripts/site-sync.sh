#!/usr/bin/env bash
set -euo pipefail

usage() {
  cat <<USAGE
Usage: $(basename "$0") <environment> [options]

Run the database sync and plugin sync sequentially for a given environment.

Arguments:
  <environment>         Target environment key (e.g. prod, dev)

Options:
  --skip-db             Skip the database sync step
  --skip-plugins        Skip the plugin sync step
  --plugins-dry-run     Run the plugin sync in dry-run mode (no file changes)
  --no-plugin-flush     Disable cache/rewrite flush after plugin sync
  --db-skip-backup      Temporarily skip creating the local DB backup
  --keep-dump           Preserve the downloaded DB dump file
  --help                Show this help message

Environment passthrough:
  Respects ENV_FILE if set. Additional behaviour can be controlled via
  environment variables consumed by the underlying scripts (see each script's
  usage).
USAGE
}

if [[ ${1:-} == "--help" || ${1:-} == "-h" ]]; then
  usage
  exit 0
fi

target=${1:-}
if [[ -z "$target" ]]; then
  echo "[site-sync] Missing environment argument" >&2
  usage
  exit 1
fi

shift
skip_db=0
skip_plugins=0
plugins_dry_run=0
no_plugin_flush=0
db_skip_backup_override=0
keep_dump_override=0

while [[ $# -gt 0 ]]; do
  case $1 in
    --skip-db)
      skip_db=1
      ;;
    --skip-plugins)
      skip_plugins=1
      ;;
    --plugins-dry-run)
      plugins_dry_run=1
      ;;
    --no-plugin-flush)
      no_plugin_flush=1
      ;;
    --db-skip-backup)
      db_skip_backup_override=1
      ;;
    --keep-dump)
      keep_dump_override=1
      ;;
    --help|-h)
      usage
      exit 0
      ;;
    *)
      echo "[site-sync] Unknown option: $1" >&2
      usage
      exit 1
      ;;
  esac
  shift
done

script_dir=$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)
db_script="$script_dir/db-sync.sh"
plugins_script="$script_dir/plugins-sync.sh"

if [[ $skip_db -eq 0 && ! -x "$db_script" ]]; then
  echo "[site-sync] Missing or non-executable db-sync.sh at $db_script" >&2
  exit 1
fi

if [[ $skip_plugins -eq 0 && ! -x "$plugins_script" ]]; then
  echo "[site-sync] Missing or non-executable plugins-sync.sh at $plugins_script" >&2
  exit 1
fi

ENV_FILE=${ENV_FILE:-.env}
if [[ ! -f "$ENV_FILE" ]]; then
  echo "[site-sync] Missing environment file: $ENV_FILE" >&2
  exit 1
fi

printf '[site-sync] Starting sync for %s\n' "$target"

if [[ $skip_db -eq 0 ]]; then
  echo "[site-sync] Running database sync"
  db_env=(ENV_FILE="$ENV_FILE")
  if [[ $db_skip_backup_override -eq 1 ]]; then
    db_env+=(DBSYNC_SKIP_LOCAL_BACKUP=1)
  fi
  if [[ $keep_dump_override -eq 1 ]]; then
    db_env+=(DBSYNC_KEEP_TEMP_DUMP=1)
  fi
  env "${db_env[@]}" "$db_script" "$target"
else
  echo "[site-sync] Skipping database sync"
fi

if [[ $skip_plugins -eq 0 ]]; then
  echo "[site-sync] Running plugin sync"
  plugin_env=(ENV_FILE="$ENV_FILE")
  if [[ $no_plugin_flush -eq 1 ]]; then
    plugin_env+=(DBSYNC_PLUGIN_RUN_WP_CMDS=0)
  fi
  if [[ $plugins_dry_run -eq 1 ]]; then
    env "${plugin_env[@]}" "$plugins_script" "$target" --dry-run
  else
    env "${plugin_env[@]}" "$plugins_script" "$target"
  fi
else
  echo "[site-sync] Skipping plugin sync"
fi

printf '[site-sync] Sync complete for %s\n' "$target"
