/**
 * PageBuilder (Build pages using content elements)
 * Version: 1.1.3
 *
 * Copyright (c) KINIDI Tech and other contributors
 * Released under the MIT license.
 * For more information, see https://kiniditech.com/ or https://github.com/vickzkater
 *
 * PageBuilder depends on several libraries, such as:
 * - jQuery (https://jquery.com/download/)
 * - jQuery UI (https://jqueryui.com/download/)
 * - TinyMCE (https://www.tiny.cloud/get-tiny/downloads/)
 * - Bootstrap (https://getbootstrap.com/docs/4.3/getting-started/download/)
 *
 * Support Content Elements (Standard):
 * - Text (Rich Text Editor / WYSIWYG HTML Editor)
 * - Image
 * - Image & Text (Rich Text Editor / WYSIWYG HTML Editor)
 * - Video
 * - Video & Text (Rich Text Editor / WYSIWYG HTML Editor)
 * - Plain Text
 */

// SET PAGEBUILDER MODE
if (typeof pagebuilder_mode == "undefined") {
  var pagebuilder_mode = 'standard';
}

// SET CONTENT CONTAINER IN PAGEBUILDER
if (typeof content_container_default == "undefined") {
  var content_container_default = "#content-pagebuilder";
}
if (typeof content_container == "undefined") {
  var content_container = "#content-pagebuilder";
}

function set_content_container(identifier) {
  content_container = identifier;
}

// SET DEFAULT IMAGE FOR "NO IMAGE" IN PAGEBUILDER
if (typeof pagebuilder_no_img == "undefined") {
  var pagebuilder_no_img = "https://kiniditech.com/hosting/no-image.png";
}

// SET URL INTERNAL IN PAGEBUILDER
if (typeof pagebuilder_url == "undefined") {
  var pagebuilder_url = window.location.origin;
}

function random_number(max) {
  return Math.floor(Math.random() * max);
}

function show_loading_pagebuilder() {
  $('.modal-content-element-loading').modal();
}

function hide_loading_pagebuilder() {
  $('.modal-content-element-loading').modal('hide');
}

$(document).ready(function () {
  switch (pagebuilder_mode) {
    case 'landing page':
      initialize_sortable_content_in_page(content_container);
      break;

    case 'form':
      initialize_sortable_content_in_form(content_container);
      break;
  
    default:
      // standard
      initialize_sortable_content(content_container);
      initialize_tinymce(".text-editor");
      break;
  }
});

/**
 * for displaying image after browse it before uploaded
 * Example: implement onchange="display_img_input(this, 'before')" in input type file in form
 *
 * @param {element input file form} input
 * @param {string} position_image - (after/before)
 * @param {string} default_image
 * @param {string} existing_image - (after/before)
 */
function display_img_input(input, position_image = "after", image_id = 'none', default_image = 'none', existing_image = 'none') {
  if (default_image == 'none') {
    default_image = pagebuilder_no_img;
  }

  if (input.files && input.files[0]) {
      var reader = new FileReader();

      reader.onload = function (e) {
          if (image_id != 'none') {
              $('#' + image_id).attr("src", e.target.result);
          } else {
              if (position_image == "before") {
                  $(input).prev("img").attr("src", e.target.result);
              } else {
                  $(input).next("img").attr("src", e.target.result);
              }
          }
      };

      reader.readAsDataURL(input.files[0]);
  } else if (existing_image != 'none') {
      if (image_id != 'none') {
          $('#' + image_id).attr("src", existing_image);
      } else {
          if (position_image == "before") {
              $(input).prev("img").attr("src", existing_image);
          } else {
              $(input).next("img").attr("src", existing_image);
          }
      }
  } else if (default_image != 'none') {
      if (image_id != 'none') {
          $('#' + image_id).attr("src", default_image);
      } else {
          if (position_image == "before") {
              $(input).prev("img").attr("src", default_image);
          } else {
              $(input).next("img").attr("src", default_image);
          }
      }
  }
}

