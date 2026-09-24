// Suggested keywords under the Keywords field, from the existing vocabulary only.
// Settings come from admin-customizations.php via RMITKeywordSuggestions.
(function ($) {
    'use strict';

    var cfg = window.RMITKeywordSuggestions;
    if (!cfg) { return; }

    var field = document.querySelector('.acf-field[data-key="' + cfg.fieldKey + '"]');
    var select = field && field.querySelector('select');
    if (!select) { return; }

    var box = document.createElement('div');
    box.className = 'rmit-ll-keyword-suggestions';
    box.style.cssText = 'margin-top:8px;display:flex;flex-wrap:wrap;gap:6px;align-items:center;';
    select.parentNode.appendChild(box);

    var ambiguous = cfg.ambiguous.map(function (n) { return n.toLowerCase(); });
    var neighbours = cfg.neighbours.map(String);

    // The editor text lives in CodeMirror (syntax highlighter plugin) or TinyMCE,
    // not reliably in the textarea, so read whichever is live.
    function contentHtml() {
        var cm = document.querySelector('.CodeMirror');
        if (cm && cm.CodeMirror) { return cm.CodeMirror.getValue(); }
        var ed = window.tinymce && window.tinymce.get('content');
        if (ed && !ed.isHidden()) { return ed.getContent(); }
        var ta = document.getElementById('content');
        return ta ? ta.value : '';
    }

    function plain(html) {
        return html.replace(/\[[^\]]*\]/g, ' ').replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').toLowerCase();
    }

    function escapeRe(s) { return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); }

    function suggestions() {
        var html = contentHtml();
        var title = (document.getElementById('title') || {}).value || '';
        var headings = (html.match(/<h[1-4][^>]*>[\s\S]*?<\/h[1-4]>/gi) || []).join(' ');
        var prominent = plain(title + ' ' + headings);
        var body = plain(html);
        var chosen = Array.prototype.map.call(select.selectedOptions, function (o) { return o.value; });

        var scored = [];
        cfg.terms.forEach(function (t) {
            var id = String(t.id);
            if (chosen.indexOf(id) !== -1) { return; }
            var re = new RegExp('\\b' + escapeRe(t.name.toLowerCase()) + 's?\\b');
            var score = 0;
            if (re.test(prominent)) { score += 3; }
            else if (ambiguous.indexOf(t.name.toLowerCase()) === -1 && re.test(body)) { score += 1; }
            if (neighbours.indexOf(id) !== -1) { score += 2; }
            if (score) { scored.push({ id: id, name: t.name, score: score }); }
        });

        return scored.sort(function (a, b) { return b.score - a.score || a.name.localeCompare(b.name); }).slice(0, 8);
    }

    function add(term) {
        if (!select.querySelector('option[value="' + term.id + '"]')) {
            select.appendChild(new Option(term.name, term.id, true, true));
        } else {
            select.querySelector('option[value="' + term.id + '"]').selected = true;
        }
        $(select).trigger('change');
    }

    function render() {
        var list = suggestions();
        box.innerHTML = '';
        if (!list.length) { return; }
        var label = document.createElement('span');
        label.className = 'description';
        label.textContent = 'Suggested existing keywords:';
        box.appendChild(label);
        list.forEach(function (term) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'button button-small';
            b.textContent = '+ ' + term.name;
            b.addEventListener('click', function () { add(term); });
            box.appendChild(b);
        });
    }

    var timer;
    function later() { clearTimeout(timer); timer = setTimeout(render, 600); }

    $(select).on('change', render);
    $('#title').on('input', later);
    $('#content').on('input', later);
    // The syntax highlighter builds CodeMirror after DOM ready, so wait for it,
    // the same way the shortcode toolbar waits for QTags.
    var tries = 0;
    (function watchEditor() {
        var cm = document.querySelector('.CodeMirror');
        if (cm && cm.CodeMirror) { cm.CodeMirror.on('change', later); render(); return; }
        if (tries++ < 40) { setTimeout(watchEditor, 100); }
    })();
    $(render);
})(jQuery);
