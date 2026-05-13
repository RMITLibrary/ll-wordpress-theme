# RMIT Learning Lab WordPress Theme

WordPress theme development repository for the RMIT Learning Lab website.

> **Quick Setup Checklist**
> 1. Install prerequisites: MySQL server, nvm, WP-CLI, and rsync (see below).
> 2. Clone this repo into your local WordPress environment.
> 3. Download the latest WordPress backup (core + uploads + plugins) from WP Engine.
> 4. Import the production database into your local MySQL instance.
> 5. Update `wp-config.php` with local DB credentials and set `WP_HOME` / `WP_SITEURL`.
> 6. Copy `.env.example` → `.env` and fill the WP Engine SSH details.
> 7. Run `npm install`, then `npm run site:pull:prod` (or `:dev`).
> 8. Start your local server and log in with your usual WordPress credentials.

## Prerequisites

- Node.js v20+ (uses `.nvmrc`; install via nvm for easiest switching)
- nvm (Node Version Manager) – https://github.com/nvm-sh/nvm
- npm or yarn
- Local WordPress development environment (e.g., Laravel Herd or MAMP)
- PHP 7.4+ (WordPress requirement)
- MySQL 5.7+ or MariaDB 10.3+ (e.g., `brew install mysql`)
- WP-CLI and rsync available on your PATH

### Installing prerequisites

```bash
# Install nvm (see https://github.com/nvm-sh/nvm for latest script)
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.7/install.sh | bash

# Install Node.js 20 using nvm
export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && . "$NVM_DIR/nvm.sh"
[ -s "$NVM_DIR/bash_completion" ] && . "$NVM_DIR/bash_completion"
nvm install 20

# Install MySQL via Homebrew (macOS)
brew install mysql
brew services start mysql

# Optional: install WP-CLI
brew install wp-cli
```

- After installing nvm, restart your terminal or run `source "$HOME/.nvm/nvm.sh"` so the `nvm` command is available.
- First-time MySQL installs default to `root` with an empty password. Test access with `mysql -u root`.
- If you prefer GUI tools, use TablePlus or Sequel Ace once MySQL is running.
- Ensure `rsync` is available (`xcode-select --install` on macOS installs command line tools if missing).

## Local Environment Setup

These steps mirror how we run the theme locally with WP Engine data. Complete steps 1–6 to get up and running; the remaining steps are optional helpers.