function initialize_sortable_content(container) {
  if (typeof $.fn.sortable === "undefined") {
    alert("jQuery UI library is not included");
    return;
  }

  $(container).sortable({
    change: function (event, ui) {
      // console.log('Sortable changed');
    },
    update: function (event, ui) {
      // console.log('Sortable updated');
    },
    sort: function (e) {
      // console.log('Sortable sorted');
    },
    stop: function (event, ui) {
      // console.log('Sortable stopped');
      var section = ui.item[0].id.split("_");
      var text_id = "v_element_content_text_" + section[2];

      tinymce.remove("#" + text_id);
      initialize_tinymce("#" + text_id);
    },
  });
}

function initialize_tinymce(elm) {
  if (typeof tinymce === "undefined") {
    alert("TinyMCE library is not included");
    return;
  }

  tinymce.init({
    selector: elm,
    branding: false,
    height: 500,
    plugins: [
      "link image imagetools table spellchecker charmap fullscreen emoticons help preview searchreplace code lists advlist",
    ],
    toolbar: [
      {
        name: 'view', 
        items: [ 'fullscreen', 'code', 'preview' ]
      },
      {
        name: "history",
        items: ["undo", "redo"],
      },
      {
        name: "styles",
        items: ["styleselect"],
      },
      {
        name: "formatting",
        items: ["bold", "italic", "underline"],
      },
      {
        name: "ordinal",
        items: ["bullist", "numlist"],
      },
      {
        name: "alignment",
        items: ["alignleft", "aligncenter", "alignright", "alignjustify"],
      },
      {
        name: "indentation",
        items: ["outdent", "indent"],
      },
      {
        name: "insert",
        items: ["link", "image", "charmap", "emoticons"],
      },
      {
        name: "help",
        items: ["searchreplace", "help"],
      },
    ],
    toolbar_sticky: false,
    setup: function(editor) {
      editor.on('keyup', function(e) {
          // Saves all contents from TinyMCE to Textarea
          tinyMCE.triggerSave();
      });
    },
    init_instance_callback : "hide_loading_pagebuilder"
  });
}

function add_content_element(
  type,
  collapsed = false,
  identifier = 0,
  data = ""
) {
  if (identifier == 0) {
    var uniqid = Date.now();
  } else {
    var uniqid = identifier;
  }

  var html_content_element = "";

  switch (type) {
    case "text":
      html_content_element = set_html_text(collapsed, uniqid, data);
      break;

    case "image":
      html_content_element = set_html_image(collapsed, uniqid, data);
      break;

    case "image & text":
      html_content_element = set_html_image_text(collapsed, uniqid, data);
      break;

    case "video":
      html_content_element = set_html_video(collapsed, uniqid, data);
      break;

    case "video & text":
      html_content_element = set_html_video_text(collapsed, uniqid, data);
      break;

    case "plain text":
      html_content_element = set_html_plaintext(collapsed, uniqid, data);
      break;

    default:
      alert("NO CONTENT ELEMENT TYPE SELECTED");
      return false;
      break;
  }

  $(content_container).append(html_content_element);

  // initialize TinyMCE
  switch (type) {
    case "text":
      initialize_tinymce("#v_element_content_text_" + uniqid);
      break;

    case "image & text":
      initialize_tinymce("#v_element_content_text_" + uniqid);
      break;

    case "video & text":
      initialize_tinymce("#v_element_content_text_" + uniqid);
      break;
  }
}

function delete_content_element(id) {
  if (
    confirm(
      "Are you sure to delete this content?\n(this action can't be undone)"
    )
  ) {
    $("#pagebuilder_elm_" + id).remove();
    return true;
  }
  return false;
}

function set_section_name(id, value) {
  $("#section" + id).html(value);
}

// SET CONTENT ELEMENT BELOW *********

