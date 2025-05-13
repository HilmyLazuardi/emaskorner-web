/**
 * PageBuilder - Form (Build form pages using content elements)
 * Version: 1.0.8
 *
 * Copyright (c) KINIDI Tech and other contributors
 * Released under the MIT license.
 * For more information, see https://kiniditech.com/ or https://github.com/vickzkater
 *
 * PageBuilder depends on several libraries, such as:
 * - jQuery (https://jquery.com/download/)
 * - jQuery UI (https://jqueryui.com/download/)
 * - Bootstrap (https://getbootstrap.com/docs/4.3/getting-started/download/)
 * - TinyMCE (https://www.tiny.cloud/get-tiny/downloads/)
 *
 * Support Form Elements:
 * - Multiple Choice (Text)
 * - Multiple Choice (Text & Image)
 * - Checkboxes (Text)
 * - Checkboxes (Text & Image)
 * - Drop-down
 * - Linear Scale
 */

// SET DEFAULT UPLOADED PATH
if (typeof pagebuilder_uploaded_path == "undefined") {
    var pagebuilder_uploaded_path = "";
}

// SET DEFAULT STATUS USING RICHTEXT FOR QUESTION TEXT
if (typeof pagebuilder_question_text_using_richtext == "undefined") {
    var pagebuilder_question_text_using_richtext = false;
}

// SET DEFAULT STATUS USING RICHTEXT FOR OPTION TEXT
if (typeof pagebuilder_option_text_using_richtext == "undefined") {
    var pagebuilder_option_text_using_richtext = false;
}

// SET DEFAULT STATUS USING RICHTEXT FOR OPTION RESPONSE
if (typeof pagebuilder_option_response_using_richtext == "undefined") {
    var pagebuilder_option_response_using_richtext = false;
}

// SET DEFAULT STATUS USING QUESTION MEDIA
if (typeof pagebuilder_question_media == "undefined") {
    var pagebuilder_question_media = false;
}

// SET DEFAULT STATUS FOR ENABLE REQUIRED STATUS
if (typeof pagebuilder_enable_required_status == "undefined") {
    var pagebuilder_enable_required_status = false;
}

// SET DEFAULT TOTAL OPTIONS
if (typeof pagebuilder_total_options == "undefined") {
    var pagebuilder_total_options = 4;
}

function scroll_to_element_id(identifier) {
    var elm_identifier = document.getElementById(identifier);
    if (elm_identifier != null) {
        setTimeout(function () {
            elm_identifier.scrollIntoView({
                behavior: 'smooth'
            });
        }, 500);
    }
}

function initialize_sortable_content_in_form(container) {
    if (typeof $.fn.sortable === "undefined") {
        alert("jQuery UI library is not included");
        return;
    }

    $(container).sortable({
        sort: function (e) {
            // console.log('Sortable sorted');
        },
        stop: function (event, ui) {
            // console.log('Sortable stopped');

            tinymce.remove(".page-element-text-editor");
            initialize_tinymce(".page-element-text-editor");
        },
    });
}

function delete_option(elm_id) {
    if (confirm("Are you sure to delete this option (this action can't be undone) ?")) {
        $(elm_id).remove();
        return true;
    }
    return false;
}

// FORM ITEM - BEGIN
var form_item_id = 0;

function set_form_item_id(identifier) {
    form_item_id = identifier;
}

function set_panel_name(elm_id, value) {
    if (value == '') {
        value = 'undefined';
    }
    $(elm_id).html(value);
}

function delete_form_item(elm_id) {
    if (confirm("Are you sure to delete this form item?\n(this action can't be undone)")) {
        $(elm_id).remove();
        return true;
    }
    return false;
}

function add_form_option(container_id, form_type, question_type, option_type, collapsed, parent_identifier) {
    var html_option = '';

    switch (question_type) {
        case 'checkboxes':
            break;
    
        default:
            // multiple_choice
            switch (option_type) {
                case 'image':
                    html_option = add_option_text_image(form_type, collapsed, parent_identifier);
                    break;
            
                default:
                    // text
                    html_option = add_option_text(form_type, collapsed, parent_identifier);
                    break;
            }
            break;
    }

    // append
    $(container_id).append(html_option);
}