### Core setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/RMITLibrary/ll-wordpress-theme.git
   cd ll-wordpress-theme
   ```
2. **Install WordPress core and uploads**
   1. Download WordPress (or a WP Engine backup snapshot).
   2. Extract WordPress into the repo root so `wp-admin/`, `wp-includes/`, etc. sit beside this README.
   3. Copy `wp-content/uploads`, `wp-content/mu-plugins`, and any other environment-specific directories from the backup (these paths are git-ignored).
   *WP Engine reference: [Download backups](https://wpengine.com/support/backup-points/) and [SSH/SFTP access](https://wpengine.com/support/ssh-gateway/).* 
3. **Restore the database**
   1. Create a local MySQL database.
   2. Import the production/staging SQL dump via `mysql`, phpMyAdmin, or `./scripts/db-sync.sh <env>`.
   3. Update `wp-config.php` with local DB credentials and set the local URLs:
      ```php
      define( 'WP_HOME', 'https://ll-wordpress-theme.test' );
      define( 'WP_SITEURL', 'https://ll-wordpress-theme.test' );
      ```
      - `WP_HOME` / `WP_SITEURL`: tell WordPress which domain to use locally.
      - Keep these in sync with whatever domain your local server is serving.
      - Fresh Homebrew MySQL installs use the `root` user with an empty password (`mysql -u root`).
4. **Install Node dependencies**
   ```bash
   nvm use        # optional, aligns with .nvmrc
   npm install
   ```
5. **Start your local server & log in**
   1. Boot your preferred PHP/MySQL stack (Laravel Herd or MAMP).
   2. Visit the URL defined in `WP_HOME` and log in using your normal WordPress credentials (accounts come across with the imported DB).
6. **Verify the theme loads**
   - Confirm the Learning Lab theme is active under Appearance → Themes.
   - Browse a few pages to ensure URLs resolve to your local domain.

### Optional tooling & helpers

7. **Configure `.env` for sync tooling**
   1. Copy `.env.example` to `.env`.
   2. Fill in `DBSYNC_<ENV>_*` values (SSH host, path, URL) for each remote you want to pull.
   3. Adjust plugin sync flags (`DBSYNC_PLUGIN_*`) if you need excludes or exact mirroring.
   4. Ensure your SSH public key is registered with WP Engine (internal guide: [Setting Up SSH Access to WP Engine](https://slcrmit.atlassian.net/wiki/spaces/DLT/pages/4509139854/Guide+Setting+Up+SSH+Access+to+WP+Engine)); the sync scripts authenticate via SSH.
   *Glossary:* `.env` holds private credentials; the sync scripts export values like `DBSYNC_PROD_SSH_HOST` to know where to fetch data from.
8. **Pull database and plugins automatically**
   ```bash
   npm run site:pull:prod    # or :dev, :uat, etc.
   ```
   The wrapper script first runs the DB sync (backup → import → search/replace → cache flush) then rsyncs `wp-content/plugins`, printing summaries for both phases.
9. **Front-end workflow helpers**
   ```bash
   npm run dev     # watch & compile SASS during development
   npm run build   # one-off production compile
   npm run clean   # remove compiled CSS artifacts
   ```

## Available Scripts

- `npm run dev` – Watch SASS files and auto-compile on changes (alias for `watch`)
- `npm run build` – Build compressed CSS with source maps (alias for `sass`)
- `npm run sass` – Single compile with compressed output and source maps
- `npm run watch` – Watch SASS files and auto-compile on changes
- `npm run clean` – Remove generated CSS files
- `npm run db:pull:prod` / `npm run db:pull:dev` – Pull the database from the specified environment
- `npm run plugins:pull:prod` / `npm run plugins:pull:dev` – Pull remote plugins down to the local install via rsync
- `npm run site:pull:prod` / `npm run site:pull:dev` – Pull both database and plugins sequentially

> The sync commands read configuration from `.env`; see the sync workflow section below before running them.

### Testing

**Mobile overflow audit** — checks every published page at 320px viewport for horizontal overflow (content wider than the screen). Useful for catching MathJax equations, wide tables, or other content that breaks the mobile layout.

```bash
# Run audit — prints offending URLs and the elements causing the overflow
npm run test:overflow

# Same but also saves a full JSON report
npm run test:overflow:report
```

Requirements:
- Local site must be running at `https://ll-wordpress-theme.test`
- WP-CLI must be available on your `PATH`
- Playwright Chromium is installed automatically via `npm install`

The report (if generated) is saved to `overflow-report.json` in the project root and is git-ignored.

## Project Structure

```
ll-wordpress-theme/
├── wp-content/
│   └── themes/
│       ├── picostrap5/          # Parent theme (Bootstrap 5 base)
│       └── rmit-learning-lab/   # Child theme (main development)
│           ├── sass/             # SASS source files
│           │   ├── main.scss     # Main SASS entry point
│           │   ├── design-system/# Design system components
│           │   └── learning-lab/ # Site-specific styles
│           ├── css-output/       # Compiled CSS output
│           │   └── bundle.css    # Main compiled CSS
│           ├── js/               # JavaScript files
│           ├── includes/         # Modular PHP functionality
│           │   ├── admin-customizations.php
│           │   ├── analytics-dashboards.php
│           │   ├── breadcrumbs-navigation.php
│           │   ├── content-filters.php
│           │   ├── custom-shortcodes.php
│           │   ├── custom-taxonomy.php
│           │   ├── helper-utils.php
│           │   ├── json-export.php
│           │   ├── redirect.php
│           │   └── seo-noindex-inheritance.php
│           ├── page-templates/   # Custom page templates
│           ├── custom-shortcodes/# Custom WordPress shortcodes
│           ├── acf-json/         # ACF field definitions
│           └── functions.php     # Theme functions (modular)
├── package.json                  # Node dependencies and scripts
├── .nvmrc                        # Node version specification
├── SYNC_GUIDE.md                 # Full sync workflow documentation
└── README.md                     # This file
```

