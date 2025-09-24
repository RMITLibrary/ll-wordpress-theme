# Repository Guidelines

## Project Structure & Module Organization
The WordPress core sits in the project root; theme work happens in `wp-content/themes/rmit-learning-lab`. Keep PHP templates in the theme root, shared logic in `includes/`, shortcodes in `custom-shortcodes/`, and JavaScript in `js/`. Sass sources live under `sass/` (notably `sass/design-system/`), with compiled assets emitted to `css-output/`. Bundled fonts, images, and screenshots reside in `fonts/` and the root theme folder. Advanced Custom Fields definitions are versioned in `acf-json/`—export new field groups there.

## Build, Test, and Development Commands
Run `npm install` once per machine. Use `npm run dev` during theme development to watch Sass and rebuild `css-output/bundle.css`. Execute `npm run build` for a one-off production compile, and `npm run clean` before regenerating assets if caches cause issues. All commands target `wp-content/themes/rmit-learning-lab` paths and assume the project root as the working directory.

## Coding Style & Naming Conventions
Follow WordPress PHP coding standards: tabs for indentation, snake_case functions, and escaped output (`esc_html`, `wp_kses`) for user-facing content. Keep template partials small and place reusable functions in `includes/`. Sass files prefer two-space indentation and modular imports—mirror existing patterns when adding to `sass/design-system/`. When adding JavaScript, enqueue files via `functions.php` with `in_footer => true` to maintain non-blocking loads.

## Testing Guidelines
There is no automated test suite; rely on manual verification. After asset changes, load key templates (home, archive, custom pages) and test both desktop and mobile breakpoints. Confirm ACF-driven layouts by synchronising field groups (`Custom Fields > Tools > Sync`) and re-saving. Validate accessibility basics (keyboard navigation, color contrast) before requesting review.

## Commit & Pull Request Guidelines
Adopt the existing Conventional Commits flavor (`feat:`, `fix:`, `doc:`) and keep subjects under ~72 characters. Group related Sass, PHP, and asset changes in a single commit when they belong to the same feature. Pull requests should describe the change, note impacted templates or shortcodes, and include before/after screenshots for visual tweaks. Reference Jira or GitHub issues where relevant, and confirm that `npm run build` has been executed so reviewers see the latest compiled CSS.

## Configuration Tips
Respect environment-specific configuration by leaving `wp-config.php` untouched; use `.env` or hosting controls instead. When adding new images or downloads, place them under `wp-content/uploads` via the WordPress media library rather than committing binaries to Git.