function add_option_text(form_type, collapsed, parent_identifier) {
    var parent = 'list-form_item-' + parent_identifier;
    var total_options = $('.v_option_answer-' + parent_identifier).length;
    var index = total_options + 1;
    var identifier = Date.now() + index;
    var html = '<div class="panel panel-pagebuilder-options" id="panel-option-' + identifier + '">';
    if (collapsed) {
        html += '<a class="panel-heading panel-pagebuilder-options-heading" role="tab" data-toggle="collapse" data-parent="' + parent + '" href="#collapse-option-' + identifier + '" aria-expanded="false" onclick="scroll_to_element_id(\'panel-option-' + identifier + '\')">';
    } else {
        html += '<a class="panel-heading panel-pagebuilder-options-heading" role="tab" data-toggle="collapse" data-parent="' + parent + '" href="#collapse-option-' + identifier + '" aria-expanded="true" onclick="scroll_to_element_id(\'panel-option-' + identifier + '\')">';
    }
            html += '<h4 class="panel-title"><i class="fa fa-check-circle the-answers-'+parent_identifier+'" id="the-answer-' + identifier + '" style="display:none; color:whitesmoke; margin-right: 10px;"></i><i id="panel-option-label-' + identifier + '">Option Text</i><span class="pull-right"><i class="fa fa-sort"></i><i class="fa fa-trash" style="color:red; margin-left: 20px;" onclick="delete_option(\'#panel-option-' + identifier + '\')"></i></span></h4>';
        html += "</a>";

    if (collapsed) {
        html += '<div id="collapse-option-' + identifier + '" class="panel-collapse collapse" role="tabpanel">';
    } else {
        html += '<div id="collapse-option-' + identifier + '" class="panel-collapse collapse in" role="tabpanel">';
    }

            html += '<div class="panel-body">';

                // v_option_text
                html += '<div class="form-group">';
                    html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">Option Text <span class="required">*</span></label>';
                    html += '<div class="col-md-6 col-sm-6 col-xs-12">';
                        html += '<textarea name="v_option_text[' + parent_identifier + '][' + identifier + ']" required="required" class="form-control col-md-7 col-xs-12" onblur="set_panel_name(\'#panel-option-label-' + identifier + '\', this.value)"></textarea>';
                    html += '</div>';
                html += '</div>';

                if (form_type == 'quiz') {
                    html += '<div class="form-group">';
                        html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">&nbsp;</label>';
                        html += '<div class="col-md-6 col-sm-6 col-xs-12">';
                            html += '<label><input type="radio" name="v_option_answer[' + parent_identifier + ']" value="' + identifier + '" class="v_option_answer-' + parent_identifier + '" onclick="set_answer(\'' + identifier + '\', \'' + parent_identifier + '\')"> Mark this as answer</label>';
                        html += '</div>';
                    html += '</div>';
                }

            html += '</div><!-- /.panel-body -->';
        html += '</div><!-- /.panel-collapse -->';
    html += '</div><!-- /.panel -->';

    return html;
}

function add_option_text_image(form_type, collapsed, parent_identifier) {
    var parent = 'list-form_item-' + parent_identifier;
    var total_options = $('.v_option_answer-' + parent_identifier).length;
    var index = total_options + 1;
    var identifier = Date.now() + index;
    var html = '<div class="panel panel-pagebuilder-options" id="panel-option-' + identifier + '">';
    if (collapsed) {
        html += '<a class="panel-heading panel-pagebuilder-options-heading" role="tab" data-toggle="collapse" data-parent="' + parent + '" href="#collapse-option-' + identifier + '" aria-expanded="false" onclick="scroll_to_element_id(\'panel-option-' + identifier + '\')">';
    } else {
        html += '<a class="panel-heading panel-pagebuilder-options-heading" role="tab" data-toggle="collapse" data-parent="' + parent + '" href="#collapse-option-' + identifier + '" aria-expanded="true" onclick="scroll_to_element_id(\'panel-option-' + identifier + '\')">';
    }
            html += '<h4 class="panel-title"><i class="fa fa-check-circle the-answers-'+parent_identifier+'" id="the-answer-' + identifier + '" style="display:none; color:whitesmoke; margin-right: 10px;"></i><i id="panel-option-label-' + identifier + '">Option Text</i><span class="pull-right"><i class="fa fa-sort"></i><i class="fa fa-trash" style="color:red; margin-left: 20px;" onclick="delete_option(\'#panel-option-' + identifier + '\')"></i></span></h4>';
        html += "</a>";

    if (collapsed) {
        html += '<div id="collapse-option-' + identifier + '" class="panel-collapse collapse" role="tabpanel">';
    } else {
        html += '<div id="collapse-option-' + identifier + '" class="panel-collapse collapse in" role="tabpanel">';
    }

            html += '<div class="panel-body">';

                // v_option_text
                html += '<div class="form-group">';
                    html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">Option Text <span class="required">*</span></label>';
                    html += '<div class="col-md-6 col-sm-6 col-xs-12">';
                        html += '<textarea name="v_option_text[' + parent_identifier + '][' + identifier + ']" required="required" class="form-control col-md-7 col-xs-12" onblur="set_panel_name(\'#panel-option-label-' + identifier + '\', this.value)"></textarea>';
                    html += '</div>';
                html += '</div>';

                // v_option_media
                html += '<div class="form-group">';
                html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">Image <span class="required">*</span></label>';
                html += '<div class="col-md-6 col-sm-6 col-xs-12">';
                    html += '<img src="' + pagebuilder_no_img + '" id="v_img_preview_' + identifier + '" style="max-width: 200px;max-height: 200px;display: block;margin-left: auto;margin-right: auto;">';
                    html += '<input type="file" name="v_option_media[' + parent_identifier + '][' + identifier + ']" required="required" class="form-control col-md-7 col-xs-12" accept="image/*" onchange="display_img_input(this, \'before\', \'v_img_preview_' + identifier + '\', pagebuilder_no_img, pagebuilder_no_img);" style="margin-top:5px">';
                    html += '<input type="hidden" name="v_option_media_exist[' + parent_identifier + '][' + identifier + ']" value="' + pagebuilder_no_img + '">';
                html += '</div>';
            html += '</div>';

                if (form_type == 'quiz') {
                    html += '<div class="form-group">';
                        html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">&nbsp;</label>';
                        html += '<div class="col-md-6 col-sm-6 col-xs-12">';
                            html += '<label><input type="radio" name="v_option_answer[' + parent_identifier + ']" value="' + identifier + '" class="v_option_answer-' + parent_identifier + '" onclick="set_answer(\'' + identifier + '\', \'' + parent_identifier + '\')"> Mark this as answer</label>';
                        html += '</div>';
                    html += '</div>';
                }

            html += '</div><!-- /.panel-body -->';
        html += '</div><!-- /.panel-collapse -->';
    html += '</div><!-- /.panel -->';

    return html;
}