## Theme Architecture

This is a WordPress child theme setup:
- **Parent Theme**: Picostrap5 (v3.1.0) - Provides Bootstrap 5.3.3 foundation
- **Child Theme**: RMIT Learning Lab (v3.1.0) - Custom implementation

## SASS Workflow

1. Edit SASS files in `wp-content/themes/rmit-learning-lab/sass/`
2. Main entry point is `sass/main.scss`
3. Site-specific styles go in `sass/learning-lab/` directory
4. Compiled output goes to `css-output/bundle.css` (compressed with source maps)
5. All builds produce consistent compressed CSS matching the parent theme compiler

## Version Control

The repository tracks:
- Child theme files (rmit-learning-lab)
- Parent theme files (picostrap5)
- Development configuration (package.json, .nvmrc)
- Custom sync tooling (`scripts/`, `SYNC_GUIDE.md`)

The repository ignores:
- WordPress core files
- Plugin files
- Upload directories
- Build artifacts and source maps
- Local configuration files

## Deployment

This theme is hosted on WP Engine and uses GitHub Actions for automated deployment.

Push changes to the appropriate branch and GitHub Actions will deploy the `wp-content/themes/` directory to the mapped WP Engine environment. Cache is cleared after each deployment. Monitor builds at https://github.com/RMITLibrary/ll-wordpress-theme/actions.

See [`GITFLOW_GUIDE.md`](./GITFLOW_GUIDE.md) for the full branching workflow, release process, hotfixes, and rollback steps.

## Troubleshooting Highlights

| Symptom | Try This |
| --- | --- |
| `wp: command not found` | Install WP-CLI and ensure it’s on your PATH (https://wp-cli.org/). |
| Database import fails | Confirm DB credentials in `wp-config.php`; check SQL dump isn’t gzipped; use `mysql -u root dbname < dump.sql   # add -p if you set a password`. |
| Site shows wrong domain or redirects | Re-run `npm run site:pull:prod` (search-replace step) or manually update `WP_HOME`/`WP_SITEURL`. |
| Plugin sync removes local-only plugins | Add them to `DBSYNC_PLUGIN_EXCLUDES` before running the script, or set `DBSYNC_PLUGIN_DELETE=0`. |
| `ENV_FILE=.env: command not found` | Ensure you’re calling scripts via `./scripts/*.sh` or the npm alias on the latest branch (wrapper now uses `env`). |
| Permission denied (publickey) when syncing | Follow the internal SSH setup guide above and confirm the key matches the user in `.env`; test with `ssh <user>@<host>`. |

## Git Workflow

This project follows **GitFlow** using Sourcetree, with GitHub Actions deploying automatically on merge to `develop` or `main`.

| Branch | Deploys To |
|---|---|
| `main` | PRD — `prdlearninglab.wpenginepowered.com` |
| `develop` | DEV — `devlearninglab.wpenginepowered.com` |

The standard path is `feature/*` → `develop` → release → `main`. For most changes the team releases straight to PRD; DEV is reserved for large structural changes. Primary validation is local testing against a PRD DB pull (`npm run site:pull:prod`).

See [`GITFLOW_GUIDE.md`](./GITFLOW_GUIDE.md) for the full workflow — features, releases, hotfixes, and rollback.

## Contributing

1. Always branch from `develop` and follow the workflow in [`GITFLOW_GUIDE.md`](./GITFLOW_GUIDE.md)
2. Use [Conventional Commits](https://www.conventionalcommits.org/) for all commit messages
3. Run `npm run build` before merging to ensure compiled CSS is up to date
4. Test locally against a PRD DB pull before releasing

## Support

For issues or questions, contact the Digital Learning Team at RMIT University.

## License

GPL-2.0 (WordPress compatible)
