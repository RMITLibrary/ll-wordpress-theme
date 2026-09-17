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
//-----------------------------

add_action('admin_print_footer_scripts', function () {
    ?>
    <style>
        /* Standalone divider between the WordPress buttons and ours. Drawn as its own
           element, not a border on the button, so it does not sit against the button edge. */
        #ed_toolbar .ll-sep {
            display: inline-block;
            width: 1px;
            height: 20px;
            margin: 0 10px -5px;
            background: #c3c4c7;
        }
        .ll-panel {
            position: absolute;
            z-index: 100100;
            background: #fff;
            border: 1px solid #8c8f94;
            border-radius: 3px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .2);
            padding: 12px 14px;
            font-size: 13px;
        }
        .ll-panel[hidden] { display: none !important; }
        .ll-panel label {
            display: block;
            margin-bottom: 12px;
            font-weight: 600;
            color: #1d2327;
        }
        .ll-panel select, .ll-panel input[type="text"] { width: 100%; margin-top: 3px; }
        .ll-panel label[hidden] { display: none !important; }
        .ll-panel .ll-check { display: flex; align-items: flex-start; gap: 6px; }
        .ll-panel .ll-check input { margin: 2px 0 0; }
        .ll-panel .ll-hint {
            display: block;
            margin: 1px 0 3px;
            font-size: 12px;
            line-height: 1.4;
            font-weight: 400;
            color: #646970;
        }
        .ll-panel .ll-actions { display: flex; gap: 8px; justify-content: flex-end; }
        .ll-panel .ll-swatch-row { display: flex; align-items: center; gap: 8px; margin-top: 3px; }
        .ll-panel .ll-swatch-row select { margin-top: 0; }
        .ll-panel .ll-swatch {
            flex: 0 0 auto;
            width: 34px;
            height: 22px;
            border: 1px solid #c3c4c7;
            border-radius: 3px;
            border-top-width: 4px;
            background: #fff center/contain no-repeat;
        }
        .ll-panel .ll-swatch.ll-icon { width: 40px; height: 32px; border-top-width: 1px; }
        .ll-panel .ll-swatch.ll-fill { border-top-width: 1px; }
        .ll-panel .ll-swatch.ll-diagram {
            width: 62px; height: 34px; border-top-width: 1px;
            display: flex; gap: 2px; padding: 3px; background: #fff;
        }
        .ll-panel .ll-diagram i { display: block; background: #c3c4c7; border-radius: 1px; }
        .ll-panel .ll-diagram .ll-key { background: #fac800; }
    </style>
    <script>
    if (typeof QTags !== 'undefined') {

        function llWrap(id, label, open, close, empty, title) {
            QTags.addButton(id, label, function (el, canvas) {
                var selected = canvas.value.substring(canvas.selectionStart, canvas.selectionEnd);
                QTags.insertContent(selected ? open + selected + close : empty);
            }, '', '', title);
        }

        // A dropdown or checkbox, not a prompt — these attributes are fixed sets and
        // yes/no flags, neither of which a free-text box expresses well. One panel,
        // rebuilt per button from a field list, rather than one panel per shortcode.
        var llPanel = null, llCanvas = null, llStart = 0, llEnd = 0, llBuild = null;

        function llEnsurePanel() {
            if (llPanel) { return llPanel; }
            llPanel = document.createElement('div');
            llPanel.className = 'll-panel';
            llPanel.hidden = true;
            document.body.appendChild(llPanel);
            llPanel.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') { llClose(); }
                if (e.key === 'Enter' && e.target.tagName !== 'BUTTON') { e.preventDefault(); llApply(); }
            });
            return llPanel;
        }

        function llClose() {
            if (llPanel) { llPanel.hidden = true; }
            if (llCanvas) { llCanvas.focus(); }
        }

        function llApply() {
            var values = {};
            llPanel.querySelectorAll('[data-field]').forEach(function (input) {
                values[input.getAttribute('data-field')] =
                    input.type === 'checkbox' ? input.checked : input.value;
            });

            llPanel.hidden = true;
            llCanvas.focus();

            // Returning focus makes the syntax highlighter push its own copy back into the
            // textarea. Build and insert on the next tick so that sync lands first and our
            // edits are not overwritten by it.
            // Build may return a string, or a list of {from, to, text} edits applied in
            // document order from the end backwards, so earlier offsets stay valid.
            // Everything goes through insertContent — writing canvas.value directly is
            // undone by the syntax highlighter pushing its own copy back.
            setTimeout(function () {
                var selected = llCanvas.value.substring(llStart, llEnd),
                    result   = llBuild(values, selected, llCanvas);

                if (typeof result === 'string') {
                    result = [{ from: llStart, to: llEnd, text: result }];
                }

                result.sort(function (a, b) { return b.from - a.from; }).forEach(function (edit) {
                    llCanvas.selectionStart = edit.from;
                    llCanvas.selectionEnd   = edit.to;
                    QTags.insertContent(edit.text);
                });
            }, 0);
        }

        function llPanelButton(id, label, fields, build, title) {
            QTags.addButton(id, label, function (el, canvas) {
                llEnsurePanel();
                llCanvas = canvas;
                llStart  = canvas.selectionStart;
                llEnd    = canvas.selectionEnd;
                llBuild  = build;

                var list = typeof fields === 'function' ? fields(canvas, llStart, llEnd) : fields,
                    html = '';
                list.forEach(function (f) {
                    if (f.type === 'text') {
                        html += '<label'
                              + (f.showWhen ? ' data-show-when="' + f.showWhen.field
                                              + ':' + f.showWhen.is + '" hidden' : '')
                              + '>' + f.label
                              + (f.hint ? '<span class="ll-hint">' + f.hint + '</span>' : '')
                              + '<input type="text" data-field="' + f.name + '" value="' + (f.value || '') + '">'
                              + '</label>';
                        return;
                    }
                    if (f.type === 'checkbox') {
                        html += '<label class="ll-check"><input type="checkbox" data-field="' + f.name + '"'
                              + (f.checked ? ' checked' : '') + '>'
                              + '<span>' + f.label
                              + (f.hint ? '<span class="ll-hint">' + f.hint + '</span>' : '')
                              + '</span>'
                              + (f.colours ? '<span class="ll-swatch'
                                             + (f.asDiagram ? ' ll-diagram' : '')
                                             + '" data-swatch="' + f.name + '"></span>' : '')
                              + '</label>';
                        return;
                    }
                    html += '<label>' + f.label
                          + (f.hint ? '<span class="ll-hint">' + f.hint + '</span>' : '')
                          + (f.colours ? '<span class="ll-swatch-row">' : '')
                          + '<select data-field="' + f.name + '">';
                    f.options.forEach(function (o) {
                        html += '<option value="' + o[0] + '"'
                              + (o[0] === f.value ? ' selected' : '') + '>' + o[1] + '</option>';
                    });
                    html += '</select>'
                          + (f.colours ? '<span class="ll-swatch' + (f.asImage ? ' ll-icon' : '')
                                         + (f.asFill ? ' ll-fill' : '')
                                         + (f.asDiagram ? ' ll-diagram' : '')
                                         + '" data-swatch="' + f.name + '"></span></span>' : '')
                          + '</label>';
                });
                html += '<div class="ll-actions">'
                      + '<button type="button" class="button" data-ll-cancel>Cancel</button>'
                      + '<button type="button" class="button button-primary" data-ll-ok>Insert</button>'
                      + '</div>';
                llPanel.innerHTML = html;
                llPanel.querySelector('[data-ll-cancel]').addEventListener('click', llClose);
                llPanel.querySelector('[data-ll-ok]').addEventListener('click', llApply);

                list.forEach(function (f) {
                    if (!f.showWhen) { return; }
                    var owner  = llPanel.querySelector('[data-field="' + f.showWhen.field + '"]'),
                        target = llPanel.querySelector('[data-show-when="' + f.showWhen.field
                                                       + ':' + f.showWhen.is + '"]');
                    if (!owner || !target) { return; }
                    var toggle = function () {
                        target.hidden = owner.value !== f.showWhen.is;
                        if (!target.hidden) { target.querySelector('input').focus(); }
                    };
                    owner.addEventListener('change', toggle);
                    toggle();
                });

                list.forEach(function (f) {
                    if (!f.colours) { return; }
                    var select = llPanel.querySelector('[data-field="' + f.name + '"]'),
                        swatch = llPanel.querySelector('[data-swatch="' + f.name + '"]');
                    var paint = function () {
                        var current = select.type === 'checkbox'
                                    ? (select.checked ? 'true' : '')
                                    : select.value,
                            v = f.colours[current] || '';
                        if (f.asDiagram) { swatch.innerHTML = v; return; }
                        if (f.asImage) {
                            swatch.style.backgroundImage = v ? 'url("' + v + '")' : 'none';
                        } else if (f.asFill) {
                            swatch.style.backgroundColor = v || '#fff';
                        } else {
                            swatch.style.borderTopColor = v || '#c3c4c7';
                        }
                    };
                    select.addEventListener('change', paint);
                    paint();
                });

                var at = el.getBoundingClientRect();
                llPanel.style.top  = (at.bottom + window.pageYOffset + 4) + 'px';
                llPanel.style.left = (at.left + window.pageXOffset) + 'px';
                llPanel.hidden = false;
                var first = llPanel.querySelector('select, input');
                if (first) { first.focus(); }
            }, '', '', title);
        }

        llPanelButton('ll_image', 'image', [
            { name: 'size', label: 'Size', value: 'md', options: [
                ['', 'Default'], ['xs', 'xs'], ['sm', 'sm'], ['md', 'md'], ['lg', 'lg'], ['wide', 'wide']
            ] },
            // One control: a floated figure ignores margin:auto, so centred and floated
            // cannot both apply — and across 1020 existing images none set both.
            { name: 'align', label: 'Alignment', value: 'centre', options: [
                ['centre', 'Centred'], ['', 'Left'], ['float', 'Float right — text wraps around it']
            ] },
            { name: 'attr', type: 'text', label: 'Attribution id', value: 'my-id',
              hint: 'Matches the id on the page\'s [attribution] block' }
        ], function (v) {
            // The caption goes between the tags, not in caption=, so links can use double
            // quotes. image.php splits this content into caption + any [transcript].
            return '[ll-image url="" alt=""'
                 + (v.size ? ' size="' + v.size + '"' : '')
                 + (v.align === 'float' ? ' float="true"'
                    : v.align ? ' align="' + v.align + '"' : '')
                 + (v.attr ? ' attribution-id="' + v.attr + '"' : '')
                 + ']Subject of the image, image by '
                 + '<a href="https://unsplash.com/photos/...">Photographer name</a> via Unsplash.'
                 + '[/ll-image]';
        }, 'Insert an image — choose a size and whether it floats');

        // trim only colours the card for correct / incorrect / neutral; any other value
        // gives a plain top trim, which is why the dropdown offers no free text.
        llPanelButton('ll_card', 'card', [
            { name: 'title', type: 'text', label: 'Title' },
            { name: 'trim', label: 'Trim', hint: 'Coloured bar across the top', value: '',
              colours: { '': '', 'correct': '#008057', 'incorrect': '#e61e2a', 'neutral': '#fac800' },
              options: [
                ['', 'None'], ['correct', 'Correct'], ['incorrect', 'Incorrect'], ['neutral', 'Neutral']
            ] },
            { name: 'heading', label: 'Title heading level', value: '', options: [
                ['', 'Default (h3)'], ['h2', 'h2'], ['h3', 'h3'], ['h4', 'h4']
            ] },
            { name: 'purpose', type: 'text', label: 'Screen reader note',
              hint: 'What the card means, e.g. "Correct:" — read before the title' }
        ], function (v, selected) {
            return '[ll-card title="' + (v.title || '') + '"'
                 + (v.purpose ? ' purpose="' + v.purpose + '"' : '')
                 + (v.trim ? ' trim="' + v.trim + '"' : '')
                 + (v.heading ? ' heading-tag="' + v.heading + '"' : '')
                 + ']\n' + (selected || '<p>Card content here.</p>') + '\n[/ll-card]';
        }, 'Card — wraps the selection, with an optional coloured trim');

        llPanelButton('ll_title_icon', 'title icon', [
            // Mirrors the background-image in _title-icon.scss / _other.scss — the theme
            // bundle is not loaded in wp-admin, so the preview cannot reuse those classes.
            { name: 'type', label: 'Icon', value: 'maths-in-context', asImage: true,
              colours: {
                'maths-in-context':   'https://rmitlibrary.github.io/cdn/learninglab/illustration/subject-support/maths-and-statistics/maths-context-icon-pie.png',
                'aboriginal-flag':    'https://www.rmit.edu.au/content/dam/rmit/au/en/news/homepage/flag-red.png',
                'torres-strait-flag': 'https://www.rmit.edu.au/content/dam/rmit/au/en/news/homepage/flag-green.png',
                'quiz-icon':          "data:image/svg+xml,%3Csvg version='1.1' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E   %3Ccircle cx='20' cy='20' r='19.5' fill='%23000054'/%3E   %3Cpath d='M19.8,24.2c-1.7,0-3-1.3-3-3v-1.3c0-2.3,1.4-4.2,3.6-4.9.7-.2,1.2-.9,1.2-1.7,0-.9-.8-1.7-1.7-1.7-.4,0-.9.2-1.3.5-.3.3-.5.8-.5,1.2,0,1.7-1.3,3-3,3s-3-1.3-3-3,.8-4.1,2.3-5.5c1.5-1.5,3.5-2.2,5.6-2.2,4.2,0,7.6,3.5,7.6,7.6,0,3.2-1.8,6-4.7,7.3v.8c0,1.7-1.3,3-3,3ZM19.8,26.3c-2,0-3.5,1.6-3.5,3.5s1.6,3.5,3.5,3.5,3.5-1.6,3.5-3.5-1.6-3.5-3.5-3.5Z' fill='%23ffffff'/%3E %3C/svg%3E"
              },
              options: [
                ['maths-in-context', 'Maths in context'],
                ['aboriginal-flag', 'Aboriginal flag'],
                ['torres-strait-flag', 'Torres Strait Islander flag'],
                ['quiz-icon', 'Quiz']
            ] },
            { name: 'heading', label: 'Heading level', value: 'h3', options: [
                ['h2', 'h2'], ['h3', 'h3'], ['h4', 'h4']
            ] }
        ], function (v, selected) {
            return '[title-icon type="' + v.type + '" heading-tag="' + v.heading + '"]'
                 + (selected || 'Heading text') + '[/title-icon]';
        }, 'Heading with an icon before it');

        llPanelButton('ll_accordion', 'accordion', [
            { name: 'title', type: 'text', label: 'Title',
              hint: 'What a screen reader announces — say what is inside, not "Read more"' },
            { name: 'heading', label: 'Heading level', value: '', options: [
                ['', 'Default (h3)'], ['h2', 'h2'], ['h3', 'h3'], ['h4', 'h4']
            ] },
            { name: 'open', type: 'checkbox', label: 'Open by default' }
        ], function (v, selected) {
            return '[ll-accordion title="' + (v.title || '') + '"'
                 + (v.heading ? ' heading-tag="' + v.heading + '"' : '')
                 + (v.open ? ' open="true"' : '')
                 + ']\n'
                 + (selected || '<p>Accordion content here.</p>')
                 + '\n[/ll-accordion]';
        }, 'Accordion — wraps the selection, or inserts an empty one');

        llPanelButton('ll_transcript', 'transcript', [
            { name: 'title', type: 'text', label: 'Title',
              hint: 'Defaults to "Transcript" when left blank' }
        ], function (v, selected) {
            return '[transcript' + (v.title ? ' title="' + v.title + '"' : '') + ']\n'
                 + (selected || '<p>Paste the transcript here, one paragraph per speaker turn.</p>')
                 + '\n[/transcript]';
        }, 'Transcript — goes inside [ll-video]');

        llWrap('ll_attribution', 'attribution',
            '[attribution id="my-id"]', '[/attribution]',
            '[attribution id="my-id"][/attribution]',
            'Image credit — empty gives the default RMIT CC BY-NC line; add content to override it');

        // one-column moves the key above the content instead of beside it — the diagram
        // shows the two layouts rather than making the author remember which is which.
        var llCol = {
            // content beside the key
            '':     '<i style="flex:2"></i><i class="ll-key" style="flex:1"></i>',
            // key above, content full width beneath it
            'true': '<i style="flex:1;display:flex;flex-direction:column;gap:2px;background:none">'
                  + '<i class="ll-key" style="height:7px"></i><i style="flex:1"></i></i>'
        };

        llPanelButton('ll_highlight_text', 'highlight block', [
            { name: 'key', type: 'text', label: 'Key',
              hint: 'Labels separated by | — numbering is added for you' },
            { name: 'onecol', type: 'checkbox', label: 'Key above the content',
              hint: 'Full width instead of a column beside it',
              colours: llCol, asDiagram: true },
            { name: 'sr', type: 'text', label: 'Screen reader note',
              hint: 'Optional — read before the block' }
        ], function (v, selected) {
            var labels = (v.key || 'Label one|Label two').split('|')
                            .map(function (l, i) { return (i + 1) + ':' + l.trim(); })
                            .join('|');
            return '[highlight-text key="' + labels + '"'
                 + (v.onecol ? ' one-column="true"' : '')
                 + (v.sr ? ' screen-reader="' + v.sr + '"' : '')
                 + ']\n'
                 + (selected || '<p>Text with [hl id="1"]highlighted[/hl] words.</p>')
                 + '\n[/highlight-text]';
        }, 'Highlight block — the coloured key and the text it applies to');

        // The id picks a colour from the key on the enclosing [highlight-text] block, so
        // read that key, offer its real labels, and append a new one when asked.
        function llFindKey(canvas, at) {
            var before = canvas.value.substring(0, at),
                open   = before.lastIndexOf('[highlight-text'),
                close  = before.lastIndexOf('[/highlight-text]');
            if (open <= close) { return null; }
            var match = /key="([^"]*)"/.exec(canvas.value.substring(open));
            if (!match) { return null; }
            return {
                key: match[1],
                at:  open + match.index + match[0].indexOf(match[1])
            };
        }

        llPanelButton('ll_hl', 'highlight word', function (canvas, start) {
            var found   = llFindKey(canvas, start),
                entries = found ? found.key.split('|').map(function (e) { return e.trim(); }).filter(Boolean) : [],
                options = entries.map(function (e) {
                    var n = e.split(':')[0].trim();
                    return [n, e];
                });
            // The new label takes the next free number, so its colour is knowable now.
            var next = 1;
            entries.forEach(function (e) {
                var n = parseInt(e.split(':')[0], 10);
                if (!isNaN(n) && n >= next) { next = n + 1; }
            });

            options.push(['__new__', found ? 'Add a new label… (' + next + ')' : 'Set a label…']);
            // Mirrors .highlight-1 … .highlight-9 in the compiled stylesheet.
            var palette = { '1': '#fac800', '2': '#70cfff', '3': '#81e996', '4': '#eb7ab6',
                            '5': '#f5904d', '6': '#e66cef', '7': '#00f2b6', '8': '#99afff',
                            '9': '#cef218' };
            // .highlight-10 has no colour of its own, so past 9 there is nothing to show.
            palette['__new__'] = palette[String(next)] || '';
            return [
                { name: 'id', label: 'Highlight',
                  hint: found ? '' : 'Cursor is not inside a [highlight-text] block',
                  value: options.length > 1 ? options[0][0] : '__new__', options: options,
                  asFill: true, colours: palette },
                { name: 'label', type: 'text', label: 'New label',
                  showWhen: { field: 'id', is: '__new__' } },
                { name: 'sr', type: 'text', label: 'Screen reader note',
                  hint: 'What the colour conveys — 88% of highlights set this' },
                { name: 'nosup', type: 'checkbox', label: 'Hide the superscript number',
                  hint: 'The number shown after the highlighted word' }
            ];
        }, function (v, selected, canvas) {
            var id = v.id;

            if (id === '__new__') {
                var label = (v.label || '').trim();
                if (!label) { return '[hl id="1"]' + selected + '[/hl]'; }

                var found = llFindKey(canvas, canvas.selectionStart);
                if (!found) { return '[hl id="1"]' + selected + '[/hl]'; }

                var next = 1;
                found.key.split('|').forEach(function (e) {
                    var n = parseInt(e.split(':')[0], 10);
                    if (!isNaN(n) && n >= next) { next = n + 1; }
                });

                var newKey = (found.key ? found.key + '|' : '') + next + ':' + label;

                // Two edits: the key, and the highlight itself. The key always sits earlier
                // in the document, so applying back to front leaves its offsets untouched.
                return [
                    { from: found.at, to: found.at + found.key.length, text: newKey },
                    { from: llStart,  to: llEnd, text: llHl(next, v, selected) }
                ];
            }

            return llHl(id, v, selected);
        }, 'Colour a word or phrase — offers the labels from the block you are inside');

        function llHl(id, v, selected) {
            return '[hl id="' + id + '"'
                 + (v.sr ? ' screen-reader="' + v.sr + '"' : '')
                 + (v.nosup ? ' superscript="false"' : '')
                 + ']' + selected + '[/hl]';
        }

        jQuery(function ($) {
            var bar = $('#ed_toolbar');
            bar.find('[id$="_ll_image"]').before('<span class="ll-sep" aria-hidden="true"></span>');
            bar.find('[id$="_ll_highlight_text"]').before('<span class="ll-sep" aria-hidden="true"></span>');
        });
    }
    </script>
    <?php
}, 100);
