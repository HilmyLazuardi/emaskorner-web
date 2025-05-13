<?php

use Illuminate\Support\Facades\Session;

if (!function_exists('lang')) {
    /**
     * @author Vicky Budiman vickzkater@gmail.com
     * 
     * this function need data $translation from `app/Http/Controllers/Controller.php`
     * for set the translation phrase
     * 
     * @param string $phrase - phrase for get the translation based language used | required
     * @param array $translation - translation data | optional
     * @param array $replace_words - replace words in phrase, sample: ['#item' => 'brand', '#name' => 'Vicky'] | optional
     */
    function lang($phrase, $translation = [], $replace_words = [])
    {
        // if using API as back-end mode OR Multilingual Module is unable, so we can't get translation data
        if (env('APP_BACKEND', 'MODEL') == 'API' || env('MULTILANG_MODULE', false) == false) {
            // just return the param as it is
            $result = $phrase;
        } else {
            // get default language
            $default_lang = env('DEFAULT_LANGUAGE', 'EN');

            // set language
            $language = Session::get('language_used');
            if (empty($language)) {
                $language = $default_lang;
            }

            // set country
            $country = Session::get('country_used');
            if (empty($country)) {
                $country = env('DEFAULT_COUNTRY', 'US');
            }

            // if no translation data sent in param, then ...
            if (empty($translation)) {
                if (Session::has('sio_translations')) {
                    // get translation data from session
                    $translation = Session::get('sio_translations');
                }
            }

            if (isset($translation[$phrase])) {
                // if translation is set & data is found
                $result = $translation[$phrase];
            } else {
                // if not found the translation, just return the param as it is
                $result = $phrase;
            }
        }

        // replace words *if exist
        if (is_array($replace_words) && count($replace_words) > 0) {
            foreach ($replace_words as $key => $value) {
                $result = str_ireplace($key, $value, $result);
            }
        }

        return htmlspecialchars_decode($result);
    }
}

