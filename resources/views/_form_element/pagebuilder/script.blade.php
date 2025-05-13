<script src="{{ asset('/admin/vendors/jquery-ui/jquery-ui-git.js') }}"></script>

<script>
    $(document).ready(function () {
        $("#content-pagebuilder").sortable({
            change: function(event, ui) {
                // console.log('Sortable changed');
            },
            update: function(event, ui) {
                // console.log('Sortable updated');
            },
            sort: function(e) {
                // console.log('Sortable sorted');
            },
            stop: function(event, ui) {
                // console.log('Sortable stopped');
                var section = ui.item[0].id.split("_");
                var text_id = 'v_element_content_text_'+section[2];

                tinymce.remove('#'+text_id);
                init_tinymce_single('#'+text_id);
            }
        });
    });

    function delete_content_element(id) {
        if(confirm("Are you sure to delete this content (this action can't be undone) ?")) {
            $('#pagebuilder_elm_'+id).remove();
            $('#hidden_'+id).remove();
            return true;
        }
        return false;
    }

    function set_section_name(id, value) {
        $('#section'+id).html(value);
    }

    function add_content_element(type, collapsed = false) {
        var html_content_element = '';
        var uniqid = Date.now();

        switch (type) {
            case 'text':
                html_content_element = set_html_text(collapsed, uniqid);
                break;

            case 'image':
                html_content_element = set_html_image(collapsed, uniqid);
                break;

            case 'image & text':
                html_content_element = set_html_image_text(collapsed, uniqid);
                break;

            case 'video':
                html_content_element = set_html_video(collapsed, uniqid);
                break;

            case 'video & text':
                html_content_element = set_html_video_text(collapsed, uniqid);
                break;
        
            case 'plain text':
                html_content_element = set_html_plaintext(collapsed, uniqid);
                break;
            
            default:
                alert('NO CONTENT ELEMENT TYPE SELECTED');
                return false;
                break;
        }

        $("#content-pagebuilder").append(html_content_element);

        // initialize TinyMCE
        switch (type) {
            case 'text':
                init_tinymce_single('#v_element_content_text_'+uniqid);
                break;

            case 'image & text':
                init_tinymce_single('#v_element_content_text_'+uniqid);
                break;

            case 'video & text':
                init_tinymce_single('#v_element_content_text_'+uniqid);
                break;
        }
    }

    function init_tinymce() {
        tinymce.init({ 
            selector:'.text-editor', 
            branding: false,
            height: 500,
            plugins: [
                'link image imagetools table spellchecker charmap fullscreen emoticons help preview searchreplace code lists advlist'
            ],
            toolbar: [
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
                    name: 'view', 
                    items: [ 'searchreplace', 'preview', 'fullscreen', 'code' ]
                },
                {
                    name: 'help', 
                    items: [ 'help' ]
                }
            ],
            toolbar_sticky: true
        });
    }

    function init_tinymce_single(elm_id) {
        tinymce.init({ 
            selector: elm_id, 
            branding: false,
            height: 500,
            plugins: [
                'link image imagetools table spellchecker charmap fullscreen emoticons help preview searchreplace code lists advlist'
            ],
            toolbar: [
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
                    name: 'view', 
                    items: [ 'searchreplace', 'preview', 'fullscreen', 'code' ]
                },
                {
                    name: 'help', 
                    items: [ 'help' ]
                }
            ],
            toolbar_sticky: true
        });
    }

    // SET CONTENT ELEMENT BELOW *********

    function set_html_text(collapsed, uniqid) {
        var html = '<div class="panel" id="pagebuilder_elm_'+uniqid+'">';
        html += '<input type="hidden" name="v_element_type['+uniqid+']" value="text">';

        if (collapsed) {
            html += '<a class="panel-heading" role="tab" data-toggle="collapse" data-parent="#content-pagebuilder" href="#collapse'+uniqid+'" aria-expanded="false">';
        } else {
            html += '<a class="panel-heading" role="tab" data-toggle="collapse" data-parent="#content-pagebuilder" href="#collapse'+uniqid+'" aria-expanded="true">';
        }

                html += '<h4 class="panel-title">Text - Section <i id=section'+uniqid+'></i><span class="pull-right"><i class="fa fa-sort"></i><i class="fa fa-trash" style="color:red; margin-left: 20px;" onclick="delete_content_element('+uniqid+')"></i></span></h4>';
            html += '</a>';

        if (collapsed) {
            html += '<div id="collapse'+uniqid+'" class="panel-collapse collapse" role="tabpanel">';
        } else {
            html += '<div id="collapse'+uniqid+'" class="panel-collapse collapse in" role="tabpanel">';
        }
            
                html += '<div class="panel-body">';
                    html += '<div class="form-group">';
                        html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">Section <span class="required">*</span></label>';
                        html += '<div class="col-md-6 col-sm-6 col-xs-12"><input type="text" autocomplete="off" value="" name="v_element_section['+uniqid+']" required="required" class="form-control col-md-7 col-xs-12" onblur="set_section_name('+uniqid+', this.value)"></div>';
                    html += '</div>';
                    html += '<div class="form-group">';
                        html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">Text <span class="required">*</span></label>';
                        html += '<div class="col-md-9 col-sm-9 col-xs-12"><textarea name="v_element_content_text['+uniqid+']" id="v_element_content_text_'+uniqid+'" class="form-control col-md-7 col-xs-12 text-editor"></textarea></div>';
                    html += '</div>';
                html += '</div>';
            html += '</div>';
        html += '</div>';

        return html;
    }
    
    function set_html_image(collapsed, uniqid) {
        var default_value = '{{ asset("/images/no-image.png") }}';
        var html = '<div class="panel" id="pagebuilder_elm_'+uniqid+'">';
        html += '<input type="hidden" name="v_element_type['+uniqid+']" value="image">';
        
        if (collapsed) {
            html += '<a class="panel-heading" role="tab" data-toggle="collapse" data-parent="#content-pagebuilder" href="#collapse'+uniqid+'" aria-expanded="false">';
        } else {
            html += '<a class="panel-heading" role="tab" data-toggle="collapse" data-parent="#content-pagebuilder" href="#collapse'+uniqid+'" aria-expanded="true">';
        }
            
                html += '<h4 class="panel-title">Image - Section <i id=section'+uniqid+'></i><span class="pull-right"><i class="fa fa-sort"></i><i class="fa fa-trash" style="color:red; margin-left: 20px;" onclick="delete_content_element('+uniqid+')"></i></span></h4>';
            html += '</a>';
        
        if (collapsed) {
            html += '<div id="collapse'+uniqid+'" class="panel-collapse collapse" role="tabpanel">';
        } else {
            html += '<div id="collapse'+uniqid+'" class="panel-collapse collapse in" role="tabpanel">';
        }

                html += '<div class="panel-body">';
                    html += '<div class="form-group">';
                        html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">Section <span class="required">*</span></label>';
                        html += '<div class="col-md-6 col-sm-6 col-xs-12"><input type="text" autocomplete="off" value="" name="v_element_section['+uniqid+']" required="required" class="form-control col-md-7 col-xs-12" onblur="set_section_name('+uniqid+', this.value)"></div>';
                    html += '</div>';
                    html += '<div class="form-group">';
                        html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">Image <span class="required">*</span></label>';
                        html += '<div class="col-md-6 col-sm-6 col-xs-12">';
                            html += '<img src="'+default_value+'" style="max-width: 200px;max-height: 200px;display: block;margin-left: auto;margin-right: auto;">';
                            html += '<input type="file" name="v_element_content_image['+uniqid+']" required="required" class="form-control col-md-7 col-xs-12" accept=".jpg, .jpeg, .png" onchange="readURL(this, \'before\');" style="margin-top:5px">';
                        html += '</div>';
                    html += '</div>';
                html += '</div>';
            html += '</div>';
        html += '</div>';

        return html;
    }

    function set_html_image_text(collapsed, uniqid) {
        var default_value = '{{ asset("/images/no-image.png") }}';
        var html = '<div class="panel" id="pagebuilder_elm_'+uniqid+'">';
        html += '<input type="hidden" name="v_element_type['+uniqid+']" value="image & text">';
        
        if (collapsed) {
            html += '<a class="panel-heading" role="tab" data-toggle="collapse" data-parent="#content-pagebuilder" href="#collapse'+uniqid+'" aria-expanded="false">';
        } else {
            html += '<a class="panel-heading" role="tab" data-toggle="collapse" data-parent="#content-pagebuilder" href="#collapse'+uniqid+'" aria-expanded="true">';
        }

                html += '<h4 class="panel-title">Image & Text - Section <i id=section'+uniqid+'></i><span class="pull-right"><i class="fa fa-sort"></i><i class="fa fa-trash" style="color:red; margin-left: 20px;" onclick="delete_content_element('+uniqid+')"></i></span></h4>';
            html += '</a>';
        
        if (collapsed) {
            html += '<div id="collapse'+uniqid+'" class="panel-collapse collapse" role="tabpanel">';
        } else {
            html += '<div id="collapse'+uniqid+'" class="panel-collapse collapse in" role="tabpanel">';
        }

                html += '<div class="panel-body">';
                    html += '<div class="form-group">';
                        html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">Section <span class="required">*</span></label>';
                        html += '<div class="col-md-6 col-sm-6 col-xs-12"><input type="text" autocomplete="off" value="" name="v_element_section['+uniqid+']" required="required" class="form-control col-md-7 col-xs-12" onblur="set_section_name('+uniqid+', this.value)"></div>';
                    html += '</div>';
                    html += '<div class="form-group">';
                        html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">Image <span class="required">*</span></label>';
                        html += '<div class="col-md-6 col-sm-6 col-xs-12">';
                            html += '<img src="'+default_value+'" style="max-width: 200px;max-height: 200px;display: block;margin-left: auto;margin-right: auto;">';
                            html += '<input type="file" name="v_element_content_image['+uniqid+']" required="required" class="form-control col-md-7 col-xs-12" accept=".jpg, .jpeg, .png" onchange="readURL(this, \'before\');" style="margin-top:5px">';
                        html += '</div>';
                    html += '</div>';
                    html += '<div class="form-group">';
                        html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">Text <span class="required">*</span></label>';
                        html += '<div class="col-md-9 col-sm-9 col-xs-12"><textarea name="v_element_content_text['+uniqid+']" id="v_element_content_text_'+uniqid+'" class="form-control col-md-7 col-xs-12 text-editor"></textarea></div>';
                    html += '</div>';
                    html += '<div class="form-group">';
                        html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">Text Position on Image <span class="required">*</span></label>';
                        html += '<div class="col-md-6 col-sm-6 col-xs-12">';
                            html += '<select name="v_text_position['+uniqid+']" required="required" class="form-control col-md-7 col-xs-12">';
                                html += '<option value="">- Please Choose One -</option>';
                                html += '<option value="right">Right</option>';
                                html += '<option value="left">Left</option>';
                            html += '</select>';
                        html += '</div>';
                    html += '</div>';
                html += '</div>';
            html += '</div>';
        html += '</div>';

        return html;
    }

    function set_html_video(collapsed, uniqid) {
       var html = '<div class="panel" id="pagebuilder_elm_'+uniqid+'">';
        html += '<input type="hidden" name="v_element_type['+uniqid+']" value="video">';
            
        if (collapsed) {
            html += '<a class="panel-heading" role="tab" data-toggle="collapse" data-parent="#content-pagebuilder" href="#collapse'+uniqid+'" aria-expanded="false">';
        } else {
            html += '<a class="panel-heading" role="tab" data-toggle="collapse" data-parent="#content-pagebuilder" href="#collapse'+uniqid+'" aria-expanded="true">';
        }

                html += '<h4 class="panel-title">Video - Section <i id=section'+uniqid+'></i><span class="pull-right"><i class="fa fa-sort"></i><i class="fa fa-trash" style="color:red; margin-left: 20px;" onclick="delete_content_element('+uniqid+')"></i></span></h4>';
            html += '</a>';
            
        if (collapsed) {
            html += '<div id="collapse'+uniqid+'" class="panel-collapse collapse" role="tabpanel">';
        } else {
            html += '<div id="collapse'+uniqid+'" class="panel-collapse collapse in" role="tabpanel">';
        }

                html += '<div class="panel-body">';
                    html += '<div class="form-group">';
                        html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">Section <span class="required">*</span></label>';
                        html += '<div class="col-md-6 col-sm-6 col-xs-12"><input type="text" autocomplete="off" value="" name="v_element_section['+uniqid+']" required="required" class="form-control col-md-7 col-xs-12" onblur="set_section_name('+uniqid+', this.value)"></div>';
                    html += '</div>';
                    html += '<div class="form-group">';
                        html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">Video (YouTube URL) <span class="required">*</span></label>';
                        html += '<div class="col-md-6 col-sm-6 col-xs-12"><input type="text" autocomplete="off" name="v_element_content_video['+uniqid+']" required="required" placeholder="https://www.youtube.com/watch?v=XXXX" class="form-control col-md-7 col-xs-12"></div>';
                    html += '</div>';
                html += '</div>';
            html += '</div>';
        html += '</div>';

        return html;
    }

    function set_html_video_text(collapsed, uniqid) {
        var html = '<div class="panel" id="pagebuilder_elm_'+uniqid+'">';
        html += '<input type="hidden" name="v_element_type['+uniqid+']" value="video & text">';
            
        if (collapsed) {
            html += '<a class="panel-heading" role="tab" data-toggle="collapse" data-parent="#content-pagebuilder" href="#collapse'+uniqid+'" aria-expanded="false">';
        } else {
            html += '<a class="panel-heading" role="tab" data-toggle="collapse" data-parent="#content-pagebuilder" href="#collapse'+uniqid+'" aria-expanded="true">';
        }

                html += '<h4 class="panel-title">Video & Text - Section <i id=section'+uniqid+'></i><span class="pull-right"><i class="fa fa-sort"></i><i class="fa fa-trash" style="color:red; margin-left: 20px;" onclick="delete_content_element('+uniqid+')"></i></span></h4>';
            html += '</a>';
            
        if (collapsed) {
            html += '<div id="collapse'+uniqid+'" class="panel-collapse collapse" role="tabpanel">';
        } else {
            html += '<div id="collapse'+uniqid+'" class="panel-collapse collapse in" role="tabpanel">';
        }

                html += '<div class="panel-body">';
                    html += '<div class="form-group">';
                        html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">Section <span class="required">*</span></label>';
                        html += '<div class="col-md-6 col-sm-6 col-xs-12"><input type="text" autocomplete="off" value="" name="v_element_section['+uniqid+']" required="required" class="form-control col-md-7 col-xs-12" onblur="set_section_name('+uniqid+', this.value)"></div>';
                    html += '</div>';
                    html += '<div class="form-group">';
                        html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">Video (YouTube URL) <span class="required">*</span></label>';
                        html += '<div class="col-md-6 col-sm-6 col-xs-12"><input type="text" autocomplete="off" name="v_element_content_video['+uniqid+']" required="required" placeholder="https://www.youtube.com/watch?v=XXXX" class="form-control col-md-7 col-xs-12"></div>';
                    html += '</div>';
                    html += '<div class="form-group">';
                        html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">Text <span class="required">*</span></label>';
                        html += '<div class="col-md-9 col-sm-9 col-xs-12"><textarea name="v_element_content_text['+uniqid+']" id="v_element_content_text_'+uniqid+'" class="form-control col-md-7 col-xs-12 text-editor"></textarea></div>';
                    html += '</div>';
                    html += '<div class="form-group">';
                        html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">Text Position on Video <span class="required">*</span></label>';
                        html += '<div class="col-md-6 col-sm-6 col-xs-12">';
                            html += '<select name="v_text_position['+uniqid+']" required="required" class="form-control col-md-7 col-xs-12">';
                                html += '<option value="">- Please Choose One -</option>';
                                html += '<option value="right">Right</option>';
                                html += '<option value="left">Left</option>';
                            html += '</select>';
                        html += '</div>';
                    html += '</div>';
                html += '</div>';
            html += '</div>';
        html += '</div>';

        return html;
    }

    function set_html_plaintext(collapsed, uniqid) {
        var html = '<div class="panel" id="pagebuilder_elm_'+uniqid+'">';
        html += '<input type="hidden" name="v_element_type['+uniqid+']" value="plain text">';

        if (collapsed) {
            html += '<a class="panel-heading" role="tab" data-toggle="collapse" data-parent="#content-pagebuilder" href="#collapse'+uniqid+'" aria-expanded="false">';
        } else {
            html += '<a class="panel-heading" role="tab" data-toggle="collapse" data-parent="#content-pagebuilder" href="#collapse'+uniqid+'" aria-expanded="true">';
        }

                html += '<h4 class="panel-title">Script - Section <i id=section'+uniqid+'></i><span class="pull-right"><i class="fa fa-sort"></i><i class="fa fa-trash" style="color:red; margin-left: 20px;" onclick="delete_content_element('+uniqid+')"></i></span></h4>';
            html += '</a>';

        if (collapsed) {
            html += '<div id="collapse'+uniqid+'" class="panel-collapse collapse" role="tabpanel">';
        } else {
            html += '<div id="collapse'+uniqid+'" class="panel-collapse collapse in" role="tabpanel">';
        }
            
                html += '<div class="panel-body">';
                    html += '<div class="form-group">';
                        html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">Section <span class="required">*</span></label>';
                        html += '<div class="col-md-6 col-sm-6 col-xs-12"><input type="text" autocomplete="off" value="" name="v_element_section['+uniqid+']" required="required" class="form-control col-md-7 col-xs-12" onblur="set_section_name('+uniqid+', this.value)"></div>';
                    html += '</div>';
                    html += '<div class="form-group">';
                        html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">Content <span class="required">*</span></label>';
                        html += '<div class="col-md-9 col-sm-9 col-xs-12"><textarea name="v_element_content_text['+uniqid+']" required="required" class="form-control col-md-7 col-xs-12" rows="10"></textarea></div>';
                    html += '</div>';
                html += '</div>';
            html += '</div>';
        html += '</div>';

        return html;
    }
</script>