function set_html_text(collapsed, uniqid, data) {
  var section_name = "";
  var data_text = "";
  if (data != "") {
    section_name = data.section;
    data_text = decodeURI(data.text);
  }

  var html =
    '<div class="panel panel-content-element" id="pagebuilder_elm_' +
    uniqid +
    '">';
  html +=
    '<input type="hidden" name="v_element_type[' + uniqid + ']" value="text">';

  if (collapsed) {
    html +=
      '<a class="panel-heading" role="tab" data-toggle="collapse" data-parent="' +
      content_container +
      '" href="#collapse' +
      uniqid +
      '" aria-expanded="false">';
  } else {
    html +=
      '<a class="panel-heading" role="tab" data-toggle="collapse" data-parent="' +
      content_container +
      '" href="#collapse' +
      uniqid +
      '" aria-expanded="true">';
  }

  html +=
    '<h4 class="panel-title">Text - Section <i id=section' +
    uniqid +
    ">" +
    section_name +
    '</i><span class="pull-right"><i class="fa fa-sort"></i><i class="fa fa-trash" style="color:red; margin-left: 20px;" onclick="delete_content_element(' +
    uniqid +
    ')"></i></span></h4>';
  html += "</a>";

  if (collapsed) {
    html +=
      '<div id="collapse' +
      uniqid +
      '" class="panel-collapse collapse" role="tabpanel">';
  } else {
    html +=
      '<div id="collapse' +
      uniqid +
      '" class="panel-collapse collapse in" role="tabpanel">';
  }

  html += '<div class="panel-body">';
  // SECTION
  html += '<div class="form-group">';
  html +=
    '<label class="control-label col-md-3 col-sm-3 col-xs-12">Section <span class="required">*</span></label>';
  html += '<div class="col-md-6 col-sm-6 col-xs-12">';
  html +=
    '<input type="text" autocomplete="off" value="' +
    section_name +
    '" name="v_element_section[' +
    uniqid +
    ']" required="required" class="form-control col-md-7 col-xs-12" onblur="set_section_name(' +
    uniqid +
    ', this.value)">';
  html += "</div>";
  html += "</div>";
  // TEXTAREA
  html += '<div class="form-group">';
  html +=
    '<label class="control-label col-md-3 col-sm-3 col-xs-12">Text <span class="required">*</span></label>';
  html += '<div class="col-md-9 col-sm-9 col-xs-12">';
  html +=
    '<textarea name="v_element_content_text[' +
    uniqid +
    ']" id="v_element_content_text_' +
    uniqid +
    '" class="form-control col-md-7 col-xs-12 text-editor">' +
    data_text +
    "</textarea>";
  html += "</div>";
  html += "</div>";
  html += "</div>";
  html += "</div>";
  html += "</div>";

  return html;
}

