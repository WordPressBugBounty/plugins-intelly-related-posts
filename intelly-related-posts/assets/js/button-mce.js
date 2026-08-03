(function() {
    // Nonce for the ui_button_editor do_action dispatcher case, exposed as a
    // global by button-mce.php (admin footer).
    function IRP_mceNonce() {
        return (window.irp_mce && window.irp_mce.nonce) ? window.irp_mce.nonce : '';
    }
    tinymce.PluginManager.add('irp_mce_button', function(editor, url) {
        editor.addButton('irp_mce_button', {
            title: 'Inline Related Posts'
            , type: 'menubutton'
            , icon: 'icon irp-own-icon'
            , image : url + '/../images/repeat.png'
            , menu: [
                {
                    text: 'Inline Related Post'
                    , onclick: function() {
                        var code='[irp]';
                        editor.insertContent(code);
                    }
                }
                , {
                    text: 'Custom Related Post'
                    , onclick: function() {
                        editor.windowManager.open({
                            title: 'Choose a post'
                            , width: 350
                            , height: 250
                            , file: ajaxurl+'?action=do_action&irp_action=ui_button_editor&irp_post_type=post&nonce='+IRP_mceNonce()
                            , inline: 1
                            , resizable: false
                        });
                    }
                }
                , {
                    text: 'Custom Related Page'
                    , onclick: function() {
                        editor.windowManager.open({
                            title: 'Choose a page'
                            , width: 350
                            , height: 250
                            , file: ajaxurl+'?action=do_action&irp_action=ui_button_editor&irp_post_type=page&nonce='+IRP_mceNonce()
                            , inline: 1
                            , resizable: false
                        });
                    }
                }
           ]
        });
    });
})();