function set_answer(identifier, parent_identifier) {
    $('.the-answers-'+parent_identifier).hide();
    $('#the-answer-'+identifier).show();
}

// form_type ('questionnaire','quiz')
// question_type ('multiple_choice', 'checkboxes', 'drop-down', 'linear_scale')
// option_type ('text', 'image')
// data = question_text, response_wording, question_media, question_src, option_other, point_per_item, option_answer_index, is_required, checkpoint_status
function add_question(
    form_type = 'questionnaire',
    question_type = 'multiple_choice',
    option_type = 'text',
    data = '',
    options_text = '',
    options_media = '',
    enable_other_option = false,
    collapsed = false,
    identifier = 0) {

    // SET "content_container" TO THE DEFAULT
    set_content_container(content_container_default);

    if (identifier == 0) {
        identifier = Date.now();
    }

    var question_text_label = 'Question';
    var v_question_text = '';
    var v_response_wording = '';
    var v_question_media = '';
    var v_question_media_image_selected = '';
    var v_question_src_image = '';
    var v_question_media_video_selected = '';
    var v_question_src_video = '';
    var v_point_per_item = 0;
    var option_answer_index = 0;
    var is_required_yes = '';
    var is_required_no = '';
    var opt_other_yes = '';
    var opt_other_no = '';
    if (data != "") {
        data = JSON.parse(data);

        if (pagebuilder_question_text_using_richtext) {
            v_question_text = data.question_text;
            question_text_label = jQuery(unescapeHTML(v_question_text)).text();
        } else {
            v_question_text = data.question_text.replace(/&lt;br[^>]*&gt;/gi, "\n");
            question_text_label = v_question_text;
        }

        v_response_wording = data.response_wording;
        
        v_question_media = data.question_media;
        v_question_src = data.question_src;
        if (v_question_media == 'image') {
            v_question_media_image_selected = 'selected';
            v_question_src_image = v_question_src;
        } else if (v_question_media == 'video') {
            v_question_media_video_selected = 'selected';
            v_question_src_video = v_question_src;
        }

        v_point_per_item = data.point_per_item;

        option_answer_index = data.option_answer_index;

        if (data.is_required == 1) {
            is_required_yes = 'selected';
        } else {
            is_required_no = 'selected';
        }
    }

    var question_type_label = '';
    var html_options = '';
    switch (question_type) {
        case 'checkboxes':
            question_type_label = 'Checkboxes';
            switch (option_type) {
                case 'image':
                    question_type_label += ' (Text & Image)'; 
                    html_options = generate_html_options_text_image(form_type, options_text, option_answer_index, collapsed, identifier, options_media);
                    break;
            
                default:
                    // text
                    question_type_label += ' (Text)'; 
                    html_options = generate_html_options_text(form_type, options_text, option_answer_index, collapsed, identifier);
                    break;
            }
            break;

        case 'drop-down':
            question_type_label = 'Drop-down';
            html_options = generate_html_options_text(form_type, options_text, option_answer_index, collapsed, identifier);
            break;

        case 'linear_scale':
            question_type_label = 'Linear Scale';
            html_options = generate_html_options_scale(options_text, options_media, identifier);
            break;

        default:
            // multiple_choice
            question_type_label = 'Multiple Choice';
            switch (option_type) {
                case 'image':
                    question_type_label += ' (Text & Image)'; 
                    html_options = generate_html_options_text_image(form_type, options_text, option_answer_index, collapsed, identifier, options_media);
                    break;
            
                default:
                    // text
                    question_type_label += ' (Text)'; 
                    html_options = generate_html_options_text(form_type, options_text, option_answer_index, collapsed, identifier);
                    break;
            }
            break;
    }

    var html = '<div class="panel panel-pagebuilder-form_item" id="pagebuilder-form_item-' + identifier + '">';
        html += '<input type="hidden" name="v_question_type[' + identifier + ']" value="'+question_type+'">';
        html += '<input type="hidden" name="v_option_type[' + identifier + ']" value="'+option_type+'">';

    if (collapsed) {
        html += '<a class="panel-heading panel-pagebuilder-form_item-heading" role="tab" data-toggle="collapse" data-parent="' + content_container + '" href="#collapse_form_item_' + identifier + '" aria-expanded="false" onclick="scroll_to_element_id(\'pagebuilder-form_item-' + identifier + '\')">';
    } else {
        html += '<a class="panel-heading panel-pagebuilder-form_item-heading" role="tab" data-toggle="collapse" data-parent="' + content_container + '" href="#collapse_form_item_' + identifier + '" aria-expanded="true" onclick="scroll_to_element_id(\'pagebuilder-form_item-' + identifier + '\')">';
    }
            html += '<h4 class="panel-title">' + question_type_label + ' &nbsp;<i id=v_question_text_' + identifier + '>' + question_text_label + '</i><span class="pull-right"><i class="fa fa-sort"></i><i class="fa fa-trash" style="color:red; margin-left: 20px;" onclick="delete_form_item(\'#pagebuilder-form_item-' + identifier + '\')"></i></span></h4>';
        html += '</a>';

    if (collapsed) {
        html += '<div id="collapse_form_item_' + identifier + '" class="panel-collapse collapse" role="tabpanel">';
    } else {
        html += '<div id="collapse_form_item_' + identifier + '" class="panel-collapse collapse in" role="tabpanel">';
    }

            html += '<div class="panel-body">';

                // v_question_text
                html += '<div class="form-group">';
                    html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">Question <span class="required">*</span></label>';
                    html += '<div class="col-md-6 col-sm-6 col-xs-12">';
                        html += '<textarea name="v_question_text[' + identifier + ']" required="required" class="form-control col-md-7 col-xs-12" placeholder="sample: Which one do you prefer, red or green?" onblur="set_panel_name(\'#v_question_text_' + identifier + '\', this.value)">' + v_question_text + '</textarea>';
                    html += '</div>';
                html += '</div>';

                // v_response_wording
                html += '<div class="form-group">';
                    html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">Response Wording</label>';
                    html += '<div class="col-md-6 col-sm-6 col-xs-12">';
                        html += '<textarea name="v_response_wording[' + identifier + ']" class="form-control col-md-7 col-xs-12" placeholder="sample: I prefer (option)">' + v_response_wording + '</textarea>';
                    html += '</div>';
                html += '</div>';

                if (pagebuilder_question_media) {
                    // v_question_media
                    html += '<div class="form-group">';
                        html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">Question Media</label>';
                        html += '<div class="col-md-6 col-sm-6 col-xs-12">';
                            html += '<select name="v_question_media[' + identifier + ']" class="form-control col-md-7 col-xs-12">';
                                html += '<option value="">None</option>';
                                html += '<option value="image" '+v_question_media_image_selected+'>Image</option>';
                                html += '<option value="video" '+v_question_media_video_selected+'>Video</option>';
                            html += '</select>';
                        html += '</div>';
                    html += '</div>';

                    // v_question_src (image)
                    html += '<div class="form-group question_src-' + identifier + '" id="question_src-image-' + identifier + '" style="display:none;">';
                        html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">Image <span class="required">*</span></label>';
                        html += '<div class="col-md-6 col-sm-6 col-xs-12">';
                            html += '<img src="" id="v_question_src_image_preview-' + identifier + '" style="max-width: 200px;max-height: 200px;display: block;">';
                            html += '<input type="file" name="v_question_src_image[' + identifier + ']" class="form-control col-md-7 col-xs-12" accept="image/*" onchange="display_img_input(this, \'before\', \'v_question_src_image_preview-' + identifier + '\', pagebuilder_no_img, \''+v_question_src_image+'\');" style="margin-top:5px">';
                            html += '<input type="hidden" name="v_question_src_image_exist[' + identifier + ']" value="">';
                        html += '</div>';
                    html += '</div>';
                    
                    // v_question_src (video)
                    html += '<div class="form-group question_src-' + identifier + '" id="question_src-video-' + identifier + '" style="display:none;">';
                        html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">Video (YouTube URL) <span class="required">*</span></label>';
                        html += '<div class="col-md-6 col-sm-6 col-xs-12">';
                            html += '<input type="text" autocomplete="off" name="v_question_src_video[' + identifier + ']" value="' + v_question_src_video + '" placeholder="https://www.youtube.com/watch?v=XXXX" class="form-control col-md-7 col-xs-12">';
                        html += '</div>';
                    html += '</div>';
                }

                if (form_type == 'quiz') {
                    // v_point_per_item
                    html += '<div class="form-group">';
                        html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">Point Per Item <span class="required">*</span></label>';
                        html += '<div class="col-md-6 col-sm-6 col-xs-12">';
                            html += '<input type="number" name="v_point_per_item[' + identifier + ']" min="0" class="form-control col-md-7 col-xs-12" placeholder="input this if want to set point per item" value="' + v_point_per_item + '">';
                        html += '</div>';
                    html += '</div>';
                }

                if (pagebuilder_enable_required_status) {
                    // v_is_required
                    html += '<div class="form-group">';
                        html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">Required to Answer? <span class="required">*</span></label>';
                        html += '<div class="col-md-6 col-sm-6 col-xs-12">';
                            html += '<select name="v_is_required[' + identifier + ']" class="form-control col-md-7 col-xs-12">';
                                html += '<option value="1" ' + is_required_yes + '>YES</option>';
                                html += '<option value="0" ' + is_required_no + '>NO</option>';
                            html += '</select>';
                        html += '</div>';
                    html += '</div>';
                }

                html += "<hr>";
                html += '<div class="sortable-form_item" id="list-form_item-' + identifier + '">'+html_options+'</div>';
                html += "<hr>";

                if (question_type != 'linear_scale') {
                    html += "<center>";
                        html += '<span class="btn btn-primary" onclick="add_form_option(\'#list-form_item-' + identifier + '\', \'' + form_type + '\', \'' + question_type + '\', \'' + option_type + '\', ' + collapsed + ', \'' + identifier + '\')">';
                            html += '<i class="fa fa-plus-circle"></i>&nbsp; Add Option</span>';
                        html += '</span>';
                    html += "</center>";
                    html += "<hr>";
                }

                // only show v_option_other if form_type is not "quiz" coz quiz need a correct answer
                if (enable_other_option && form_type != 'quiz') {
                    html += '<div class="form-group">';
                        html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">Add "Other" Option</label>';
                        html += '<div class="col-md-6 col-sm-6 col-xs-12">';
                            html += '<select name="v_option_other[' + identifier + ']" class="form-control col-md-7 col-xs-12">';
                                html += '<option value="0" ' + opt_other_no + '>NO</option>';
                                html += '<option value="1" ' + opt_other_yes + '>YES</option>';
                            html += '</select>';
                        html += '</div>';
                    html += '</div>';
                }

            html += "</div><!-- /.panel-body -->";
        html += "</div><!-- /.panel-collapse -->";
    html += "</div><!-- /.panel -->";

    $(content_container).append(html);

    initialize_sortable_content_in_form('#list-form_item-' + identifier);
    $('#btn_add_item_bottom').show();

    if (data == '') {
        alert('Item added');
    }
}
// FORM ITEM - END