function set_html_image(collapsed, uniqid, data) {
  var section_name = "";
  var data_image = pagebuilder_no_img;
  var is_required = 'required="required"';
  if (data != "") {
    section_name = data.section;
    data_image = decodeURI(data.image);
    is_required = "";
  }

  var html =
    '<div class="panel panel-content-element" id="pagebuilder_elm_' +
    uniqid +
    '">';
  html +=
    '<input type="hidden" name="v_element_type[' + uniqid + ']" value="image">';

  if (collapsed) {
    html +=
      '<a class="panel-heading" role="tab" data-toggle="collapse" data-parent="' +
      content_container +
      '" href="#collapse' +
      uniqid +
      '" aria-expanded="false">';
  } else {
    html +=
      '<a class="panel-heading" role="tab" data-toggle="collapse" data-parent="' +
      content_container +
      '" href="#collapse' +
      uniqid +
      '" aria-expanded="true">';
  }

  html +=
    '<h4 class="panel-title">Image - Section <i id=section' +
    uniqid +
    ">" +
    section_name +
    '</i><span class="pull-right"><i class="fa fa-sort"></i><i class="fa fa-trash" style="color:red; margin-left: 20px;" onclick="delete_content_element(' +
    uniqid +
    ')"></i></span></h4>';
  html += "</a>";

  if (collapsed) {
    html +=
      '<div id="collapse' +
      uniqid +
      '" class="panel-collapse collapse" role="tabpanel">';
  } else {
    html +=
      '<div id="collapse' +
      uniqid +
      '" class="panel-collapse collapse in" role="tabpanel">';
  }

  html += '<div class="panel-body">';
  html += '<div class="form-group">';
  html +=
    '<label class="control-label col-md-3 col-sm-3 col-xs-12">Section <span class="required">*</span></label>';
  html +=
    '<div class="col-md-6 col-sm-6 col-xs-12"><input type="text" autocomplete="off" value="' +
    section_name +
    '" name="v_element_section[' +
    uniqid +
    ']" required="required" class="form-control col-md-7 col-xs-12" onblur="set_section_name(' +
    uniqid +
    ', this.value)"></div>';
  html += "</div>";
  html += '<div class="form-group">';
  html +=
    '<label class="control-label col-md-3 col-sm-3 col-xs-12">Image <span class="required">*</span></label>';
  html += '<div class="col-md-6 col-sm-6 col-xs-12">';
  html +=
    '<img src="' +
    data_image +
    '" style="max-width: 200px;max-height: 200px;display: block;margin-left: auto;margin-right: auto;">';
  html +=
    '<input type="file" name="v_element_content_image[' +
    uniqid +
    ']" ' +
    is_required +
    ' class="form-control col-md-7 col-xs-12" accept=".jpg, .jpeg, .png" onchange="display_img_input(this, \'before\');" style="margin-top:5px">';
  html += "</div>";
  html += "</div>";
  html += "</div>";
  html += "</div>";
  html += "</div>";

  return html;
}

