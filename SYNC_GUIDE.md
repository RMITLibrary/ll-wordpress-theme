# Local Sync Guide

This guide covers the helper scripts bundled in this repository for keeping a local WordPress instance in step with remote environments. All commands should be run from the project root (`/Users/<you>/Sites/ll-wordpress-theme`).

## 1. Prerequisites

- WP-CLI installed and available on your `PATH`.
- SSH access to the target environments.
- `rsync` available (macOS and most Linux distros ship with it by default).
- A populated `.env` file at the project root. Copy `.env.example` and fill in the values that describe each remote:
  ```bash
  cp .env.example .env
  # edit .env with your credentials
  ```

Key values you must provide per environment:

- `DBSYNC_<ENV>_SSH_HOST`, `DBSYNC_<ENV>_SSH_PORT`, `DBSYNC_<ENV>_REMOTE_USER`
- `DBSYNC_<ENV>_WP_PATH` (absolute path to the remote WordPress install)
- `DBSYNC_<ENV>_URL` (full remote site URL, e.g. `https://www.example.com`)
- Optional: `DBSYNC_<ENV>_PLUGIN_PATH` if plugins live outside the standard `wp-content/plugins`.
- Global toggles: `LOCAL_URL`, `DBSYNC_LOCAL_WP_PATH`, `DBSYNC_PLUGIN_LOCAL_PATH`, and other overrides documented inside `.env.example`.

> Tip: if you add additional environments (e.g. `uat`), just mirror the pattern from `prod`/`dev` in `.env`.

## 2. Database Sync (`scripts/db-sync.sh`)

Usage highlights:

```bash
./scripts/db-sync.sh prod
```

What happens:

1. Exports the remote database over SSH using WP-CLI.
2. Optionally backs up your local DB (`./db-sync/backups/...`).
3. Resets the local database and imports the fresh dump.
4. Runs a set of search/replace cycles that cover:
   - HTTPS ↔ HTTP variations
   - Protocol-relative (`//example.com`) and bare-host URLs
   - Preserves users & primary GUID columns by skipping `guid,user_email,user_login,user_nicename` and the entire users table.
5. Flushes caches, deletes transients, flushes rewrite rules, and prints a
   block-style summary of replacements, backup status, and multisite mode.

Key environment toggles (set permanently in `.env` or temporarily inline):

- `DBSYNC_SKIP_LOCAL_BACKUP=1` – skip the pre-import local backup.
- `DBSYNC_KEEP_TEMP_DUMP=1` – keep the downloaded SQL file.
- `DBSYNC_IS_MULTISITE=1` – include `--network` on search-replace.
- `DBSYNC_LOCAL_EXTRA_SEARCH="old=>new another=>replacement"` – extra search/replace pairs.

## 3. Plugin Sync (`scripts/plugins-sync.sh`)

Usage highlights:

```bash
./scripts/plugins-sync.sh prod
./scripts/plugins-sync.sh prod --dry-run   # preview changes
```

What happens:

1. Rsyncs `wp-content/plugins` from the remote server.
2. Shows rsync `--itemize-changes` output so you can see files added/changed.
3. After a real sync (non dry-run) it runs:
   - `wp cache flush`
   - `wp transient delete --all`
   - `wp rewrite flush --hard`
4. Prints an itemised summary block showing how many files changed, which
   plugin folders were touched/deleted, and whether the `--delete` or cache
   flush toggles were active.

Key environment toggles:

- `DBSYNC_PLUGIN_LOCAL_PATH` – change local destination (defaults to `wp-content/plugins`).
- `DBSYNC_PLUGIN_DELETE=1` – pass `--delete` to rsync (mirrors remote exactly; removes local-only files).
- `DBSYNC_PLUGIN_EXCLUDES="akismet/ hello.php"` – space-separated rsync excludes.
- `DBSYNC_PLUGIN_RSYNC_FLAGS="--info=progress2"` – append custom rsync flags.
- `DBSYNC_PLUGIN_RUN_WP_CMDS=0` – skip post-sync WP-CLI cache/permalink flushes.

## 4. Site Sync Wrapper (`scripts/site-sync.sh`)

Combine both steps in one command:

```bash
./scripts/site-sync.sh prod
```

Options:

- `--skip-db` – only sync plugins.
- `--skip-plugins` – only sync the database.
- `--plugins-dry-run` – dry run the plugin sync.
- `--no-plugin-flush` – skip the plugin post-sync WP-CLI commands.
- `--db-skip-backup` – temporarily skip the local DB backup.
- `--keep-dump` – keep the temporary SQL dump from the DB download.

Example: only grab plugins with a preview:

```bash
./scripts/site-sync.sh dev --skip-db --plugins-dry-run
```

## 5. npm Convenience Scripts

The `package.json` exposes shortcuts:

```bash
npm run db:prod           # ./scripts/db-sync.sh prod
npm run db:dev
npm run plugins:prod
npm run plugins:dev
npm run site-sync:prod    # database + plugins
npm run site-sync:dev
```

You can chain flags by invoking the underlying shell script directly (npm doesn’t pass extra args cleanly).

## 6. Safety & Best Practices

- Always run `--dry-run` with the plugin sync the first time you point to a new environment.
- Keep local-only plugin experiments out of the sync by adding them to `DBSYNC_PLUGIN_EXCLUDES`.
- DB sync search-replace skips sensitive columns, but you should still verify admin logins after a pull.
- Before pushing changes upstream, re-run `npm run build` to ensure compiled Sass is up to date.
- If you hit permission problems, confirm the SSH user has read access to `wp-content/plugins` and the database.
- For multisite installs set `DBSYNC_IS_MULTISITE=1` and consider syncing `uploads/` via an additional script.

## 7. Troubleshooting

| Issue | Possible Fix |
| --- | --- |
| `declare: -A: invalid option` | Update macOS Bash (or use the shipped scripts; current version avoids associative arrays). |
| `ENV_FILE=.env: command not found` | Ensure you’re on the latest `scripts/site-sync.sh` (uses `env` to pass variables). |
| `wp: command not found` | Install WP-CLI and ensure it’s on your PATH. |
| `rsync failed` | Run with `--dry-run` to inspect output; confirm SSH credentials and path variables. |

## 8. Extending The Workflow

- Add additional npm scripts if you regularly sync a staging/UAT environment.
- Clone `plugins-sync.sh` as a pattern if you want to sync uploads or mu-plugins.
- Consider adding cron jobs or GitHub Actions that alert you when remote plugins update so you know when to re-sync locally.

---

Questions or improvements? Reach out to the Digital Learning Team so we can keep this workflow predictable for everyone.