function generate_html_options_text(form_type, options_text, option_answer_index, collapsed, identifier) {
    if (identifier == 0) {
        identifier = Date.now();
    }

    var parent_identifier = identifier;
    var parent = 'list-form_item-' + parent_identifier;

    var html = '';

    if (options_text != '') {
        // generate options with existing values
        $.each(options_text, function(index, value) {
            var answer_icon_style = 'display:none;';
            if (form_type == 'quiz') {
                if (index == option_answer_index) {
                    answer_icon_style = '';
                }
            }
            identifier = identifier + index;
            html += '<div class="panel panel-pagebuilder-options" id="panel-option-' + identifier + '">';
            if (collapsed) {
                html += '<a class="panel-heading panel-pagebuilder-options-heading" role="tab" data-toggle="collapse" data-parent="' + parent + '" href="#collapse-option-' + identifier + '" aria-expanded="false" onclick="scroll_to_element_id(\'panel-option-' + identifier + '\')">';
            } else {
                html += '<a class="panel-heading panel-pagebuilder-options-heading" role="tab" data-toggle="collapse" data-parent="' + parent + '" href="#collapse-option-' + identifier + '" aria-expanded="true" onclick="scroll_to_element_id(\'panel-option-' + identifier + '\')">';
            }
                    html += '<h4 class="panel-title"><i class="fa fa-check-circle the-answers-'+parent_identifier+'" id="the-answer-' + identifier + '" style="' + answer_icon_style + ' color:whitesmoke; margin-right: 10px;"></i><i id="panel-option-label-' + identifier + '">'+value+'</i><span class="pull-right"><i class="fa fa-sort"></i><i class="fa fa-trash" style="color:red; margin-left: 20px;" onclick="delete_option(\'#panel-option-' + identifier + '\')"></i></span></h4>';
                html += "</a>";
        
            if (collapsed) {
                html += '<div id="collapse-option-' + identifier + '" class="panel-collapse collapse" role="tabpanel">';
            } else {
                html += '<div id="collapse-option-' + identifier + '" class="panel-collapse collapse in" role="tabpanel">';
            }
        
                    html += '<div class="panel-body">';
        
                        // v_option_text
                        html += '<div class="form-group">';
                            html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">Option Text <span class="required">*</span></label>';
                            html += '<div class="col-md-6 col-sm-6 col-xs-12">';
                                html += '<textarea name="v_option_text[' + parent_identifier + '][' + identifier + ']" required="required" class="form-control col-md-7 col-xs-12" onblur="set_panel_name(\'#panel-option-label-' + identifier + '\', this.value)">'+value+'</textarea>';
                            html += '</div>';
                        html += '</div>';
        
                        if (form_type == 'quiz') {
                            var answer_checked = '';
                            if (index == option_answer_index) {
                                answer_checked = 'checked';
                            }
                            html += '<div class="form-group">';
                                html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">&nbsp;</label>';
                                html += '<div class="col-md-6 col-sm-6 col-xs-12">';
                                    html += '<label><input type="radio" name="v_option_answer[' + parent_identifier + ']" value="' + identifier + '" ' + answer_checked + ' class="v_option_answer-' + parent_identifier + '" onclick="set_answer(\'' + identifier + '\', \'' + parent_identifier + '\')"> Mark this as answer</label>';
                                html += '</div>';
                            html += '</div>';
                        }
        
                    html += '</div><!-- /.panel-body -->';
                html += '</div><!-- /.panel-collapse -->';
            html += '</div><!-- /.panel -->';
        });
    } else {
        // generate blank options
        for (let index = 0; index < pagebuilder_total_options; index++) {
            var answer_icon_style = 'display:none;';
            if (form_type == 'quiz') {
                if (index == option_answer_index) {
                    answer_icon_style = '';
                }
            }
            identifier = identifier + index;
            html += '<div class="panel panel-pagebuilder-options" id="panel-option-' + identifier + '">';
            if (collapsed) {
                html += '<a class="panel-heading panel-pagebuilder-options-heading" role="tab" data-toggle="collapse" data-parent="' + parent + '" href="#collapse-option-' + identifier + '" aria-expanded="false" onclick="scroll_to_element_id(\'panel-option-' + identifier + '\')">';
            } else {
                html += '<a class="panel-heading panel-pagebuilder-options-heading" role="tab" data-toggle="collapse" data-parent="' + parent + '" href="#collapse-option-' + identifier + '" aria-expanded="true" onclick="scroll_to_element_id(\'panel-option-' + identifier + '\')">';
            }
                    html += '<h4 class="panel-title"><i class="fa fa-check-circle the-answers-'+parent_identifier+'" id="the-answer-' + identifier + '" style="'+answer_icon_style+' color:whitesmoke; margin-right: 10px;"></i><i id="panel-option-label-' + identifier + '">Option Text</i><span class="pull-right"><i class="fa fa-sort"></i><i class="fa fa-trash" style="color:red; margin-left: 20px;" onclick="delete_option(\'#panel-option-' + identifier + '\')"></i></span></h4>';
                html += "</a>";
        
            if (collapsed) {
                html += '<div id="collapse-option-' + identifier + '" class="panel-collapse collapse" role="tabpanel">';
            } else {
                html += '<div id="collapse-option-' + identifier + '" class="panel-collapse collapse in" role="tabpanel">';
            }

                    html += '<div class="panel-body">';

                        // v_option_text
                        html += '<div class="form-group">';
                            html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">Option Text <span class="required">*</span></label>';
                            html += '<div class="col-md-6 col-sm-6 col-xs-12">';
                                html += '<textarea name="v_option_text[' + parent_identifier + '][' + identifier + ']" required="required" class="form-control col-md-7 col-xs-12" onblur="set_panel_name(\'#panel-option-label-' + identifier + '\', this.value)"></textarea>';
                            html += '</div>';
                        html += '</div>';

                        if (form_type == 'quiz') {
                            var answer_checked = '';
                            if (index == option_answer_index) {
                                answer_checked = 'checked';
                            }
                            html += '<div class="form-group">';
                                html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">&nbsp;</label>';
                                html += '<div class="col-md-6 col-sm-6 col-xs-12">';
                                    html += '<label><input type="radio" name="v_option_answer[' + parent_identifier + ']" value="' + identifier + '" ' + answer_checked + ' class="v_option_answer-' + parent_identifier + '" onclick="set_answer(\'' + identifier + '\', \'' + parent_identifier + '\')"> Mark this as answer</label>';
                                html += '</div>';
                            html += '</div>';
                        }

                    html += '</div><!-- /.panel-body -->';
                html += '</div><!-- /.panel-collapse -->';
            html += '</div><!-- /.panel -->';
        }
    }

    return html;
}

