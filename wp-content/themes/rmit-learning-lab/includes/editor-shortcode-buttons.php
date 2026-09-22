<?php

//-----------------------------
// SHORTCODE BUTTONS IN THE CODE EDITOR
//
// QTags buttons only — they insert plain text at the cursor, or wrap a selection.
// The syntax highlighter plugin re-reads the textarea into CodeMirror after a
// toolbar action, so the insert lands in the visible Code tab.
//
// The Visual tab would need a TinyMCE plugin per shortcode, which is a much bigger
// build for the same result once the author switches to Code.
//
// Every wrapping button uses llWrap so they behave the same way: wrap a selection,
// or drop a complete block when there is none. QTags' own two-string form leaves the
// tag hanging open and renames the button to "/name", which reads as a second button.
//
// The buttons themselves live in js/editor-shortcode-buttons.js with their styles in
// css/editor-shortcode-buttons.css. They used to be printed inline on
// admin_print_footer_scripts, which fires on every admin screen, so ~24KB of CSS and
// JS was parsed on pages with no editor and re-sent on every load because inline
// assets cannot be cached.
//-----------------------------

add_action('admin_enqueue_scripts', function ($hook) {
    // The Code editor toolbar only exists where a post is being edited.
    if (!in_array($hook, array('post.php', 'post-new.php'), true)) {
        return;
    }

    $css = 'css/editor-shortcode-buttons.css';
    $js  = 'js/editor-shortcode-buttons.js';
    $uri = trailingslashit(get_stylesheet_directory_uri());

    wp_enqueue_style(
        'rmit-ll-editor-shortcode-buttons',
        $uri . $css,
        array(),
        rmit_learning_lab_asset_version($css)
    );

    // quicktags defines QTags, jquery is used to slot the separators into the toolbar.
    wp_enqueue_script(
        'rmit-ll-editor-shortcode-buttons',
        $uri . $js,
        array('quicktags', 'jquery'),
        rmit_learning_lab_asset_version($js),
        true
    );
});
