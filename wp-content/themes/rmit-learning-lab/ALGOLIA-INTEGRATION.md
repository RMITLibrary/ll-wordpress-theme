# Algolia Integration — Future Planning Doc

**Status:** Not started
**Last updated:** 2026-03-19
**Goal:** Replace the existing Fuse.js client-side search with Algolia for faster, more accurate, and faceted search across Learning Lab content.

---

## Background

The site currently uses a **Fuse.js** fuzzy search implementation:

- `includes/json-export.php` generates static JSON files (`pages.json`, `pages-index.json`, `pages-urls.json`) written to `wp-content/uploads/`
- `js/search.js` and `js/search-home.js` load those JSON files client-side and run searches in the browser
- The search page lives at `page-search.php`

The JSON export pipeline is already well-built — it extracts content from shortcodes (accordions, transcripts, highlights), pulls SEO meta descriptions, builds breadcrumbs, and respects Work in Progress exclusions. This is the foundation for the Algolia integration.

---

## Content & Data Structure

### Post Types
- Only `page` is used. No custom post types.

### Custom Taxonomies (defined in `acf-json/`)
- **Keywords** — non-hierarchical, public, in REST API. Used for content tagging.
- **Subject Areas** — non-hierarchical, public, in REST API. Used for content categorisation.

### ACF Fields (defined in `acf-json/`)
- **Keywords** — multi-select taxonomy field on pages
- **Subject areas** — multi-select taxonomy field on pages
- **Author notes** — internal textarea, not for search
- **Previous page / Next page** — relationship fields for sequential navigation

---

## Recommended Algolia Record Shape