function generate_html_options_text_image(form_type, options_text, option_answer_index, collapsed, identifier, options_media) {
    if (identifier == 0) {
        identifier = Date.now();
    }

    var parent_identifier = identifier;
    var parent = 'list-form_item-' + parent_identifier;

    var html = '';

    if (options_text != '') {
        // generate options with existing values
        $.each(options_text, function(index, value) {
            var answer_icon_style = 'display:none;';
            if (form_type == 'quiz') {
                if (index == option_answer_index) {
                    answer_icon_style = '';
                }
            }
            identifier = identifier + index;
            html += '<div class="panel panel-pagebuilder-options" id="panel-option-' + identifier + '">';
            if (collapsed) {
                html += '<a class="panel-heading panel-pagebuilder-options-heading" role="tab" data-toggle="collapse" data-parent="' + parent + '" href="#collapse-option-' + identifier + '" aria-expanded="false" onclick="scroll_to_element_id(\'panel-option-' + identifier + '\')">';
            } else {
                html += '<a class="panel-heading panel-pagebuilder-options-heading" role="tab" data-toggle="collapse" data-parent="' + parent + '" href="#collapse-option-' + identifier + '" aria-expanded="true" onclick="scroll_to_element_id(\'panel-option-' + identifier + '\')">';
            }
                    html += '<h4 class="panel-title"><i class="fa fa-check-circle the-answers-'+parent_identifier+'" id="the-answer-' + identifier + '" style="' + answer_icon_style + ' color:whitesmoke; margin-right: 10px;"></i><i id="panel-option-label-' + identifier + '">'+value+'</i><span class="pull-right"><i class="fa fa-sort"></i><i class="fa fa-trash" style="color:red; margin-left: 20px;" onclick="delete_option(\'#panel-option-' + identifier + '\')"></i></span></h4>';
                html += "</a>";
        
            if (collapsed) {
                html += '<div id="collapse-option-' + identifier + '" class="panel-collapse collapse" role="tabpanel">';
            } else {
                html += '<div id="collapse-option-' + identifier + '" class="panel-collapse collapse in" role="tabpanel">';
            }
        
                    html += '<div class="panel-body">';
        
                        // v_option_text
                        html += '<div class="form-group">';
                            html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">Option Text <span class="required">*</span></label>';
                            html += '<div class="col-md-6 col-sm-6 col-xs-12">';
                                html += '<textarea name="v_option_text[' + parent_identifier + '][' + identifier + ']" required="required" class="form-control col-md-7 col-xs-12" onblur="set_panel_name(\'#panel-option-label-' + identifier + '\', this.value)">'+value+'</textarea>';
                            html += '</div>';
                        html += '</div>';

                        // v_option_media
                        html += '<div class="form-group">';
                            html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">Image <span class="required">*</span></label>';
                            html += '<div class="col-md-6 col-sm-6 col-xs-12">';
                                html += '<img src="' + pagebuilder_url + '/' + options_media[index] + '" id="v_img_preview_' + identifier + '" style="max-width: 200px;max-height: 200px;display: block;margin-left: auto;margin-right: auto;">';
                                html += '<input type="file" name="v_option_media[' + parent_identifier + '][' + identifier + ']" class="form-control col-md-7 col-xs-12" accept="image/*" onchange="display_img_input(this, \'before\', \'v_img_preview_' + identifier + '\', pagebuilder_no_img, \''+ pagebuilder_url + '/' + options_media[index] + '\');" style="margin-top:5px">';
                                html += '<input type="hidden" name="v_option_media_exist[' + parent_identifier + '][' + identifier + ']" value="' + options_media[index] + '">';
                            html += '</div>';
                        html += '</div>';
        
                        if (form_type == 'quiz') {
                            var answer_checked = '';
                            if (index == option_answer_index) {
                                answer_checked = 'checked';
                            }
                            html += '<div class="form-group">';
                                html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">&nbsp;</label>';
                                html += '<div class="col-md-6 col-sm-6 col-xs-12">';
                                    html += '<label><input type="radio" name="v_option_answer[' + parent_identifier + ']" value="' + identifier + '" ' + answer_checked + ' class="v_option_answer-' + parent_identifier + '" onclick="set_answer(\'' + identifier + '\', \'' + parent_identifier + '\')"> Mark this as answer</label>';
                                html += '</div>';
                            html += '</div>';
                        }
        
                    html += '</div><!-- /.panel-body -->';
                html += '</div><!-- /.panel-collapse -->';
            html += '</div><!-- /.panel -->';
        });
    } else {
        // generate blank options
        for (let index = 0; index < pagebuilder_total_options; index++) {
            var answer_icon_style = 'display:none;';
            if (form_type == 'quiz') {
                if (index == option_answer_index) {
                    answer_icon_style = '';
                }
            }
            identifier = identifier + index;
            html += '<div class="panel panel-pagebuilder-options" id="panel-option-' + identifier + '">';
            if (collapsed) {
                html += '<a class="panel-heading panel-pagebuilder-options-heading" role="tab" data-toggle="collapse" data-parent="' + parent + '" href="#collapse-option-' + identifier + '" aria-expanded="false" onclick="scroll_to_element_id(\'panel-option-' + identifier + '\')">';
            } else {
                html += '<a class="panel-heading panel-pagebuilder-options-heading" role="tab" data-toggle="collapse" data-parent="' + parent + '" href="#collapse-option-' + identifier + '" aria-expanded="true" onclick="scroll_to_element_id(\'panel-option-' + identifier + '\')">';
            }
                    html += '<h4 class="panel-title"><i class="fa fa-check-circle the-answers-'+parent_identifier+'" id="the-answer-' + identifier + '" style="'+answer_icon_style+' color:whitesmoke; margin-right: 10px;"></i><i id="panel-option-label-' + identifier + '">Option Text</i><span class="pull-right"><i class="fa fa-sort"></i><i class="fa fa-trash" style="color:red; margin-left: 20px;" onclick="delete_option(\'#panel-option-' + identifier + '\')"></i></span></h4>';
                html += "</a>";
        
            if (collapsed) {
                html += '<div id="collapse-option-' + identifier + '" class="panel-collapse collapse" role="tabpanel">';
            } else {
                html += '<div id="collapse-option-' + identifier + '" class="panel-collapse collapse in" role="tabpanel">';
            }

                    html += '<div class="panel-body">';

                        // v_option_text
                        html += '<div class="form-group">';
                            html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">Option Text <span class="required">*</span></label>';
                            html += '<div class="col-md-6 col-sm-6 col-xs-12">';
                                html += '<textarea name="v_option_text[' + parent_identifier + '][' + identifier + ']" required="required" class="form-control col-md-7 col-xs-12" onblur="set_panel_name(\'#panel-option-label-' + identifier + '\', this.value)"></textarea>';
                            html += '</div>';
                        html += '</div>';

                        // v_option_media
                        html += '<div class="form-group">';
                            html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">Image <span class="required">*</span></label>';
                            html += '<div class="col-md-6 col-sm-6 col-xs-12">';
                                html += '<img src="' + pagebuilder_no_img + '" id="v_img_preview_' + identifier + '" style="max-width: 200px;max-height: 200px;display: block;margin-left: auto;margin-right: auto;">';
                                html += '<input type="file" name="v_option_media[' + parent_identifier + '][' + identifier + ']" required="required" class="form-control col-md-7 col-xs-12" accept="image/*" onchange="display_img_input(this, \'before\', \'v_img_preview_' + identifier + '\', pagebuilder_no_img, pagebuilder_no_img);" style="margin-top:5px">';
                                html += '<input type="hidden" name="v_option_media_exist[' + parent_identifier + '][' + identifier + ']" value="' + pagebuilder_no_img + '">';
                            html += '</div>';
                        html += '</div>';

                        if (form_type == 'quiz') {
                            var answer_checked = '';
                            if (index == option_answer_index) {
                                answer_checked = 'checked';
                            }
                            html += '<div class="form-group">';
                                html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">&nbsp;</label>';
                                html += '<div class="col-md-6 col-sm-6 col-xs-12">';
                                    html += '<label><input type="radio" name="v_option_answer[' + parent_identifier + ']" value="' + identifier + '" ' + answer_checked + ' class="v_option_answer-' + parent_identifier + '" onclick="set_answer(\'' + identifier + '\', \'' + parent_identifier + '\')"> Mark this as answer</label>';
                                html += '</div>';
                            html += '</div>';
                        }

                    html += '</div><!-- /.panel-body -->';
                html += '</div><!-- /.panel-collapse -->';
            html += '</div><!-- /.panel -->';
        }
    }

    return html;
}