function set_html_image_text(collapsed, uniqid, data) {
  var section_name = "";
  var data_image = pagebuilder_no_img;
  var is_required = 'required="required"';
  var data_text = "";
  var text_position_left = "";
  if (data != "") {
    section_name = data.section;
    data_image = decodeURI(data.image);
    is_required = "";
    data_text = decodeURI(data.text);
    if (data.text_position == "left") {
      text_position_left = "selected";
    }
  }

  var html =
    '<div class="panel panel-content-element" id="pagebuilder_elm_' +
    uniqid +
    '">';
  html +=
    '<input type="hidden" name="v_element_type[' +
    uniqid +
    ']" value="image & text">';

  if (collapsed) {
    html +=
      '<a class="panel-heading" role="tab" data-toggle="collapse" data-parent="' +
      content_container +
      '" href="#collapse' +
      uniqid +
      '" aria-expanded="false">';
  } else {
    html +=
      '<a class="panel-heading" role="tab" data-toggle="collapse" data-parent="' +
      content_container +
      '" href="#collapse' +
      uniqid +
      '" aria-expanded="true">';
  }

  html +=
    '<h4 class="panel-title">Image & Text - Section <i id=section' +
    uniqid +
    ">" +
    section_name +
    '</i><span class="pull-right"><i class="fa fa-sort"></i><i class="fa fa-trash" style="color:red; margin-left: 20px;" onclick="delete_content_element(' +
    uniqid +
    ')"></i></span></h4>';
  html += "</a>";

  if (collapsed) {
    html +=
      '<div id="collapse' +
      uniqid +
      '" class="panel-collapse collapse" role="tabpanel">';
  } else {
    html +=
      '<div id="collapse' +
      uniqid +
      '" class="panel-collapse collapse in" role="tabpanel">';
  }

  html += '<div class="panel-body">';
  html += '<div class="form-group">';
  html +=
    '<label class="control-label col-md-3 col-sm-3 col-xs-12">Section <span class="required">*</span></label>';
  html +=
    '<div class="col-md-6 col-sm-6 col-xs-12"><input type="text" autocomplete="off" value="' +
    section_name +
    '" name="v_element_section[' +
    uniqid +
    ']" required="required" class="form-control col-md-7 col-xs-12" onblur="set_section_name(' +
    uniqid +
    ', this.value)"></div>';
  html += "</div>";
  html += '<div class="form-group">';
  html +=
    '<label class="control-label col-md-3 col-sm-3 col-xs-12">Image <span class="required">*</span></label>';
  html += '<div class="col-md-6 col-sm-6 col-xs-12">';
  html +=
    '<img src="' +
    data_image +
    '" style="max-width: 200px;max-height: 200px;display: block;margin-left: auto;margin-right: auto;">';
  html +=
    '<input type="file" name="v_element_content_image[' +
    uniqid +
    ']" ' +
    is_required +
    ' class="form-control col-md-7 col-xs-12" accept=".jpg, .jpeg, .png" onchange="display_img_input(this, \'before\');" style="margin-top:5px">';
  html += "</div>";
  html += "</div>";
  html += '<div class="form-group">';
  html +=
    '<label class="control-label col-md-3 col-sm-3 col-xs-12">Text <span class="required">*</span></label>';
  html +=
    '<div class="col-md-9 col-sm-9 col-xs-12"><textarea name="v_element_content_text[' +
    uniqid +
    ']" id="v_element_content_text_' +
    uniqid +
    '" class="form-control col-md-7 col-xs-12 text-editor">' +
    data_text +
    "</textarea></div>";
  html += "</div>";
  html += '<div class="form-group">';
  html +=
    '<label class="control-label col-md-3 col-sm-3 col-xs-12">Text Position on Image <span class="required">*</span></label>';
  html += '<div class="col-md-6 col-sm-6 col-xs-12">';
  html +=
    '<select name="v_text_position[' +
    uniqid +
    ']" required="required" class="form-control col-md-7 col-xs-12">';
  html += '<option value="right">Right</option>';
  html += '<option value="left" ' + text_position_left + ">Left</option>";
  html += "</select>";
  html += "</div>";
  html += "</div>";
  html += "</div>";
  html += "</div>";
  html += "</div>";

  return html;
}

function set_html_video(collapsed, uniqid, data) {
  var section_name = "";
  var data_video = "";
  if (data != "") {
    section_name = data.section;
    data_video = decodeURI(data.video);
  }

  var html =
    '<div class="panel panel-content-element" id="pagebuilder_elm_' +
    uniqid +
    '">';
  html +=
    '<input type="hidden" name="v_element_type[' + uniqid + ']" value="video">';

  if (collapsed) {
    html +=
      '<a class="panel-heading" role="tab" data-toggle="collapse" data-parent="' +
      content_container +
      '" href="#collapse' +
      uniqid +
      '" aria-expanded="false">';
  } else {
    html +=
      '<a class="panel-heading" role="tab" data-toggle="collapse" data-parent="' +
      content_container +
      '" href="#collapse' +
      uniqid +
      '" aria-expanded="true">';
  }

  html +=
    '<h4 class="panel-title">Video - Section <i id=section' +
    uniqid +
    ">" +
    section_name +
    '</i><span class="pull-right"><i class="fa fa-sort"></i><i class="fa fa-trash" style="color:red; margin-left: 20px;" onclick="delete_content_element(' +
    uniqid +
    ')"></i></span></h4>';
  html += "</a>";

  if (collapsed) {
    html +=
      '<div id="collapse' +
      uniqid +
      '" class="panel-collapse collapse" role="tabpanel">';
  } else {
    html +=
      '<div id="collapse' +
      uniqid +
      '" class="panel-collapse collapse in" role="tabpanel">';
  }

  html += '<div class="panel-body">';
  html += '<div class="form-group">';
  html +=
    '<label class="control-label col-md-3 col-sm-3 col-xs-12">Section <span class="required">*</span></label>';
  html +=
    '<div class="col-md-6 col-sm-6 col-xs-12"><input type="text" autocomplete="off" value="' +
    section_name +
    '" name="v_element_section[' +
    uniqid +
    ']" required="required" class="form-control col-md-7 col-xs-12" onblur="set_section_name(' +
    uniqid +
    ', this.value)"></div>';
  html += "</div>";
  html += '<div class="form-group">';
  html +=
    '<label class="control-label col-md-3 col-sm-3 col-xs-12">Video (YouTube URL) <span class="required">*</span></label>';
  html +=
    '<div class="col-md-6 col-sm-6 col-xs-12"><input type="text" autocomplete="off" name="v_element_content_video[' +
    uniqid +
    ']" required="required" placeholder="https://www.youtube.com/watch?v=XXXX" value="' +
    data_video +
    '" class="form-control col-md-7 col-xs-12"></div>';
  html += "</div>";
  html += "</div>";
  html += "</div>";
  html += "</div>";

  return html;
}

