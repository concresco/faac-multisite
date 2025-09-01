'use strict';

(function () {
    var oftb_toolbarActive = false;

    // CallBack function to add content to Classic MCE editor //
    window.wpcp_add_content_to_mce = function (content) {
        tinymce.activeEditor.execCommand('mceInsertContent', false, content);
        tinymce.activeEditor.windowManager.close();
        tinymce.activeEditor.focus();
    };

    tinymce.create('tinymce.plugins.outofthebox', {
        init: function (ed, url) {
            var t = this;
            t.url = url;

            ed.addCommand('mceOutoftheBox', function (query) {
                ed.windowManager.open(
                    {
                        file:
                            ajaxurl +
                            '?action=outofthebox-getpopup&type=modules&' +
                            query +
                            '&callback=wpcp_add_content_to_mce',
                        width: 1280,
                        height: 680,
                        inline: 1,
                    },
                    {
                        plugin_url: url,
                    }
                );
            });
            ed.addCommand('mceOutoftheBox_links', function () {
                ed.windowManager.open(
                    {
                        file: ajaxurl + '?action=outofthebox-getpopup&type=links&callback=wpcp_add_content_to_mce',
                        width: 1280,
                        height: 680,
                        inline: 1,
                    },
                    {
                        plugin_url: url,
                    }
                );
            });
            ed.addCommand('mceOutoftheBox_embed', function () {
                ed.windowManager.open(
                    {
                        file: ajaxurl + '?action=outofthebox-getpopup&type=embedded&callback=wpcp_add_content_to_mce',
                        width: 1280,
                        height: 680,
                        inline: 1,
                    },
                    {
                        plugin_url: url,
                    }
                );
            });
            ed.addButton('outofthebox', {
                title: 'Out-of-the-Box module',
                image: url + '/../../css/images/dropbox_logo.png',
                cmd: 'mceOutoftheBox',
            });
            ed.addButton('outofthebox_links', {
                title: 'Out-of-the-Box links',
                image: url + '/../../css/images/dropbox_logo_link.png',
                cmd: 'mceOutoftheBox_links',
            });
            ed.addButton('outofthebox_embed', {
                title: 'Embed Files from Dropbox',
                image: url + '/../../css/images/dropbox_logo_embedded.png',
                cmd: 'mceOutoftheBox_embed',
            });

            ed.on('mousedown', function (event) {
                if (ed.dom.getParent(event.target, '#wpcp-mce-toolbar')) {
                    if (tinymce.Env.ie) {
                        // Stop IE > 8 from making the wrapper resizable on mousedown
                        event.preventDefault();
                    }
                } else {
                    removeOftBToolbar(ed);
                }
            });

            ed.on('mouseup', function (event) {
                var image,
                    node = event.target,
                    dom = ed.dom;

                // Don't trigger on right-click
                if (event.button && event.button > 1) {
                    return;
                }

                if (node.nodeName === 'DIV' && dom.getParent(node, '#wpcp-mce-toolbar')) {
                    image = dom.select('img[data-wp-oftbselect]')[0];

                    if (image) {
                        ed.selection.select(image);

                        if (dom.hasClass(node, 'remove')) {
                            removeOftBToolbar(ed);
                            removeOftBImage(image, ed);
                        } else if (dom.hasClass(node, 'edit')) {
                            var raw_content = ed.selection.getContent();
                            var shortcode = raw_content.replace('</p>', '').replace('<p>', '');
                            var query = 'shortcode=' + toBinary(shortcode);
                            removeOftBToolbar(ed);
                            ed.execCommand('mceOutoftheBox', query);
                        }
                    }
                } else if (
                    node.nodeName === 'IMG' &&
                    !ed.dom.getAttrib(node, 'data-wp-oftbselect') &&
                    isOftBPlaceholder(node, ed)
                ) {
                    addOftBToolbar(node, ed);
                } else if (node.nodeName !== 'IMG') {
                    removeOftBToolbar(ed);
                }
            });

            ed.on('keydown', function (event) {
                var keyCode = event.keyCode;
                // Key presses will replace the image so we need to remove the toolbar
                if (oftb_toolbarActive) {
                    if (
                        event.ctrlKey ||
                        event.metaKey ||
                        event.altKey ||
                        (keyCode < 48 && keyCode > 90) ||
                        keyCode > 186
                    ) {
                        return;
                    }

                    removeOftBToolbar(ed);
                }
            });

            ed.on('cut', function () {
                removeOftBToolbar(ed);
            });

            ed.on('BeforeSetcontent', function (ed) {
                ed.content = t._do_oftb_shortcode(ed.content, t.url);
            });
            ed.on('PostProcess', function (ed) {
                if (ed.get) ed.content = t._get_oftb_shortcode(ed.content);
            });
        },
        _do_oftb_shortcode: function (co, url) {
            return co.replace(/\[outofthebox([^\]]*)\]/g, function (a, b) {
                return (
                    '<img src="' +
                    url +
                    '/../../css/images/transparant.png" class="wpcp-mce-shortcode wpcp-mce-outofthebox-shortcode mceItem" title="Out-of-the-Box" data-mce-placeholder="1" data-code="' +
                    toBinary(b) +
                    '"/>'
                );
            });
        },
        _get_oftb_shortcode: function (co) {
            function getAttr(s, n) {
                n = new RegExp(n + '="([^"]+)"', 'g').exec(s);
                return n ? n[1] : '';
            }

            return co.replace(/(?:<p[^>]*>)*(<img[^>]+>)(?:<\/p>)*/g, function (a, im) {
                var cls = getAttr(im, 'class');

                if (cls.indexOf('wpcp-mce-outofthebox-shortcode') != -1)
                    return '<p>[outofthebox ' + tinymce.trim(fromBinary(getAttr(im, 'data-code'))) + ']</p>';

                return a;
            });
        },
        createControl: function (n, cm) {
            return null;
        },
    });

    tinymce.PluginManager.add('outofthebox', tinymce.plugins.outofthebox);

    function removeOftBImage(node, editor) {
        editor.dom.remove(node);
        removeOftBToolbar(editor);
    }

    function addOftBToolbar(node, editor) {
        var toolbarHtml,
            toolbar,
            dom = editor.dom;

        removeOftBToolbar(editor);

        // Don't add to placeholders
        if (!node || node.nodeName !== 'IMG' || !isOftBPlaceholder(node, editor)) {
            return;
        }

        dom.setAttrib(node, 'data-wp-oftbselect', 1);

        toolbarHtml =
            '<div class="dashicons dashicons-edit edit" data-mce-bogus="1"></div>' +
            '<div class="dashicons dashicons-no-alt remove" data-mce-bogus="1"></div>';

        toolbar = dom.create(
            'div',
            {
                id: 'wpcp-mce-toolbar',
                'data-mce-bogus': '1',
                contenteditable: false,
                class: 'wpcp-mce-toolbar',
            },
            toolbarHtml
        );

        var parentDiv = node.parentNode;
        parentDiv.insertBefore(toolbar, node);

        oftb_toolbarActive = true;
    }

    function removeOftBToolbar(editor) {
        var toolbar = editor.dom.get('wpcp-mce-toolbar');

        if (toolbar) {
            editor.dom.remove(toolbar);
        }

        editor.dom.setAttrib(editor.dom.select('img[data-wp-oftbselect]'), 'data-wp-oftbselect', null);

        oftb_toolbarActive = false;
    }

    function isOftBPlaceholder(node, editor) {
        var dom = editor.dom;

        if (dom.hasClass(node, 'wpcp-mce-outofthebox-shortcode')) {
            return true;
        }

        return false;
    }

    function toBinary(str) {
        return btoa(
            encodeURIComponent(str).replace(/%([0-9A-F]{2})/g, function toSolidBytes(match, p1) {
                return String.fromCharCode('0x' + p1);
            })
        );
    }

    function fromBinary(str) {
        return decodeURIComponent(
            atob(str)
                .split('')
                .map(function (c) {
                    return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
                })
                .join('')
        );
    }
})();