Each published page should become one Algolia record. For very long pages, see [Record Splitting](#record-splitting) below.

```json
{
  "objectID": "123",
  "title": "Understanding Referencing",
  "content": "Plain text, shortcode inner content included, HTML stripped",
  "excerpt": "Meta description from Yoast / Rank Math / AiSEO",
  "url": "/learning-lab/academic-skills/referencing/understanding-referencing/",
  "keywords": ["APA", "citations", "referencing"],
  "subject_areas": ["Academic Skills", "Writing"],
  "breadcrumbs": ["Learning Lab", "Academic Skills", "Referencing"],
  "hierarchy": {
    "lvl0": "Learning Lab",
    "lvl1": "Academic Skills",
    "lvl2": "Referencing",
    "lvl3": "Understanding Referencing"
  },
  "template": "first-content-page",
  "modified": "2024-11-01T00:00:00",
  "work_in_progress": false
}
```

**Notes on the `hierarchy` field:**
Algolia's InstantSearch.js uses `lvl0`–`lvl5` natively for breadcrumb display in search results. This maps directly to the page hierarchy that `createBreadcrumbs()` already builds.

---

## Algolia Index Configuration

Once records are indexed, configure the index as follows:

### Searchable Attributes (in priority order)
```
1. title
2. keywords
3. subject_areas
4. excerpt
5. content
```

### Attributes for Faceting
```
- keywords
- subject_areas
- hierarchy.lvl0
- hierarchy.lvl1
```

### Custom Ranking
```
- modified (descending) — freshest content wins ties
```

### Attributes to Retrieve (what gets returned in results)
```
- title
- excerpt
- url
- breadcrumbs
- hierarchy
- keywords
- subject_areas
```

`content` does not need to be retrieved — it's for search only.

---

## Implementation Approach

### Recommended: Extend the existing JSON export (Option A)

The `includes/json-export.php` file already handles the hard parts:
- Extracting inner content from shortcodes (`ll-accordion`, `transcript`, `highlight-text`, `hl`, etc.)
- Building breadcrumbs
- Pulling SEO meta descriptions from Yoast, Rank Math, AiSEO
- Excluding Work in Progress pages

The plan is to add an Algolia push step alongside (or instead of) the file write.

**Option B — WP Search with Algolia plugin**
Algolia maintains an [official WordPress plugin](https://wordpress.org/plugins/wp-search-with-algolia/). It auto-syncs posts and taxonomies but does **not** capture shortcode-extracted content (transcripts, accordions) the way the existing exporter does. Not recommended unless the shortcode content extraction is no longer a priority.

**Option C — WP-CLI batch script**
A one-off or scheduled WP-CLI command to do a full re-index. Good for initial population of the index. Should be used alongside Option A for ongoing sync.

---

## Implementation Steps

### Phase 1 — Setup & credentials

- [ ] Create an Algolia account at algolia.com
- [ ] Create a new index (e.g. `ll_pages_production`, `ll_pages_staging`)
- [ ] Collect credentials: `Application ID`, `Admin API Key`, `Search-Only API Key`
- [ ] Add credentials to `wp-config.php` as constants (never commit these):
  ```php
  define( 'ALGOLIA_APP_ID', 'your-app-id' );
  define( 'ALGOLIA_ADMIN_API_KEY', 'your-admin-key' );
  define( 'ALGOLIA_SEARCH_API_KEY', 'your-search-only-key' );
  define( 'ALGOLIA_INDEX_NAME', 'll_pages_production' );
  ```

### Phase 2 — PHP integration

- [ ] Install the Algolia PHP client via Composer:
  ```bash
  composer require algolia/algoliasearch-client-php
  ```
  Or download and include manually if Composer isn't set up.

- [ ] Create `includes/algolia-sync.php` with:
  - A function `build_algolia_record( $post_id )` that formats a page as the record shape above, reusing logic from `json-export.php`
  - A function `push_page_to_algolia( $post_id )` that calls the Algolia API
  - A function `delete_page_from_algolia( $post_id )` for when pages are trashed/deleted
  - A function `reindex_all_pages()` for full re-index (used by WP-CLI or admin trigger)

- [ ] Hook into WordPress lifecycle events in `functions.php`:
  ```php
  add_action( 'save_post_page', 'push_page_to_algolia', 10, 1 );
  add_action( 'wp_trash_post',  'delete_page_from_algolia', 10, 1 );
  add_action( 'untrash_post',   'push_page_to_algolia', 10, 1 );
  ```

- [ ] Guard against indexing WIP pages and draft pages:
  - Check `get_post_status( $post_id ) === 'publish'`
  - Check the Work in Progress flag (same logic as `json-export.php`)

### Phase 3 — Initial index population

- [ ] Run the full re-index once via WP-CLI or a temporary admin page trigger:
  ```bash
  wp eval 'reindex_all_pages();'
  ```
- [ ] Verify record count in Algolia dashboard matches published page count
- [ ] Spot-check 5–10 records for correct field values

### Phase 4 — Configure the Algolia index settings

- [ ] Set searchable attributes, facets, and custom ranking (see [Index Configuration](#algolia-index-configuration) above)
- [ ] These can be set via the Algolia dashboard UI or pushed programmatically via PHP on first setup

### Phase 5 — Frontend (InstantSearch.js)

- [ ] Add InstantSearch.js and the Algolia search client to the theme:
  ```bash
  # Via npm
  npm install algoliasearch instantsearch.js
  # Or load from CDN in functions.php
  ```
- [ ] Replace `js/search.js` with an InstantSearch implementation:
  - Search box
  - Hits widget (title, breadcrumbs, excerpt, url)
  - RefinementList for `keywords` facet
  - RefinementList for `subject_areas` facet
  - Breadcrumb widget using `hierarchy.lvl0` / `lvl1`
  - Pagination
- [ ] Replace `js/search-home.js` with a lightweight Algolia autocomplete (global header search bar)
- [ ] Update `page-search.php` to remove the Fuse.js debug panel and JSON loading
- [ ] The Search-Only API Key is safe to expose in frontend JS — it cannot modify the index

### Phase 6 — Testing & cleanup

- [ ] Test search results quality vs existing Fuse.js
- [ ] Test facet filtering by keyword and subject area
- [ ] Test that WIP pages do not appear in results
- [ ] Test that saving a page in wp-admin triggers a record update in Algolia
- [ ] Test that trashing a page removes it from Algolia
- [ ] Remove or archive old Fuse.js files once Algolia is confirmed working:
  - `js/search.js`
  - `js/search-home.js`
  - The file-write portion of `includes/json-export.php` (or keep for backup)

---

## Record Splitting

Algolia has a **10KB per record limit** (hard) and recommends keeping records under ~5KB for best performance.

Long pages with large transcripts or accordions may exceed this. If needed:

1. Split page content by heading (`<h2>`, `<h3>`)
2. Create one record per section with:
   ```json
   {
     "objectID": "123-section-2",
     "title": "Understanding Referencing",
     "section_title": "APA 7th Edition",
     "content": "Section content only...",
     "url": "/path/to/page/#apa-7th-edition",
     "anchor": "apa-7th-edition",
     ...
   }
   ```
3. All section records share the same `url` base but have an `anchor` for deep linking

This is an enhancement — start with one record per page and only split if record size becomes an issue.

---

## Files Involved

| File | Role |
|------|------|
| `includes/json-export.php` | Existing content extractor — reuse its logic |
| `includes/algolia-sync.php` | New file — Algolia push/delete/reindex functions |
| `functions.php` | Add `save_post` / `wp_trash_post` hooks |
| `page-search.php` | Replace Fuse.js UI with InstantSearch.js |
| `js/search.js` | Replace with Algolia InstantSearch |
| `js/search-home.js` | Replace with Algolia Autocomplete |
| `wp-config.php` | Add Algolia credentials as constants |

---

## Risks & Considerations

- **Algolia is a paid service** — free tier (10k records, 10k searches/month) may be sufficient for this site's scale, but check usage before committing.
- **API keys in wp-config.php** — ensure `wp-config.php` remains in `.gitignore` (it already should be).
- **Index stays fresh** — the `save_post` hook handles ongoing sync, but if bulk operations are done via WP-CLI or direct DB edits, a manual re-index will be needed.
- **Fuse.js transition** — both search systems can run in parallel during transition. Only remove Fuse.js once Algolia is confirmed working well.