function set_html_video_text(collapsed, uniqid, data) {
  var section_name = "";
  var data_video = "";
  var data_text = "";
  var text_position_left = "";
  if (data != "") {
    section_name = data.section;
    data_video = decodeURI(data.video);
    data_text = decodeURI(data.text);
    if (data.text_position == "left") {
      text_position_left = "selected";
    }
  }

  var html =
    '<div class="panel panel-content-element" id="pagebuilder_elm_' +
    uniqid +
    '">';
  html +=
    '<input type="hidden" name="v_element_type[' +
    uniqid +
    ']" value="video & text">';

  if (collapsed) {
    html +=
      '<a class="panel-heading" role="tab" data-toggle="collapse" data-parent="' +
      content_container +
      '" href="#collapse' +
      uniqid +
      '" aria-expanded="false">';
  } else {
    html +=
      '<a class="panel-heading" role="tab" data-toggle="collapse" data-parent="' +
      content_container +
      '" href="#collapse' +
      uniqid +
      '" aria-expanded="true">';
  }

  html +=
    '<h4 class="panel-title">Video & Text - Section <i id=section' +
    uniqid +
    ">" +
    section_name +
    '</i><span class="pull-right"><i class="fa fa-sort"></i><i class="fa fa-trash" style="color:red; margin-left: 20px;" onclick="delete_content_element(' +
    uniqid +
    ')"></i></span></h4>';
  html += "</a>";

  if (collapsed) {
    html +=
      '<div id="collapse' +
      uniqid +
      '" class="panel-collapse collapse" role="tabpanel">';
  } else {
    html +=
      '<div id="collapse' +
      uniqid +
      '" class="panel-collapse collapse in" role="tabpanel">';
  }

  html += '<div class="panel-body">';
  html += '<div class="form-group">';
  html +=
    '<label class="control-label col-md-3 col-sm-3 col-xs-12">Section <span class="required">*</span></label>';
  html +=
    '<div class="col-md-6 col-sm-6 col-xs-12"><input type="text" autocomplete="off" value="' +
    section_name +
    '" name="v_element_section[' +
    uniqid +
    ']" required="required" class="form-control col-md-7 col-xs-12" onblur="set_section_name(' +
    uniqid +
    ', this.value)"></div>';
  html += "</div>";
  html += '<div class="form-group">';
  html +=
    '<label class="control-label col-md-3 col-sm-3 col-xs-12">Video (YouTube URL) <span class="required">*</span></label>';
  html +=
    '<div class="col-md-6 col-sm-6 col-xs-12"><input type="text" autocomplete="off" name="v_element_content_video[' +
    uniqid +
    ']" required="required" placeholder="https://www.youtube.com/watch?v=XXXX" value="' +
    data_video +
    '" class="form-control col-md-7 col-xs-12"></div>';
  html += "</div>";
  html += '<div class="form-group">';
  html +=
    '<label class="control-label col-md-3 col-sm-3 col-xs-12">Text <span class="required">*</span></label>';
  html +=
    '<div class="col-md-9 col-sm-9 col-xs-12"><textarea name="v_element_content_text[' +
    uniqid +
    ']" id="v_element_content_text_' +
    uniqid +
    '" class="form-control col-md-7 col-xs-12 text-editor">' +
    data_text +
    "</textarea></div>";
  html += "</div>";
  html += '<div class="form-group">';
  html +=
    '<label class="control-label col-md-3 col-sm-3 col-xs-12">Text Position on Video <span class="required">*</span></label>';
  html += '<div class="col-md-6 col-sm-6 col-xs-12">';
  html +=
    '<select name="v_text_position[' +
    uniqid +
    ']" required="required" class="form-control col-md-7 col-xs-12">';
  html += '<option value="right">Right</option>';
  html += '<option value="left" ' + text_position_left + ">Left</option>";
  html += "</select>";
  html += "</div>";
  html += "</div>";
  html += "</div>";
  html += "</div>";
  html += "</div>";

  return html;
}