function generate_html_options_scale(options_text, options_media, identifier) {
    var html = '';

    if (options_text != '') {
        // generate options with existing values

        var start_from_0 = 'selected';
        var start_from_1 = '';
        if (options_text[0] == 1) {
            start_from_0 = '';
            start_from_1 = 'selected';
        }

        var start_label = '';
        var until_label = '';
        if (options_media != '') {
            if (typeof options_media[0] !== "undefined") {
                start_label = options_media[0];
            }
            if (typeof options_media[1] !== "undefined") {
                until_label = options_media[1];
            }
        }

        // v_option_start
        html += '<div class="form-group">';
            html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">Start From <span class="required">*</span></label>';
            html += '<div class="col-md-6 col-sm-6 col-xs-12">';
                html += '<select name="v_option_start[' + identifier + ']" class="form-control col-md-7 col-xs-12">';
                    html += '<option value="0" '+start_from_0+'>0</option>';
                    html += '<option value="1" '+start_from_1+'>1</option>';
                html += '</select>';
            html += '</div>';
        html += '</div>';

        // v_option_start_label
        html += '<div class="form-group">';
            html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">Start From Label</label>';
            html += '<div class="col-md-6 col-sm-6 col-xs-12">';
                html += '<textarea name="v_option_start_label[' + identifier + ']" class="form-control col-md-7 col-xs-12" placeholder="*optional">'+start_label+'</textarea>';
            html += '</div>';
        html += '</div>';

        html += '<hr>';

        // v_option_until
        html += '<div class="form-group">';
            html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">Until To <span class="required">*</span></label>';
            html += '<div class="col-md-6 col-sm-6 col-xs-12">';
                html += '<select name="v_option_until[' + identifier + ']" class="form-control col-md-7 col-xs-12">';
                    var until_to_stat = '';    
                    for (i = 2; i <= 10; i++) {
                        until_to_stat = '';
                        if (i == options_text[options_text.length - 1]) {
                            until_to_stat = 'selected';
                        }
                        html += '<option value="' + i + '" ' + until_to_stat + '>' + i + '</option>';
                    }
                html += '</select>';
            html += '</div>';
        html += '</div>';

        // v_option_until_label
        html += '<div class="form-group">';
            html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">Until To Label</label>';
            html += '<div class="col-md-6 col-sm-6 col-xs-12">';
                html += '<textarea name="v_option_until_label[' + identifier + ']" class="form-control col-md-7 col-xs-12" placeholder="*optional">'+until_label+'</textarea>';
            html += '</div>';
        html += '</div>';
    } else {
        // generate blank options

        // v_option_start
        html += '<div class="form-group">';
            html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">Start From <span class="required">*</span></label>';
            html += '<div class="col-md-6 col-sm-6 col-xs-12">';
                html += '<select name="v_option_start[' + identifier + ']" class="form-control col-md-7 col-xs-12">';
                    html += '<option value="0">0</option>';
                    html += '<option value="1">1</option>';
                html += '</select>';
            html += '</div>';
        html += '</div>';

        // v_option_start_label
        html += '<div class="form-group">';
            html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">Start From Label</label>';
            html += '<div class="col-md-6 col-sm-6 col-xs-12">';
                html += '<textarea name="v_option_start_label[' + identifier + ']" class="form-control col-md-7 col-xs-12" placeholder="*optional"></textarea>';
            html += '</div>';
        html += '</div>';

        html += '<hr>';

        // v_option_until
        html += '<div class="form-group">';
            html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">Until To <span class="required">*</span></label>';
            html += '<div class="col-md-6 col-sm-6 col-xs-12">';
                html += '<select name="v_option_until[' + identifier + ']" class="form-control col-md-7 col-xs-12">';
                    var until_to_stat = '';    
                    for (i = 2; i <= 10; i++) {
                        until_to_stat = '';
                        html += '<option value="' + i + '" ' + until_to_stat + '>' + i + '</option>';
                    }
                html += '</select>';
            html += '</div>';
        html += '</div>';

        // v_option_until_label
        html += '<div class="form-group">';
            html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">Until To Label</label>';
            html += '<div class="col-md-6 col-sm-6 col-xs-12">';
                html += '<textarea name="v_option_until_label[' + identifier + ']" class="form-control col-md-7 col-xs-12" placeholder="*optional"></textarea>';
            html += '</div>';
        html += '</div>';
    }

    return html;
}