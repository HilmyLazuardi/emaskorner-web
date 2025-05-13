/**
 * The Helper JS - a lot of JS helper functions that are ready to help in your project
 * Version: 1.0 (2020-07-04)
 *
 * Copyright (c) KINIDI Tech and other contributors
 * Released under the MIT license.
 * For more information, see https://kiniditech.com/ or https://github.com/vickzkater
 *
 * https://github.com/vickzkater/the-helper-js
 */

/**
 * for displaying image after browse it before uploaded
 * Example: implement onchange="readURL(this, 'before')" in input type file in form
 *
 * @param {element input file form} input
 * @param {string} position_image - (after/before)
 * @param {string} default_image
 * @param {string} existing_image - (after/before)
 */
function readURL(input, position_image = "after", image_id = 'none', default_image = 'none', existing_image = 'none') {
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

/**
 * for reset image preview to default image (no image)
 *
 * @param {element id of image} image_id
 * @param {string} no_image - image "no image" URL
 */
function reset_img_preview(image_id, no_image) {
    if (confirm("Are you sure to delete this uploaded image?")) {
        $('#' + image_id).attr("src", no_image);
        $('#' + image_id + "-delbtn").hide();
        $('#' + image_id + "-delete").val("yes");
    }
}

/**
 * for replaces some characters with some other characters in a string.
 */
function replace_all(search, replacement, target) {
    if (target !== null) {
        return target.split(search).join(replacement);
    }
    return "";
}

/**
 * formats a number with grouped thousands.
 *
 * @param {integer} number required
 * @param {char} separator optional
 */
function number_formatting(number, separator = ",") {
    // sanitizing number
    number = replace_all(" ", "", number);
    number = replace_all(",", "", number);
    number = replace_all(".", "", number);

    // check is negative number
    var negative = number.substring(0, 1);
    if (negative == "-") {
        number = number.substring(1);
    } else {
        negative = "";
    }

    number = "" + Math.round(number);
    if (number.length > 3) {
        var mod = number.length % 3;
        var output = mod > 0 ? number.substring(0, mod) : "";
        for (i = 0; i < Math.floor(number.length / 3); i++) {
            if (mod == 0 && i == 0)
                output += number.substring(mod + 3 * i, mod + 3 * i + 3);
            else
                output +=
                separator + number.substring(mod + 3 * i, mod + 3 * i + 3);
        }
        return negative + output;
    } else return negative + number;
}

/**
 * formats a decimal with grouped thousands.
 *
 * @param {char} number required
 * @param {integer} decimals optional
 * @param {char} decimal_separator optional
 */
 function decimal_formatting(number, decimal_separator, decimals) {
    number = parseFloat(number) || 0;
    decimal_separator = decimal_separator || "."; // Default to period as decimal separator
    decimals = decimals || 2; // Default to 2 decimals

    return number.toLocaleString().split(decimal_separator)[0]
        + decimal_separator
        + number.toFixed(decimals).split(decimal_separator)[1];
}

/**
 * copy text to the clipboard
 *
 * @param {element input id} element_id
 * @param {boolean} alert_copied
 * @param {string} alert_message
 */
function click_to_clipboard(
    element_id,
    alert_copied = true,
    alert_message = "Copied the text: "
) {
    /* Get the text field */
    var copyText = document.getElementById(element_id);

    /* Select the text field */
    copyText.select();
    copyText.setSelectionRange(0, 99999); /*For mobile devices*/

    /* Copy the text inside the text field */
    document.execCommand("copy");

    if (alert_copied) {
        /* Alert the copied text */
        alert(alert_message + copyText.value);
    }
}

/**
 * to open a new window
 *
 * @param {string} params - URL
 * @param {integer} width
 * @param {integer} height
 * @param {string} name - window title
 */
function open_window(params, width, height, name) {
    var screenLeft = 0,
        screenTop = 0;

    if (!name) name = "MyWindow";
    if (!width) width = 600;
    if (!height) height = 600;

    var defaultParams = {};

    if (typeof window.screenLeft !== "undefined") {
        screenLeft = window.screenLeft;
        screenTop = window.screenTop;
    } else if (typeof window.screenX !== "undefined") {
        screenLeft = window.screenX;
        screenTop = window.screenY;
    }

    var features_dict = {
        toolbar: "no",
        location: "no",
        directories: "no",
        left: screenLeft + ($(window).width() - width) / 2,
        top: screenTop + ($(window).height() - height) / 2,
        status: "yes",
        menubar: "no",
        scrollbars: "yes",
        resizable: "no",
        width: width,
        height: height,
    };
    features_arr = [];
    for (var k in features_dict) {
        features_arr.push(k + "=" + features_dict[k]);
    }
    features_str = features_arr.join(",");

    // var qs = "?" + $.param($.extend({}, defaultParams, params));
    // var win = window.open(qs, name, features_str);
    var win = window.open(params, name, features_str);
    win.focus();
    return false;
}

/**
 * set URL parameters (request URI)
 * 
 * @param {string} key
 * @param {string} value 
 */
function set_param_url(key, value) {
    var uri = window.location.href;
    var result = uri;
    var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
    var separator = uri.indexOf("?") !== -1 ? "&" : "?";
    if (uri.match(re)) {
        result = uri.replace(re, "$1" + key + "=" + value + "$2");
    } else {
        result = uri + separator + key + "=" + value;
    }

    var arr_uri = result.split("?");

    window.history.pushState(
        $(document).find("title").text(),
        $(document).find("title").text(),
        "?" + arr_uri[1]
    );
}

/**
 * sanitizing value of number
 *
 * @param {element input text} elm
 */
function numbers_only(elm) {
    var code = elm.which ? elm.which : elm.keyCode;

    // 37 = left arrow, 39 = right arrow.
    if (code !== 37 && code !== 39) {
        elm.value = elm.value.replace(/[^0-9]/g, "");
    }
}

/**
 * sanitizing value of decimal_number
 *
 * @param {element input text} elm
 */
 function decimal_numbers_only(elm) {
    var code = elm.which ? elm.which : elm.keyCode;

    // 37 = left arrow, 39 = right arrow.
    if (code !== 37 && code !== 39) {
        elm.value = elm.value.replace(/[^0-9.]/g, "");
    }
}

/**
 * sanitizing value of username - only allow alphanumerics, (.) dot, and (_) underscore
 * 
 * @param {element input form} elm 
 */
function username_only(elm) {
    var code = elm.which ? elm.which : elm.keyCode;

    // 37 = left arrow, 39 = right arrow.
    if (code !== 37 && code !== 39) {
        elm.value = elm.value.replace(/[^a-z0-9A-Z_.]/g, "");
    }
}

/**
 * sanitizing value of text - only allow alphanumerics and whitespace
 * 
 * @param {element input form} elm 
 */
function alphanumerics_only(elm) {
    var code = elm.which ? elm.which : elm.keyCode;

    // 37 = left arrow, 39 = right arrow.
    if (code !== 37 && code !== 39) {
        elm.value = elm.value.replace(/[^a-z0-9A-Z ]/g, "");
    }
}

/**
 * for remove uploaded file
 *
 * @param {element id} input
 */
function remove_uploaded_file(input) {
    if (confirm("Are you sure to delete this uploaded file?")) {
        $(input + "-file-preview").remove();
        $(input + "-delbtn").hide();
        $(input + "-delete").val("yes");
    }
}

/**
 * for show/hide input password
 *
 * @param {element id} id_name
 */
function viewable_password(id_name) {
    var element = document.getElementById(id_name);
    var element_icon = document.getElementById("viewable-" + id_name);
    var arr, replaced_icon;
    if (element.type == "password") {
        element.type = "text";

        element_icon.className = element_icon.className.replace(/\bfa-eye-slash\b/g, "");

        replaced_icon = "fa-eye"
        arr = element_icon.className.split(" ");
        if (arr.indexOf(replaced_icon) == -1) {
            element_icon.className += " " + replaced_icon;
        }
    } else {
        element.type = "password";

        element_icon.className = element_icon.className.replace(/\bfa-eye\b/g, "");

        replaced_icon = "fa-eye-slash"
        arr = element_icon.className.split(" ");
        if (arr.indexOf(replaced_icon) == -1) {
            element_icon.className += " " + replaced_icon;
        }
    }
}

/**
 * for get date & time value, sample: 2021-05-25 08:12:01
 */
function datetime_format(input_datetime = '') {
    if (input_datetime != '') {
        var now = new Date(input_datetime);
    } else {
        var now = new Date();
    }

    var year = now.getFullYear();
    var mon = now.getMonth() + 1;
    if (mon < 10) {
        mon = '0' + mon;
    }
    var date = now.getDate();
    if (date < 10) {
        date = '0' + date;
    }
    var hour = now.getHours();
    if (hour < 10) {
        hour = '0' + hour;
    }
    var mins = now.getMinutes();
    if (mins < 10) {
        mins = '0' + mins;
    }
    var secs = now.getSeconds();
    if (secs < 10) {
        secs = '0' + secs;
    }

    var timestamp_now = year + '-' + mon + '-' + date + ' ' + hour + ':' + mins + ':' + secs;
    return timestamp_now
}

function set_cookie(name,value,days) {
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days*24*60*60*1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "")  + expires + "; path=/";
}

function get_cookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}