function set_html_plaintext(collapsed, uniqid, data) {
  var section_name = "";
  var data_text = "";
  if (data != "") {
    section_name = data.section;
    data_text = decodeURI(data.text);
  }

  var html =
    '<div class="panel panel-content-element" id="pagebuilder_elm_' +
    uniqid +
    '">';
  html +=
    '<input type="hidden" name="v_element_type[' +
    uniqid +
    ']" value="plain text">';

  if (collapsed) {
    html +=
      '<a class="panel-heading" role="tab" data-toggle="collapse" data-parent="' +
      content_container +
      '" href="#collapse' +
      uniqid +
      '" aria-expanded="false">';
  } else {
    html +=
      '<a class="panel-heading" role="tab" data-toggle="collapse" data-parent="' +
      content_container +
      '" href="#collapse' +
      uniqid +
      '" aria-expanded="true">';
  }

  html +=
    '<h4 class="panel-title">Plain Text - Section <i id=section' +
    uniqid +
    ">" +
    section_name +
    '</i><span class="pull-right"><i class="fa fa-sort"></i><i class="fa fa-trash" style="color:red; margin-left: 20px;" onclick="delete_content_element(' +
    uniqid +
    ')"></i></span></h4>';
  html += "</a>";

  if (collapsed) {
    html +=
      '<div id="collapse' +
      uniqid +
      '" class="panel-collapse collapse" role="tabpanel">';
  } else {
    html +=
      '<div id="collapse' +
      uniqid +
      '" class="panel-collapse collapse in" role="tabpanel">';
  }

  html += '<div class="panel-body">';
  html += '<div class="form-group">';
  html +=
    '<label class="control-label col-md-3 col-sm-3 col-xs-12">Section <span class="required">*</span></label>';
  html +=
    '<div class="col-md-6 col-sm-6 col-xs-12"><input type="text" autocomplete="off" value="' +
    section_name +
    '" name="v_element_section[' +
    uniqid +
    ']" required="required" class="form-control col-md-7 col-xs-12" onblur="set_section_name(' +
    uniqid +
    ', this.value)"></div>';
  html += "</div>";
  html += '<div class="form-group">';
  html +=
    '<label class="control-label col-md-3 col-sm-3 col-xs-12">Plain Text <span class="required">*</span></label>';
  html +=
    '<div class="col-md-9 col-sm-9 col-xs-12"><textarea name="v_element_content_text[' +
    uniqid +
    ']" required="required" class="form-control col-md-7 col-xs-12" rows="10">' +
    data_text +
    "</textarea></div>";
  html += "</div>";
  html += "</div>";
  html += "</div>";
  html += "</div>";

  return html;
}
