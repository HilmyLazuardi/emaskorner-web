<script src="{{ asset('vendors/tinymce/js/tinymce/tinymce.min.js') }}"></script>

<script>
    function init_tinymce(elm) {
        if (typeof tinymce === "undefined") {
            alert('TinyMCE library is not included');
            return;
        }

        tinymce.init({ 
            selector: elm, 
            branding: false,
            height: 500,
            plugins: [
                'link image imagetools table spellchecker charmap fullscreen emoticons help preview searchreplace code lists advlist'
            ],
            toolbar: [
                {
                    name: 'view', 
                    items: [ 'fullscreen', 'code', 'preview' ]
                },
                {
                    name: 'history', 
                    items: [ 'undo', 'redo' ]
                },
                {
                    name: 'styles', 
                    items: [ 'styleselect' ]
                },
                {
                    name: 'formatting', 
                    items: [ 'bold', 'italic', 'underline' ]
                },
                {
                    name: 'ordinal', 
                    items: [ 'bullist', 'numlist']
                },
                {
                    name: 'alignment', 
                    items: [ 'alignleft', 'aligncenter', 'alignright', 'alignjustify' ]
                },
                {
                    name: 'indentation', 
                    items: [ 'outdent', 'indent' ]
                },
                {
                    name: 'insert', 
                    items: [ 'link', 'image', 'charmap', 'emoticons' ]
                },
                {
                    name: 'help', 
                    items: [ 'searchreplace', 'help' ]
                }
            ],
            toolbar_sticky: false,
            setup: function(editor) {
                editor.on('keyup', function(e) {
                    // Saves all contents from TinyMCE to Textarea
                    tinyMCE.triggerSave();
                });
            }
        });
    }

    // Initialize TinyMCE
    init_tinymce('.custom-text-editor');
</script>