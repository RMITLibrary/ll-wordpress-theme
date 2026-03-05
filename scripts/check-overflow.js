#!/usr/bin/env node

/**
 * Mobile Overflow Checker
 *
 * Visits all published pages at 320px viewport width and reports any that
 * have horizontal overflow (scrollWidth > clientWidth).
 *
 * Usage:
 *   node scripts/check-overflow.js
 *   node scripts/check-overflow.js --url https://ll-wordpress-theme.test
 *   node scripts/check-overflow.js --concurrency 3
 *   node scripts/check-overflow.js --output overflow-report.json
 */

const { chromium } = require('playwright');
const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

// --- Config ---
const args = process.argv.slice(2);
const getArg = (flag, fallback) => {
    const i = args.indexOf(flag);
    return i !== -1 ? args[i + 1] : fallback;
};

const BASE_URL   = getArg('--url', 'https://ll-wordpress-theme.test');
const VIEWPORT   = { width: 320, height: 812 };
const CONCURRENCY = parseInt(getArg('--concurrency', '3'), 10);
const OUTPUT_FILE = getArg('--output', null);
const WAIT_MS     = 1500; // time to allow MathJax to render before checking

// --- Get URLs from WP-CLI ---
function getPageUrls() {
    console.log('Fetching page URLs via WP-CLI...');
    try {
        const raw = execSync(
            'wp post list --post_type=page --post_status=publish --field=url',
            { encoding: 'utf8' }
        );
        return raw.trim().split('\n').filter(Boolean);
    } catch (e) {
        console.error('WP-CLI failed. Make sure wp is available and you are in the project root.');
        process.exit(1);
    }
}

// --- Check a single page for overflow ---
async function checkPage(page, url) {
    try {
        await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 15000 });
        // Wait for MathJax and other dynamic content to settle
        await page.waitForTimeout(WAIT_MS);

        const result = await page.evaluate(() => {
            const body = document.body;
            const html = document.documentElement;
            const pageWidth = Math.max(html.clientWidth, html.scrollWidth);
            const viewportWidth = window.innerWidth;
            const overflows = pageWidth > viewportWidth;

            // Find the specific elements causing the overflow
            const offenders = [];
            if (overflows) {
                document.querySelectorAll('*').forEach(el => {
                    const rect = el.getBoundingClientRect();
                    if (rect.right > viewportWidth + 2) { // 2px tolerance
                        offenders.push({
                            tag: el.tagName.toLowerCase(),
                            classes: el.className && typeof el.className === 'string'
                                ? el.className.trim().split(/\s+/).slice(0, 4).join(' ')
                                : '',
                            width: Math.round(rect.right),
                        });
                    }
                });
            }

            // Deduplicate by tag+class combo, keep worst offender per type
            const seen = new Set();
            const uniqueOffenders = offenders.filter(o => {
                const key = `${o.tag}.${o.classes}`;
                if (seen.has(key)) return false;
                seen.add(key);
                return true;
            }).slice(0, 5); // cap at 5 per page

            return { overflows, pageWidth, viewportWidth, offenders: uniqueOffenders };
        });

        return { url, ...result };
    } catch (e) {
        return { url, error: e.message };
    }
}

// --- Run in batches ---
async function runInBatches(items, batchSize, fn) {
    const results = [];
    for (let i = 0; i < items.length; i += batchSize) {
        const batch = items.slice(i, i + batchSize);
        const batchResults = await Promise.all(batch.map(fn));
        results.push(...batchResults);
        const done = Math.min(i + batchSize, items.length);
        process.stdout.write(`\r  Checked ${done}/${items.length} pages...`);
    }
    return results;
}

// --- Main ---
(async () => {
    const urls = getPageUrls();
    console.log(`Found ${urls.length} pages to check at ${VIEWPORT.width}px viewport.\n`);

    const browser = await chromium.launch({
        ignoreHTTPSErrors: true, // for .test local SSL
    });

    const contexts = await Promise.all(
        Array.from({ length: CONCURRENCY }, () =>
            browser.newContext({ viewport: VIEWPORT, ignoreHTTPSErrors: true })
        )
    );
    const pages = await Promise.all(contexts.map(ctx => ctx.newPage()));

    let pageIndex = 0;
    const results = await runInBatches(urls, CONCURRENCY, async (url) => {
        const page = pages[pageIndex % CONCURRENCY];
        pageIndex++;
        return checkPage(page, url);
    });

    await browser.close();

    console.log('\n');

    // --- Report ---
    const overflowing = results.filter(r => r.overflows);
    const errored     = results.filter(r => r.error);
    const clean       = results.filter(r => !r.overflows && !r.error);

    console.log(`Results:`);
    console.log(`  ✓ Clean:     ${clean.length}`);
    console.log(`  ✗ Overflow:  ${overflowing.length}`);
    console.log(`  ! Errors:    ${errored.length}`);
    console.log('');

    if (overflowing.length > 0) {
        console.log('Pages with horizontal overflow at 320px:\n');
        overflowing.forEach(r => {
            console.log(`  ${r.url}`);
            if (r.offenders && r.offenders.length > 0) {
                r.offenders.forEach(o => {
                    const cls = o.classes ? `.${o.classes.replace(/\s+/g, '.')}` : '';
                    console.log(`    → <${o.tag}${cls}> extends to ${o.width}px`);
                });
            }
            console.log('');
        });
    }

    if (errored.length > 0) {
        console.log('Pages that errored:\n');
        errored.forEach(r => console.log(`  ${r.url} — ${r.error}`));
        console.log('');
    }

    if (OUTPUT_FILE) {
        const report = { checkedAt: new Date().toISOString(), viewport: VIEWPORT, overflowing, errored };
        fs.writeFileSync(OUTPUT_FILE, JSON.stringify(report, null, 2));
        console.log(`Full report saved to ${OUTPUT_FILE}`);
    }

    process.exit(overflowing.length > 0 ? 1 : 0);
})();