if (!function_exists('set_input_form')) {
    /**
     * @author Vicky Budiman vickzkater@gmail.com
     * 
     * for generate form elements
     */
    function set_input_form($type, $input_name, $label_name, $data, $errors, $required = false, $config = null)
    {
        // declare default values
        $placeholder = null;
        $id_name = null;
        $class_form_element = null;
        $value = null;
        $attributes = null;
        $defined_data = null;
        $no_image = asset('images/no-image.png');
        $delete = false;
        $viewable = false;
        $popup = false;
        $limit_chars = 0;

        // set configuration
        if ($config) {
            if (isset($config->placeholder)) {
                $placeholder = $config->placeholder;
            }
            if (isset($config->id_name)) {
                $id_name = $config->id_name;
            }
            if (isset($config->class_form_element)) {
                $class_form_element = $config->class_form_element;
            }
            if (isset($config->value)) {
                $defined_value = $config->value;
            }
            if (isset($config->attributes)) {
                $attributes = $config->attributes;
            }
            if (isset($config->input_addon)) {
                $input_addon = $config->input_addon;
            }
            if (isset($config->info_text)) {
                $info_text = $config->info_text;
            }
            if (isset($config->limit_chars)) {
                $limit_chars = (int) $config->limit_chars;
            }
            // config for textarea
            if (isset($config->rows)) {
                $textarea_rows = $config->rows;
            }
            // config for textarea
            if (isset($config->autosize)) {
                $textarea_autosize = true;
            }
            // config for switch
            if (isset($config->default)) {
                $default = $config->default;
            }
            // config for select2 & select
            if (isset($config->defined_data)) {
                $defined_data = $config->defined_data;
            }
            // config for select2
            if (isset($config->field_value)) {
                $field_value = $config->field_value;
            }
            // config for select2
            if (isset($config->field_text)) {
                $field_text = $config->field_text;
            }
            // config for select2
            if (isset($config->separator)) {
                $separator = $config->separator;
            }
            // config for image
            if (isset($config->path)) {
                $path = $config->path;
            }
            // config for image
            if (isset($config->delete)) {
                $delete = $config->delete;
            }
            // config for image
            if (isset($config->popup)) {
                $popup = $config->popup;
            }
            // config for image
            if (isset($config->info)) {
                $info = $config->info;
            }
            // config for number_format
            if (isset($config->thousand_separator)) {
                $thousand_separator = $config->thousand_separator;
            }
            // config for password
            if (isset($config->viewable)) {
                $viewable = ($config->viewable == FALSE) ? FALSE : TRUE;
            }
        }

        // set error class
        $bad_item = '';
        if ($errors->has($input_name)) {
            $bad_item = 'bad item';
        }

        // set required in label
        $span_required = '';
        $required_status = '';
        if ($required) {
            $span_required = '<span class="required" style="color:red">*</span>';
            $required_status = 'required="required"';
        }

        // set value
        if (isset($defined_value)) {
            $value = $defined_value;
        } else {
            if (old($input_name)) {
                $value = old($input_name);
            } elseif (isset($data->$input_name)) {
                $value = $data->$input_name;
            }
        }

        // set id element
        if (!$id_name) {
            $id_name = $input_name;
        }

        // config for password
        if ($viewable) {
            $input_addon = '<i class="fa fa-eye-slash" id="viewable-' . $id_name . '" style="cursor:pointer" onclick="viewable_' . $id_name . '()"></i>';
        }

        // set form element id
        $class_form_element_set = 'vinput_' . $input_name;
        if ($class_form_element) {
            $class_form_element_set = $class_form_element;
        }

        // pre-define element form input
        $element = '<div class="form-group ' . $bad_item . ' ' . $class_form_element_set . '">';
        $element .= '<label class="control-label col-md-3 col-sm-3 col-xs-12" for="' . $input_name . '">' . $label_name . ' ' . $span_required . '</label>';
        $element .= '<div class="col-md-6 col-sm-6 col-xs-12">';

        // set default properties of element input form
        $properties = 'name="' . $input_name . '" id="' . $id_name . '" placeholder="' . $placeholder . '" ' . $required_status . ' ' . $attributes;

        // set element input form
        switch ($type) {
            case 'hidden':
                $input_element = '<input type="hidden" value="' . $value . '" ' . $properties . ' />';
                break;

            case 'capital':
                $input_element = '<input type="text" value="' . $value . '" ' . $properties . ' class="form-control col-md-7 col-xs-12" style="text-transform: uppercase !important;" />';
                break;

            case 'word':
                $input_element = '<input type="text" value="' . $value . '" ' . $properties . ' class="form-control col-md-7 col-xs-12" onkeyup="username_only(this);" />';
                break;

            case 'email':
                $input_element = '<input type="email" value="' . $value . '" ' . $properties . ' class="form-control col-md-7 col-xs-12" />';
                break;

            case 'password':
                $input_element = '<input type="password" value="' . $value . '" ' . $properties . ' class="form-control col-md-7 col-xs-12" />';
                break;

            case 'number':
                $input_element = '<input type="number" value="' . $value . '" ' . $properties . ' class="form-control col-md-7 col-xs-12" />';
                break;

            case 'number_only':
                $input_element = '<input type="text" value="' . $value . '" ' . $properties . ' class="form-control col-md-7 col-xs-12" onkeyup="numbers_only(this);" />';
                break;

            case 'number_format':
                if ($value) {
                    // sanitize the value, so it must be numeric
                    $value = (int) str_replace(',', '', $value);

                    // if set thousand separator
                    if (isset($thousand_separator)) {
                        $value = number_format($value, 0, '.', $thousand_separator);
                    } else {
                        $value = number_format($value);
                    }
                }

                if (!isset($thousand_separator)) {
                    $thousand_separator = ',';
                }

                $input_element = '<input type="text" value="' . $value . '" ' . $properties . ' class="form-control col-md-7 col-xs-12" onfocus="numbers_only(this);" onkeyup="numbers_only(this);" onblur="this.value=number_formatting(this.value, \'' . $thousand_separator . '\');" />';
                break;

            case 'decimal_format':
                if ($value) {
                    // sanitize the value, so it must be numeric
                    $value = (float) str_replace(',', '', $value);

                    // if set thousand separator
                    // if (isset($thousand_separator)) {
                    //     $value = number_format($value, 0, '.', $thousand_separator);
                    // } else {
                    //     $value = number_format($value);
                    // }
                }

                if (!isset($thousand_separator)) {
                    $thousand_separator = '.';
                }

                $input_element = '<input type="text" step="0.01" value="' . $value . '" ' . $properties . ' class="form-control col-md-7 col-xs-12" onfocus="decimal_numbers_only(this);" onkeyup="decimal_numbers_only(this);" onblur="this.value=decimal_formatting(this.value, \'' . $thousand_separator . '\', 2);" />';
                break;

            case 'textarea':
                // set rows attribute
                $attr = '';
                if (isset($textarea_rows)) {
                    $attr = 'rows="' . (int) $textarea_rows . '"';
                }
                // set autosize
                $autosize = '';
                if (isset($textarea_autosize) && $textarea_autosize == true) {
                    $autosize = 'resizable_textarea';
                }
                // set limit character
                if ($limit_chars > 0) {
                    $properties .= 'maxlength="' . $limit_chars . '" onkeyup="count_chars(\'' . $input_name . '\', ' . $limit_chars . ')"';
                }
                $input_element = '<textarea ' . $properties . ' ' . $attr . ' class="form-control col-md-7 col-xs-12 ' . $autosize . '">' . $value . '</textarea>';
                if ($limit_chars > 0) {
                    $available_chars = $limit_chars;
                    if ($value != '') {
                        $available_chars = $limit_chars - strlen($value);
                        if ($available_chars < 0) {
                            $available_chars = 0;
                        }
                    }
                    $input_element .= '<span><b id="limit_chars_' . $input_name . '">' . $available_chars . '</b>/' . $limit_chars . '</span>';
                }
                break;

            case 'text_editor':
                $input_element = '<textarea ' . $properties . ' class="form-control col-md-7 col-xs-12 custom-text-editor">' . $value . '</textarea>';
                break;

            case 'switch':
                $checked = 'checked'; // default is checked
                if (empty($value)) {
                    $checked = '';
                    // set using default value: checked / ('') empty string
                    if (isset($default)) {
                        $checked = $default;
                    }
                }

                // for status or true/false (1/0)
                $values = ["0", "1"];
                if (empty($value) || in_array($value, $values)) {
                    if (isset($data->$input_name) && $data->$input_name == $values[0]) {
                        $checked = '';
                    } elseif (old($input_name) == $values[0]) {
                        $checked = '';
                    }
                    $input_element = '<div><label><input type="checkbox" class="js-switch" name="' . $input_name . '" id="' . $id_name . '" value="' . $values[1] . '" ' . $checked . ' ' . $attributes . ' /></label></div>';
                } else {
                    $input_element = '<div><label><input type="checkbox" class="js-switch" name="' . $input_name . '" id="' . $id_name . '" value="' . $value . '" ' . $checked . ' ' . $attributes . ' /></label></div>';
                }
                break;

            case 'select2':
                $input_element = '<select ' . $properties . ' class="form-control select2">';
                if (!empty($placeholder)) {
                    $input_element .= '<option value="" disabled selected>' . $placeholder . '</option>';
                }
                // set options
                if (!empty($defined_data)) {
                    // default values
                    $set_value = 'id';
                    $set_label = 'name';
                    // set field for options value
                    if (isset($field_value)) {
                        $set_value = $field_value;
                    }
                    // set field for options text
                    if (isset($field_text)) {
                        $set_label = $field_text;
                        // if set options text more than 1 field
                        if (isset($separator)) {
                            $set_label = explode($separator, $field_text);
                        }
                    }
                    // set options
                    foreach ($defined_data as $item) {
                        // set "selected" attribute
                        $stats = '';
                        if ($item->$set_value == $value) {
                            $stats = 'selected';
                        }

                        // set options text
                        if (is_array($set_label)) {
                            // set options text more than 1 field
                            $labels = [];
                            foreach ($set_label as $val) {
                                $labels[] = $item->$val;
                            }
                            $label = implode($separator, $labels);
                        } else {
                            // set options text using 1 field
                            $label = $item->$set_label;
                        }
                        // set HTML
                        $input_element .= '<option value="' . $item->$set_value . '" ' . $stats . '>' . $label . '</option>';
                    }
                } else {
                    $input_element .= '<option value="" disabled>NO DATA</option>';
                }
                $input_element .= '</select>';
                break;

            case 'select':
                $input_element = '<select ' . $properties . ' class="form-control">';
                if (!empty($placeholder)) {
                    $input_element .= '<option value="" disabled selected>' . $placeholder . '</option>';
                }
                // set options
                if (!empty($defined_data)) {
                    if (isset($defined_data[0])) {
                        foreach ($defined_data as $opt) {
                            $stats = '';
                            if ($opt == $value && !empty($value)) {
                                $stats = 'selected';
                            }

                            $input_element .= '<option value="' . $opt . '" ' . $stats . '>' . $opt . '</option>';
                        }
                    } else {
                        foreach ($defined_data as $key => $val) {
                            $stats = '';
                            if ($key == $value && !empty($value)) {
                                $stats = 'selected';
                            }

                            $input_element .= '<option value="' . $key . '" ' . $stats . '>' . $val . '</option>';
                        }
                    }
                } else {
                    $input_element .= '<option value="" disabled>NO DATA</option>';
                }
                $input_element .= '</select>';
                break;

            case 'image':
                if (empty($value)) {
                    // default image
                    $img_src = $no_image;
                } else {
                    // set image using "$value" only
                    $img_src = asset($value);
                    if (isset($path)) {
                        // set image using "$path" & "$value"
                        $img_src = asset($path . $value);
                    }
                    // auto re-cache image every hour
                    $img_src .= '?v=' . date('YmdH');
                }
                if ($popup) {
                    // PhotoSwipe Gallery
                    $input_element = ' <div class="my-gallery" itemscope itemtype="http://schema.org/ImageGallery">';
                    // get the image size
                    $width = 1280;
                    $height = 720;
                    if (ini_get('allow_url_fopen')) {
                        // $imgPath = public_path('images/logo-square.png');
                        // $imgPath2 = $img_src;
                        // dd(file_exists($imgPath), $imgPath, $imgPath2);
                        // dd(file_exists($img_src));
                        // if (file_exists($imgPath)) {
                            // dd('ada');/
                            // $size = getimagesize($imgPath);
                        // } else {
                            // dd('tidak');
                            // echo "File tidak ditemukan!";
                        // }
                        // dd($size);

                        list($width, $height) = getimagesize($img_src);
                        // list($width, $height) = $size;
                    }
                    $input_element .= ' <figure itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">';
                    $input_element .= ' <a href="' . $img_src . '" itemprop="contentUrl" data-size="' . $width . 'x' . $height . '">';
                    $input_element .= ' <img id="vimg_preview_' . $input_name . '" src="' . $img_src . '" itemprop="thumbnail" style="max-width: 200px" data-toggle="tooltip" data-placement="right" title="click image to enlarge" />';
                    $input_element .= ' </a>';
                    $input_element .= ' </figure>';
                    $input_element .= ' </div><!-- /.my-gallery -->';
                } else {
                    $input_element = '<img id="vimg_preview_' . $input_name . '" src="' . $img_src . '" style="max-width:200px;" />';
                }
                $input_element .= '<input type="file" ' . $properties . ' class="form-control col-md-7 col-xs-12" accept="image/*" onchange="readURL(this, \'before\', \'vimg_preview_' . $input_name . '\', \'' . $no_image . '\', \'' . $img_src . '\');" style="margin-top:5px" />';
                if (isset($info)) {
                    $input_element .= '<br><span>' . $info . '</span>';
                }
                if (!empty($value) && $delete) {
                    $input_element .= '<br><span class="btn btn-warning btn-xs" id="vimg_preview_' . $id_name . '-delbtn" style="margin: 5px 0 !important;" onclick="reset_img_preview(\'vimg_preview_' . $input_name . '\', \'' . $no_image . '\')">Delete uploaded image?</span>';
                    $input_element .= ' <input type="hidden" name="' . $input_name . '_delete" id="vimg_preview_' . $input_name . '-delete">';
                }
                break;

            case 'file':
                $input_element = '';
                if (!empty($value)) {
                    // validate $value is local path or link
                    $string = filter_var($value, FILTER_VALIDATE_URL);
                    // for sanitize (">) ('>)
                    if ($string == 34 || $string == 39 || $string == false) {
                        // local path
                        $url_value = asset($value);
                    } else {
                        $headers = get_headers($value);
                        $value_is_url = stripos($headers[0], "200 OK") ? true : false;

                        if ($value_is_url) {
                            // link
                            $url_value = $value;
                        } else {
                            // local path
                            $url_value = asset($value);
                        }
                    }

                    $input_element .= '<a href="' . $url_value . '" target="_blank" id="' . $id_name . '-file-preview">' . $url_value . ' <i class="fa fa-external-link"></i></a>';
                    if ($delete) {
                        $input_element .= '&nbsp; <span class="btn btn-danger btn-xs" id="' . $id_name . '-delbtn" style="margin: 5px 0 !important;" onclick="remove_uploaded_file(\'#' . $id_name . '\')"><i class="fa fa-trash"></i></span><br>';
                        $input_element .= ' <input type="hidden" name="' . $input_name . '_delete" id="' . $input_name . '-delete">';
                    } else {
                        $input_element .= '<br>';
                    }
                }
                $input_element .= '<input type="file" ' . $properties . ' class="form-control col-md-7 col-xs-12" />';
                break;

            case 'tags':
                $input_element = '<input type="text" class="tags tagsinput form-control col-md-7 col-xs-12" value="' . $value . '" ' . $properties . ' />';
                break;

            case 'datepicker':
                if ($value) {
                    if (strpos($value, '-') !== false) {
                        // convert date format
                        $date_arr = explode('-', $value);
                        if (count($date_arr) > 0) {
                            $date_formatted = $date_arr[2] . '/' . $date_arr[1] . '/' . $date_arr[0];
                            $value = $date_formatted;
                        }
                    }
                }
                $input_element = '<div class="input-group date input-datepicker">';
                $input_element .= '<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>';
                $input_element .= '<input type="text" value="' . $value . '" ' . $properties . ' class="form-control" autocomplete="off" /></div>';
                break;

            case 'datetimepicker':
                if ($value) {
                    $value = date('d/m/Y H:i', strtotime($value));
                }
                $input_element = '<div class="input-group date input-datetimepicker">';
                $input_element .= '<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>';
                $input_element .= '<input type="text" value="' . $value . '" ' . $properties . ' class="form-control" autocomplete="off" /></div>';
                break;

            default:
                // text
                if ($limit_chars > 0) {
                    $properties .= 'maxlength="' . $limit_chars . '" onkeyup="count_chars(\'' . $input_name . '\', ' . $limit_chars . ')"';
                }
                $input_element = '<input type="text" value="' . $value . '" ' . $properties . ' class="form-control col-md-7 col-xs-12" />';
                if ($limit_chars > 0) {
                    $available_chars = $limit_chars;
                    if ($value != '') {
                        $available_chars = $limit_chars - strlen($value);
                        if ($available_chars < 0) {
                            $available_chars = 0;
                        }
                    }
                    $input_element .= '<span><b id="limit_chars_' . $input_name . '">' . $available_chars . '</b>/' . $limit_chars . '</span>';
                }
                break;
        }

        // set input group addon
        if (isset($input_addon)) {
            $element .= '<div class="input-group">';
            $element .= '<span class="input-group-addon">' . $input_addon . '</span>';
            $element .= $input_element;
            $element .= '</div>';
        } else {
            $element .= $input_element;
        }

        // add info text
        if (isset($info_text)) {
            $element .= '<span>' . $info_text . '</span>';
        }

        // set error message
        if ($errors->has($input_name)) {
            $element .= '<div class="text-danger">' . $errors->first($input_name) . '</div>';
        }
        $element .= '</div></div>';

        // config for password
        if ($viewable) {
            $element .= ' <script>
                function viewable_' . $id_name . '() {
                    var id_name = "' . $id_name . '";
                    var element = document.getElementById(id_name);
                    var element_icon = document.getElementById("viewable-"+id_name);
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
            </script>';
        }

        // special case
        if ($type == 'hidden') {
            $element = $input_element;
        }

        return $element;
    }
}
