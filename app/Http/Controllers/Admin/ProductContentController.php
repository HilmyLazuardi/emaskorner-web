<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// LIBRARIES
use App\Libraries\Helper;

// MODELS
use App\Models\product_item;

class ProductContentController extends Controller
{
    // set this module
    private $module     = 'Product Content';
    private $module_id  = 25;

    private $item = 'product content';

    public function view($product_item_id)
    {
        // authorizing...
        $authorize = Helper::authorizing($this->module, 'View Details');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        $raw_product_item_id = $product_item_id;

        if (env('CRYPTOGRAPHY_MODE', false)) {
            $product_item_id = Helper::validate_token($product_item_id);
        }

        $data = product_item::find($product_item_id);

        return view('admin.product_content.form', compact('data', 'raw_product_item_id'));
    }

    public function update($product_item_id, Request $request)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'Edit');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        // SET THIS OBJECT/ITEM NAME BASED ON TRANSLATION
        $this->item = ucwords(lang($this->item, $this->translations));

        // LARAVEL VALIDATION
        $validation = [
            // 'v_page_section' => 'required'
            'v_element_type' => 'required'
        ];
        $message = [
            'required' => ':attribute ' . lang('is required', $this->translations)
        ];
        $names = [
            // 'v_page_section' => ucwords(lang('content', $this->translations))
            'v_element_type' => ucwords(lang('content element', $this->translations))
        ];
        $this->validate($request, $validation, $message, $names);

        $raw_product_item_id = $product_item_id;

        if (env('CRYPTOGRAPHY_MODE', false)) {
            $product_item_id = Helper::validate_token($product_item_id);
        }

        // GET EXISTING DATA
        $data = product_item::find($product_item_id);
        $msg = 'Successfully updated #item';
        if (!$data) {
            $data   = new product_item();
            $msg = 'Successfully created #item';
            $full_content = $this->insert_content($request);
        } else {
            // $full_content = $this->update_content($request);
            $full_content = $this->update_content($request, $data);
        }

        // SAVE THE DATA
        $data->details = json_encode($full_content);

        if ($data->save()) {
            // SUCCESS
            return redirect()
                ->route('admin.product_content', $raw_product_item_id)
                ->with('success', lang($msg, $this->translations, ['#item' => $this->item]));
        }

        // FAILED
        return back()
            ->withInput()
            ->with('error', lang('Oops, failed to update #item . Please try again.', $this->translations, ['#item' => $this->item]));
    }
    
    private function insert_content_v1($request)
    {
        // PROCESSING CONTENT ELEMENT
        /**
         * ARRAY 1 - SECTIONS
         * 
         * array[]
         * - item
         */
        $v_page_section = $request->v_page_section;
        $v_page_section_style = $request->v_page_section_style;

        /**
         * ARRAY 2 - CONTENTS
         * 
         * array[]
         * - array[]
         * - - item
         */
        $v_page_element_type = $request->v_page_element_type;
        $v_page_element_section = $request->v_page_element_section;
        $v_page_element_status = $request->v_page_element_status;

        $full_content = [];
        foreach ($v_page_section as $key => $value) {
            // SAVE PER ELEMENT USING OBJECT
            $obj_content = new \stdClass();

            // PROCESSING ARRAY 1 DATA
            $obj_content->v_page_section = $value;
            $obj_content->v_page_section_style = $v_page_section_style[$key];

            // PROCESSING ARRAY 2 DATA
            // CREATE ARRAY VARIABLE TO STORE ARRAY 2 DATA
            $array2 = [];

            // LOOPING ARRAY 2 DATA PER ELEMENT TYPE FOR PROCESSING ARRAY 3 DATA
            foreach ($v_page_element_type[$key] as $key2 => $value2) {
                // SAVE ARRAY 2 DATA USING OBJECT
                $obj_2 = new \stdClass();
                // SAVE CONTENT ELEMENT SECTION NAME
                $obj_2->v_page_element_section = $v_page_element_section[$key][$key2];
                // SAVE CONTENT ELEMENT STATUS
                $obj_2->v_page_element_status = $v_page_element_status[$key][$key2];
                // SAVE CONTENT ELEMENT TYPE
                $obj_2->v_page_element_type = $value2;

                // CREATE ARRAY VARIABLE TO STORE ARRAY 3 DATA
                $array3 = [];

                // PROCESSING ARRAY 3 DATA
                switch ($value2) {
                    case 'masthead':
                        /**
                         * ARRAY 3 - ITEMS
                         * 
                         * array[]
                         * - array[]
                         * - - array[]
                         * - - - item
                         */
                        $v_page_element_image_title = $request->v_page_element_image_title;
                        $v_page_element_image = $request->v_page_element_image;
                        if ($request->v_page_element_imagex) {
                            $v_page_element_imagex = $request->v_page_element_imagex;
                        }
                        $v_page_element_image_mobile = $request->v_page_element_image_mobile;
                        if ($request->v_page_element_image_mobilex) {
                            $v_page_element_image_mobilex = $request->v_page_element_image_mobilex;
                        }
                        $v_page_element_title = $request->v_page_element_title;
                        $v_page_element_title_style = $request->v_page_element_title_style;
                        $v_page_element_subtitle = $request->v_page_element_subtitle;
                        $v_page_element_subtitle_style = $request->v_page_element_subtitle_style;
                        $v_page_element_alignment = $request->v_page_element_alignment;
                        $v_page_element_status_item = $request->v_page_element_status_item;

                        /**
                         * ARRAY 4 - SUB-ITEMS
                         * 
                         * array[]
                         * - array[]
                         * - - array[]
                         * - - - array[]
                         * - - - - item
                         */
                        $v_page_element_button_link_type = $request->v_page_element_button_link_type;
                        $v_page_element_button_link_internal = $request->v_page_element_button_link_internal;
                        $v_page_element_button_link_external = $request->v_page_element_button_link_external;
                        $v_page_element_button_link_target = $request->v_page_element_button_link_target;
                        $v_page_element_button_label = $request->v_page_element_button_label;
                        $v_page_element_button_style = $request->v_page_element_button_style;

                        // SUPPORT MULTIPLE ITEM
                        foreach ($v_page_element_status_item[$key][$key2] as $key3 => $value3) {
                            // SAVE ARRAY 3 DATA USING OBJECT
                            $obj_3 = new \stdClass();
                            $obj_3->v_page_element_image_title = $v_page_element_image_title[$key][$key2][$key3];
                            $obj_3->v_page_element_title = $v_page_element_title[$key][$key2][$key3];
                            $obj_3->v_page_element_title_style = $v_page_element_title_style[$key][$key2][$key3];
                            $obj_3->v_page_element_subtitle = $v_page_element_subtitle[$key][$key2][$key3];
                            $obj_3->v_page_element_subtitle_style = $v_page_element_subtitle_style[$key][$key2][$key3];
                            $obj_3->v_page_element_alignment = $v_page_element_alignment[$key][$key2][$key3];
                            $obj_3->v_page_element_status_item = $value3;

                            if (isset($v_page_element_image[$key][$key2][$key3])) {
                                // IF UPLOAD NEW IMAGE
                            
                                // PROCESSING IMAGE - v_page_element_image
                                $dir_path = 'uploads/page/';
                                $image_file = $v_page_element_image[$key][$key2][$key3];
                                $format_image_name = $key3 . '-' . $value2 . '-' . Helper::unique_string();
                                $image = Helper::upload_image($dir_path, $image_file, true, $format_image_name);
                                if ($image['status'] != 'true') {
                                    return back()
                                    ->withInput()
                                    ->with('error', lang('Oops, failed to upload image #item - #name. Please try again or try upload another one.', $this->translation, ['#item' => $value2, '#name' => $obj_3->v_page_element_image_title]));
                                }
                                $obj_3->v_page_element_image = $dir_path . $image['data'];
                            } elseif (isset($v_page_element_imagex[$key][$key2][$key3])) {
                                // USE EXISTING IMAGE
                                $obj_3->v_page_element_image = $v_page_element_imagex[$key][$key2][$key3];
                            } else {
                                //  ERROR: MUST UPLOAD IMAGE FOR NEW ELEMENT
                                return back()
                                ->withInput()
                                ->with(
                                    'error',
                                    lang(
                                        'Oops, you must upload image #item for #name. Please try again or try upload another one.',
                                        $this->translation,
                                        [
                                            '#item' => $value2,
                                            '#name' => $obj_3->v_page_element_image_title
                                        ]
                                    )
                                );
                            }

                            if (isset($v_page_element_image[$key][$key2][$key3])) {
                                // IF UPLOAD NEW IMAGE

                                // PROCESSING IMAGE - v_page_element_image_mobile
                                $dir_path = 'uploads/page/';
                                $image_file = $v_page_element_image_mobile[$key][$key2][$key3];
                                $format_image_name = $key3 . '-' . $value2 . '_mobile' . '-' . Helper::unique_string();
                                $image = Helper::upload_image($dir_path, $image_file, true, $format_image_name);
                                if ($image['status'] != 'true') {
                                    return back()
                                        ->withInput()
                                        ->with('error', lang('Oops, failed to upload image #item - #name. Please try again or try upload another one.', $this->translation, ['#item' => $value2, '#name' => $obj_3->v_page_element_image_title]));
                                }
                                $obj_3->v_page_element_image_mobile = $dir_path . $image['data'];
                            } elseif (isset($v_page_element_image_mobilex[$key][$key2][$key3])) {
                                // USE EXISTING IMAGE
                                $obj_3->v_page_element_image_mobile = $v_page_element_image_mobilex[$key][$key2][$key3];
                            } else {
                                //  ERROR: MUST UPLOAD IMAGE FOR NEW ELEMENT
                                return back()
                                    ->withInput()
                                    ->with(
                                        'error',
                                        lang(
                                            'Oops, you must upload image mobile #item for #name. Please try again or try upload another one.',
                                            $this->translation,
                                            [
                                                '#item' => $value2,
                                                '#name' => $obj_3->v_page_element_image_title
                                            ]
                                        )
                                    );
                            }

                            // CREATE ARRAY VARIABLE TO STORE ARRAY 4 DATA
                            $array4 = [];

                            // PROCESSING ARRAY 4 DATA
                            foreach ($v_page_element_button_link_type[$key][$key2][$key3] as $key4 => $value4) {
                                // SAVE ARRAY 4 DATA USING OBJECT
                                $obj_4 = new \stdClass();
                                $obj_4->v_page_element_button_link_type = $value4;
                                $obj_4->v_page_element_button_link_internal = $v_page_element_button_link_internal[$key][$key2][$key3][$key4];
                                $obj_4->v_page_element_button_link_external = $v_page_element_button_link_external[$key][$key2][$key3][$key4];
                                $obj_4->v_page_element_button_link_target = $v_page_element_button_link_target[$key][$key2][$key3][$key4];
                                $obj_4->v_page_element_button_label = $v_page_element_button_label[$key][$key2][$key3][$key4];
                                $obj_4->v_page_element_button_style = $v_page_element_button_style[$key][$key2][$key3][$key4];

                                // SAVE OBJECT 4 IN ARRAY 4
                                $array4[$key4] = $obj_4;
                            }

                            // SAVE ARRAY 4 TO OBJECT 3
                            $obj_3->sub_items = $array4;

                            // SAVE OBJECT 3 IN ARRAY 3
                            $array3[$key3] = $obj_3;
                        }
                        break;

                    case 'text':
                        /**
                         * ARRAY 2 - CONTENTS
                         * 
                         * array[]
                         * - array[]
                         * - - item
                         */
                        $v_page_element_width = $request->v_page_element_width;
                        $v_page_element_alignment = $request->v_page_element_alignment;
                        $v_page_element_total_item = $request->v_page_element_total_item;

                        /**
                         * ARRAY 3 - ITEMS
                         * 
                         * array[]
                         * - array[]
                         * - - array[]
                         * - - - item
                         */
                        $v_page_element_text = $request->v_page_element_text;

                        // PROCESSING ARRAY 2 DATA
                        $obj_2->v_page_element_width = $v_page_element_width[$key][$key2];
                        $obj_2->v_page_element_alignment = $v_page_element_alignment[$key][$key2];
                        $obj_2->v_page_element_total_item = $v_page_element_total_item[$key][$key2];

                        // SUPPORT 3 ITEMS - PROCESSING ARRAY 3 DATA
                        for ($i = 0; $i < 3; $i++) {
                            $array3[] = $v_page_element_text[$key][$key2][$i];
                        }
                        break;

                    case 'image':
                        /**
                         * ARRAY 2 - CONTENTS
                         * 
                         * array[]
                         * - array[]
                         * - - item
                         */
                        $v_page_element_image_type = $request->v_page_element_image_type;

                        /**
                         * ARRAY 3 - ITEMS
                         * 
                         * array[]
                         * - array[]
                         * - - array[]
                         * - - - item
                         */
                        $v_page_element_image_title = $request->v_page_element_image_title;
                        $v_page_element_link_type = $request->v_page_element_link_type;
                        $v_page_element_link_internal = $request->v_page_element_link_internal;
                        $v_page_element_link_external = $request->v_page_element_link_external;
                        $v_page_element_link_target = $request->v_page_element_link_target;
                        $v_page_element_status_item = $request->v_page_element_status_item;
                        $v_page_element_image = $request->v_page_element_image;
                        if ($request->v_page_element_imagex) {
                            $v_page_element_imagex = $request->v_page_element_imagex;
                        }

                        // PROCESSING ARRAY 2 DATA
                        $obj_2->v_page_element_image_type = $v_page_element_image_type[$key][$key2];

                        // SUPPORT MULTIPLE ITEM
                        foreach ($v_page_element_status_item[$key][$key2] as $key3 => $value3) {
                            // SAVE ARRAY 3 DATA USING OBJECT
                            $obj_3 = new \stdClass();
                            $obj_3->v_page_element_image_title = $v_page_element_image_title[$key][$key2][$key3];
                            $obj_3->v_page_element_link_type = $v_page_element_link_type[$key][$key2][$key3];
                            $obj_3->v_page_element_link_internal = $v_page_element_link_internal[$key][$key2][$key3];
                            $obj_3->v_page_element_link_external = $v_page_element_link_external[$key][$key2][$key3];
                            $obj_3->v_page_element_link_target = $v_page_element_link_target[$key][$key2][$key3];
                            $obj_3->v_page_element_status_item = $value3;

                            if (isset($v_page_element_image[$key][$key2][$key3])) {
                                // IF UPLOAD NEW IMAGE

                                // PROCESSING IMAGE - v_page_element_image
                                $dir_path = 'uploads/page/';
                                $image_file = $v_page_element_image[$key][$key2][$key3];
                                $format_image_name = $key3 . '-' . $value2 . '-' . Helper::unique_string();
                                $image = Helper::upload_image($dir_path, $image_file, true, $format_image_name);
                                if ($image['status'] != 'true') {
                                    return back()
                                        ->withInput()
                                        ->with('error', lang('Oops, failed to upload image #item - #name. Please try again or try upload another one.', $this->translation, ['#item' => $value2, '#name' => $obj_3->v_page_element_image_title]));
                                }
                                $obj_3->v_page_element_image = $dir_path . $image['data'];
                            } elseif (isset($v_page_element_imagex[$key][$key2][$key3])) {
                                // USE EXISTING IMAGE
                                $obj_3->v_page_element_image = $v_page_element_imagex[$key][$key2][$key3];
                            } else {
                                //  ERROR: MUST UPLOAD IMAGE FOR NEW ELEMENT
                                return back()
                                    ->withInput()
                                    ->with(
                                        'error',
                                        lang(
                                            'Oops, you must upload image #item for #name. Please try again or try upload another one.',
                                            $this->translation,
                                            [
                                                '#item' => $value2,
                                                '#name' => $obj_3->v_page_element_image_title
                                            ]
                                        )
                                    );
                            }

                            // SAVE OBJECT 3 IN ARRAY 3
                            $array3[$key3] = $obj_3;
                        }

                        break;

                    case 'image + text + button':
                        /**
                         * ARRAY 2 - CONTENTS
                         * 
                         * array[]
                         * - array[]
                         * - - item
                         */
                        $v_page_element_alignment = $request->v_page_element_alignment;
                        $v_page_element_total_item = $request->v_page_element_total_item;

                        /**
                         * ARRAY 3 - ITEMS
                         * 
                         * array[]
                         * - array[]
                         * - - array[]
                         * - - - item
                         */
                        $v_page_element_image_title = $request->v_page_element_image_title;
                        $v_page_element_text = $request->v_page_element_text;
                        $v_page_element_button_link_type = $request->v_page_element_button_link_type;
                        $v_page_element_button_link_internal = $request->v_page_element_button_link_internal;
                        $v_page_element_button_link_external = $request->v_page_element_button_link_external;
                        $v_page_element_button_link_target = $request->v_page_element_button_link_target;
                        $v_page_element_button_label = $request->v_page_element_button_label;
                        $v_page_element_button_style = $request->v_page_element_button_style;
                        $v_page_element_image = $request->v_page_element_image;
                        if ($request->v_page_element_imagex) {
                            $v_page_element_imagex = $request->v_page_element_imagex;
                        }

                        // PROCESSING ARRAY 2 DATA
                        $obj_2->v_page_element_alignment = $v_page_element_alignment[$key][$key2];
                        $obj_2->v_page_element_total_item = $v_page_element_total_item[$key][$key2];

                        // SUPPORT 3 ITEMS - PROCESSING ARRAY 3 DATA
                        for ($i = 0; $i < 3; $i++) {
                            // SAVE ARRAY 3 DATA USING OBJECT
                            $obj_3 = new \stdClass();
                            $obj_3->v_page_element_image_title = $v_page_element_image_title[$key][$key2][$i];
                            $obj_3->v_page_element_text = $v_page_element_text[$key][$key2][$i];
                            $obj_3->v_page_element_button_link_type = $v_page_element_button_link_type[$key][$key2][$i];
                            $obj_3->v_page_element_button_link_internal = $v_page_element_button_link_internal[$key][$key2][$i];
                            $obj_3->v_page_element_button_link_external = $v_page_element_button_link_external[$key][$key2][$i];
                            $obj_3->v_page_element_button_link_target = $v_page_element_button_link_target[$key][$key2][$i];
                            $obj_3->v_page_element_button_label = $v_page_element_button_label[$key][$key2][$i];
                            $obj_3->v_page_element_button_style = $v_page_element_button_style[$key][$key2][$i];

                            // IF UPLOAD AN IMAGE
                            if (isset($v_page_element_image[$key][$key2][$i])) {

                                // PROCESSING IMAGE - v_page_element_image
                                $dir_path = 'uploads/page/';
                                $image_file = $v_page_element_image[$key][$key2][$i];
                                $format_image_name = $key2 . '-' . $i . '-imagetextbutton' . '-' . Helper::unique_string();
                                $image = Helper::upload_image($dir_path, $image_file, true, $format_image_name);
                                if ($image['status'] != 'true') {
                                    return back()
                                        ->withInput()
                                        ->with('error', lang('Oops, failed to upload image #item - #name. Please try again or try upload another one.', $this->translation, ['#item' => $value2, '#name' => $obj_3->v_page_element_image_title]));
                                }
                                $obj_3->v_page_element_image = $dir_path . $image['data'];
                            } elseif (isset($v_page_element_imagex[$key][$key2][$i])) {
                                // USE EXISTING IMAGE
                                $obj_3->v_page_element_image = $v_page_element_imagex[$key][$key2][$i];
                            } else {
                                $obj_3->v_page_element_image = null;
                            }

                            // SAVE OBJECT 3 IN ARRAY 3
                            $array3[] = $obj_3;
                        }
                        break;

                    case 'video':
                        /**
                         * ARRAY 2 - CONTENTS
                         * 
                         * array[]
                         * - array[]
                         * - - item
                         */
                        $v_page_element_video_type = $request->v_page_element_video_type; // "video" or "video + text"
                        $v_page_element_video_title = $request->v_page_element_video_title;
                        $v_page_element_video = $request->v_page_element_video;
                        $v_page_element_alignment = $request->v_page_element_alignment;
                        $v_page_element_text = $request->v_page_element_text;
                        $v_page_element_button_link_type = $request->v_page_element_button_link_type;
                        $v_page_element_button_link_internal = $request->v_page_element_button_link_internal;
                        $v_page_element_button_link_external = $request->v_page_element_button_link_external;
                        $v_page_element_button_link_target = $request->v_page_element_button_link_target;
                        $v_page_element_button_label = $request->v_page_element_button_label;
                        $v_page_element_button_style = $request->v_page_element_button_style;

                        // PROCESSING ARRAY 2 DATA
                        $obj_2->v_page_element_video_type = $v_page_element_video_type[$key][$key2];
                        $obj_2->v_page_element_video_title = $v_page_element_video_title[$key][$key2];
                        $obj_2->v_page_element_video = $v_page_element_video[$key][$key2];
                        $obj_2->v_page_element_alignment = $v_page_element_alignment[$key][$key2];
                        $obj_2->v_page_element_text = $v_page_element_text[$key][$key2];
                        $obj_2->v_page_element_button_link_type = $v_page_element_button_link_type[$key][$key2];
                        $obj_2->v_page_element_button_link_internal = $v_page_element_button_link_internal[$key][$key2];
                        $obj_2->v_page_element_button_link_external = $v_page_element_button_link_external[$key][$key2];
                        $obj_2->v_page_element_button_link_target = $v_page_element_button_link_target[$key][$key2];
                        $obj_2->v_page_element_button_label = $v_page_element_button_label[$key][$key2];
                        $obj_2->v_page_element_button_style = $v_page_element_button_style[$key][$key2];
                        break;

                    case 'button':
                        /**
                         * ARRAY 2 - CONTENTS
                         * 
                         * array[]
                         * - array[]
                         * - - item
                         */
                        $v_page_element_alignment = $request->v_page_element_alignment;

                        /**
                         * ARRAY 3 - ITEMS
                         * 
                         * array[]
                         * - array[]
                         * - - array[]
                         * - - - item
                         */
                        $v_page_element_button_label = $request->v_page_element_button_label;
                        $v_page_element_button_style = $request->v_page_element_button_style;
                        $v_page_element_button_link_type = $request->v_page_element_button_link_type;
                        $v_page_element_button_link_internal = $request->v_page_element_button_link_internal;
                        $v_page_element_button_link_external = $request->v_page_element_button_link_external;
                        $v_page_element_button_link_target = $request->v_page_element_button_link_target;
                        $v_page_element_status_item = $request->v_page_element_status_item;

                        // PROCESSING ARRAY 2 DATA
                        $obj_2->v_page_element_alignment = $v_page_element_alignment[$key][$key2];

                        // SUPPORT MULTIPLE ITEM
                        foreach ($v_page_element_status_item[$key][$key2] as $key3 => $value3) {
                            // SAVE ARRAY 3 DATA USING OBJECT
                            $obj_3 = new \stdClass();
                            $obj_3->v_page_element_button_label = $v_page_element_button_label[$key][$key2][$key3];
                            $obj_3->v_page_element_button_style = $v_page_element_button_style[$key][$key2][$key3];
                            $obj_3->v_page_element_button_link_type = $v_page_element_button_link_type[$key][$key2][$key3];
                            $obj_3->v_page_element_button_link_internal = $v_page_element_button_link_internal[$key][$key2][$key3];
                            $obj_3->v_page_element_button_link_external = $v_page_element_button_link_external[$key][$key2][$key3];
                            $obj_3->v_page_element_button_link_target = $v_page_element_button_link_target[$key][$key2][$key3];
                            $obj_3->v_page_element_status_item = $value3;

                            // SAVE OBJECT 3 IN ARRAY 3
                            $array3[$key3] = $obj_3;
                        }
                        break;

                    default:
                        return back()
                            ->withInput()
                            ->with('error', lang('Oops, there is an unknown content element type', $this->translation));
                        break;
                }

                // SAVE ARRAY 3 TO OBJECT 2
                $obj_2->items = $array3;

                // SAVE OBJECT 2 TO ARRAY 2
                $array2[$key2] = $obj_2;
            }

            // SAVE ARRAY 2 TO OBJECT CONTENT
            $obj_content->contents = $array2;

            // SAVE ALL DATA
            $full_content[$key] = $obj_content;
        }

        return $full_content;
    }

    private function insert_content($request)
    {
        // PROCESSING IMAGE - CONTENT ELEMENT
        $types = $request->v_element_type;
        $sections = $request->v_element_section;
        $content_text = $request->v_element_content_text;
        $positions = $request->v_text_position;
        $content_image = $request->v_element_content_image;
        $content_video = $request->v_element_content_video;
        $full_content = [];
        
        foreach ($types as $key => $value) {
            // SAVE PER ELEMENT TYPE USING OBJECT
            $obj_content = new \stdClass();
            $obj_content->type = $types[$key];
            $obj_content->section = $sections[$key];
            
            // VALIDATE CONTENT BASED ON TYPE
            switch ($obj_content->type) {
                case 'text':
                    $obj_content->text = $content_text[$key];
                    break;

                case 'image':
                    // PROCESSING IMAGE
                    $image = $content_image[$key];
                    $destinationPath_content = public_path('uploads/page/');
                    $extension  = strtolower($image->getClientOriginalExtension());
                    // FORMAT IMAGE NAME : XXXX-content.ext
                    $image_name = $key . '-content.' . $extension;
                    // UPLOADING...
                    if (!$image->move($destinationPath_content, $image_name)) {
                        return back()->withInput()->with('error', 'Ups, gagal mengupload gambar salah satu konten. Mohon coba lagi atau upload gambar lain.');
                    }
                    // GET IMAGE DATA FOR UPLOAD TO API
                    // $obj_content->image = base64_encode(file_get_contents($destinationPath_content . $image_name));
                    $obj_content->image = $image_name;
                    $obj_content->image_ext = $extension;
                    break;

                case 'image & text':
                    // PROCESSING IMAGE
                    $image = $content_image[$key];
                    $destinationPath_content = public_path('uploads/page/');
                    $extension  = strtolower($image->getClientOriginalExtension());
                    // FORMAT IMAGE NAME : XXXX-content.ext
                    $image_name = $key . '-content.' . $extension;
                    // UPLOADING...
                    if (!$image->move($destinationPath_content, $image_name)) {
                        return back()->withInput()->with('error', 'Ups, gagal mengupload gambar salah satu konten. Mohon coba lagi atau upload gambar lain.');
                    }
                    // GET IMAGE DATA FOR UPLOAD TO API
                    // $obj_content->image = base64_encode(file_get_contents($destinationPath_content . $image_name));
                    $obj_content->image = $image_name;
                    $obj_content->image_ext = $extension;
                    $obj_content->text = $content_text[$key];
                    $obj_content->text_position = $positions[$key];
                    break;

                case 'video':
                    $obj_content->video = $content_video[$key];
                    break;

                case 'video & text':
                    $obj_content->video = $content_video[$key];
                    $obj_content->text = $content_text[$key];
                    $obj_content->text_position = $positions[$key];
                    break;

                case 'plain text':
                    $obj_content->text = $content_text[$key];
                    break;

                default:
                    return back()->withInput()->with('error', 'Ups, ada tipe konten yang tidak dikenali: ' . $obj_content->type);
                    break;
            }
            $full_content[$key] = $obj_content;
        }

        return $full_content;
    }

    private function update_content_v1($request)
    {
        // PROCESSING CONTENT ELEMENT
        /**
         * ARRAY 1 - SECTIONS
         * 
         * array[]
         * - item
         */
        $v_page_section = $request->v_page_section;
        $v_page_section_style = $request->v_page_section_style;

        /**
         * ARRAY 2 - CONTENTS
         * 
         * array[]
         * - array[]
         * - - item
         */
        $v_page_element_type = $request->v_page_element_type;
        $v_page_element_section = $request->v_page_element_section;
        $v_page_element_status = $request->v_page_element_status;

        $full_content = [];
        foreach ($v_page_section as $key => $value) {
            // SAVE PER ELEMENT USING OBJECT
            $obj_content = new \stdClass();

            // PROCESSING ARRAY 1 DATA
            $obj_content->v_page_section = $value;
            $obj_content->v_page_section_style = $v_page_section_style[$key];

            // PROCESSING ARRAY 2 DATA
            // CREATE ARRAY VARIABLE TO STORE ARRAY 2 DATA
            $array2 = [];

            // LOOPING ARRAY 2 DATA PER ELEMENT TYPE FOR PROCESSING ARRAY 3 DATA
            foreach ($v_page_element_type[$key] as $key2 => $value2) {
                // SAVE ARRAY 2 DATA USING OBJECT
                $obj_2 = new \stdClass();
                // SAVE CONTENT ELEMENT SECTION NAME
                $obj_2->v_page_element_section = $v_page_element_section[$key][$key2];
                // SAVE CONTENT ELEMENT STATUS
                $obj_2->v_page_element_status = $v_page_element_status[$key][$key2];
                // SAVE CONTENT ELEMENT TYPE
                $obj_2->v_page_element_type = $value2;

                // CREATE ARRAY VARIABLE TO STORE ARRAY 3 DATA
                $array3 = [];

                // PROCESSING ARRAY 3 DATA
                switch ($value2) {
                    case 'masthead':
                        /**
                         * ARRAY 3 - ITEMS
                         * 
                         * array[]
                         * - array[]
                         * - - array[]
                         * - - - item
                         */
                        $v_page_element_image_title = $request->v_page_element_image_title;
                        $v_page_element_image = $request->v_page_element_image;
                        if ($request->v_page_element_imagex) {
                            $v_page_element_imagex = $request->v_page_element_imagex;
                        }
                        $v_page_element_image_mobile = $request->v_page_element_image_mobile;
                        if ($request->v_page_element_image_mobilex) {
                            $v_page_element_image_mobilex = $request->v_page_element_image_mobilex;
                        }
                        $v_page_element_title = $request->v_page_element_title;
                        $v_page_element_title_style = $request->v_page_element_title_style;
                        $v_page_element_subtitle = $request->v_page_element_subtitle;
                        $v_page_element_subtitle_style = $request->v_page_element_subtitle_style;
                        $v_page_element_alignment = $request->v_page_element_alignment;
                        $v_page_element_status_item = $request->v_page_element_status_item;

                        /**
                         * ARRAY 4 - SUB-ITEMS
                         * 
                         * array[]
                         * - array[]
                         * - - array[]
                         * - - - array[]
                         * - - - - item
                         */
                        $v_page_element_button_link_type = $request->v_page_element_button_link_type;
                        $v_page_element_button_link_internal = $request->v_page_element_button_link_internal;
                        $v_page_element_button_link_external = $request->v_page_element_button_link_external;
                        $v_page_element_button_link_target = $request->v_page_element_button_link_target;
                        $v_page_element_button_label = $request->v_page_element_button_label;
                        $v_page_element_button_style = $request->v_page_element_button_style;

                        // SUPPORT MULTIPLE ITEM
                        foreach ($v_page_element_status_item[$key][$key2] as $key3 => $value3) {
                            // SAVE ARRAY 3 DATA USING OBJECT
                            $obj_3 = new \stdClass();
                            $obj_3->v_page_element_image_title = $v_page_element_image_title[$key][$key2][$key3];
                            $obj_3->v_page_element_title = $v_page_element_title[$key][$key2][$key3];
                            $obj_3->v_page_element_title_style = $v_page_element_title_style[$key][$key2][$key3];
                            $obj_3->v_page_element_subtitle = $v_page_element_subtitle[$key][$key2][$key3];
                            $obj_3->v_page_element_subtitle_style = $v_page_element_subtitle_style[$key][$key2][$key3];
                            $obj_3->v_page_element_alignment = $v_page_element_alignment[$key][$key2][$key3];
                            $obj_3->v_page_element_status_item = $value3;

                            // IF UPLOAD NEW IMAGE
                            if (isset($v_page_element_image[$key][$key2][$key3])) {
                                // PROCESSING IMAGE - v_page_element_image
                                $dir_path = 'uploads/page/';
                                $image_file = $v_page_element_image[$key][$key2][$key3];
                                $format_image_name = $key3 . '-' . $value2 . '-' . Helper::unique_string();
                                $image = Helper::upload_image($dir_path, $image_file, true, $format_image_name);
                                if ($image['status'] != 'true') {
                                    return back()
                                        ->withInput()
                                        ->with('error', lang('Oops, failed to upload image #item - #name. Please try again or try upload another one.', $this->translation, ['#item' => $value2, '#name' => $obj_3->v_page_element_image_title]));
                                }
                                $obj_3->v_page_element_image = $dir_path . $image['data'];
                            } elseif (isset($v_page_element_imagex[$key][$key2][$key3])) {
                                // USE EXISTING IMAGE
                                $obj_3->v_page_element_image = $v_page_element_imagex[$key][$key2][$key3];
                            } else {
                                //  ERROR: MUST UPLOAD IMAGE FOR NEW ELEMENT
                                return back()
                                    ->withInput()
                                    ->with('error', lang('Oops, you must upload image #item for #name. Please try again or try upload another one.', $this->translation, ['#item' => $value2, '#name' => $obj_3->v_page_element_image_title]));
                            }

                            // IF UPLOAD NEW IMAGE
                            if (isset($v_page_element_image_mobile[$key][$key2][$key3])) {
                                // PROCESSING IMAGE - v_page_element_image_mobile
                                $dir_path = 'uploads/page/';
                                $image_file = $v_page_element_image_mobile[$key][$key2][$key3];
                                $format_image_name = $key3 . '-' . $value2 . '_mobile' . '-' . Helper::unique_string();
                                $image = Helper::upload_image($dir_path, $image_file, true, $format_image_name);
                                if ($image['status'] != 'true') {
                                    return back()
                                        ->withInput()
                                        ->with('error', lang('Oops, failed to upload image mobile #item - #name. Please try again or try upload another one.', $this->translation, ['#item' => $value2, '#name' => $obj_3->v_page_element_image_title]));
                                }
                                $obj_3->v_page_element_image_mobile = $dir_path . $image['data'];
                            } elseif (isset($v_page_element_image_mobilex[$key][$key2][$key3])) {
                                // USE EXISTING IMAGE
                                $obj_3->v_page_element_image_mobile = $v_page_element_image_mobilex[$key][$key2][$key3];
                            } else {
                                //  ERROR: MUST UPLOAD IMAGE FOR NEW ELEMENT
                                return back()
                                    ->withInput()
                                    ->with('error', lang('Oops, you must upload image mobile #item for #name. Please try again or try upload another one.', $this->translation, ['#item' => $value2, '#name' => $obj_3->v_page_element_image_title]));
                            }

                            // CREATE ARRAY VARIABLE TO STORE ARRAY 4 DATA
                            $array4 = [];

                            // PROCESSING ARRAY 4 DATA
                            foreach ($v_page_element_button_link_type[$key][$key2][$key3] as $key4 => $value4) {
                                // SAVE ARRAY 4 DATA USING OBJECT
                                $obj_4 = new \stdClass();
                                $obj_4->v_page_element_button_link_type = $value4;
                                $obj_4->v_page_element_button_link_internal = $v_page_element_button_link_internal[$key][$key2][$key3][$key4];
                                $obj_4->v_page_element_button_link_external = $v_page_element_button_link_external[$key][$key2][$key3][$key4];
                                $obj_4->v_page_element_button_link_target = $v_page_element_button_link_target[$key][$key2][$key3][$key4];
                                $obj_4->v_page_element_button_label = $v_page_element_button_label[$key][$key2][$key3][$key4];
                                $obj_4->v_page_element_button_style = $v_page_element_button_style[$key][$key2][$key3][$key4];

                                // SAVE OBJECT 4 IN ARRAY 4
                                $array4[$key4] = $obj_4;
                            }

                            // SAVE ARRAY 4 TO OBJECT 3
                            $obj_3->sub_items = $array4;

                            // SAVE OBJECT 3 IN ARRAY 3
                            $array3[$key3] = $obj_3;
                        }
                        break;

                    case 'text':
                        /**
                         * ARRAY 2 - CONTENTS
                         * 
                         * array[]
                         * - array[]
                         * - - item
                         */
                        $v_page_element_width = $request->v_page_element_width;
                        $v_page_element_alignment = $request->v_page_element_alignment;
                        $v_page_element_total_item = $request->v_page_element_total_item;

                        /**
                         * ARRAY 3 - ITEMS
                         * 
                         * array[]
                         * - array[]
                         * - - array[]
                         * - - - item
                         */
                        $v_page_element_text = $request->v_page_element_text;

                        // PROCESSING ARRAY 2 DATA
                        $obj_2->v_page_element_width = $v_page_element_width[$key][$key2];
                        $obj_2->v_page_element_alignment = $v_page_element_alignment[$key][$key2];
                        $obj_2->v_page_element_total_item = $v_page_element_total_item[$key][$key2];

                        // SUPPORT 3 ITEMS - PROCESSING ARRAY 3 DATA
                        for ($i = 0; $i < 3; $i++) {
                            $array3[] = $v_page_element_text[$key][$key2][$i];
                        }
                        break;

                    case 'image':
                        /**
                         * ARRAY 2 - CONTENTS
                         * 
                         * array[]
                         * - array[]
                         * - - item
                         */
                        $v_page_element_image_type = $request->v_page_element_image_type;

                        /**
                         * ARRAY 3 - ITEMS
                         * 
                         * array[]
                         * - array[]
                         * - - array[]
                         * - - - item
                         */
                        $v_page_element_image_title = $request->v_page_element_image_title;
                        $v_page_element_link_type = $request->v_page_element_link_type;
                        $v_page_element_link_internal = $request->v_page_element_link_internal;
                        $v_page_element_link_external = $request->v_page_element_link_external;
                        $v_page_element_link_target = $request->v_page_element_link_target;
                        $v_page_element_status_item = $request->v_page_element_status_item;
                        $v_page_element_image = $request->v_page_element_image;
                        if ($request->v_page_element_imagex) {
                            $v_page_element_imagex = $request->v_page_element_imagex;
                        }

                        // PROCESSING ARRAY 2 DATA
                        $obj_2->v_page_element_image_type = $v_page_element_image_type[$key][$key2];

                        // SUPPORT MULTIPLE ITEM
                        foreach ($v_page_element_status_item[$key][$key2] as $key3 => $value3) {
                            // SAVE ARRAY 3 DATA USING OBJECT
                            $obj_3 = new \stdClass();
                            $obj_3->v_page_element_image_title = $v_page_element_image_title[$key][$key2][$key3];
                            $obj_3->v_page_element_link_type = $v_page_element_link_type[$key][$key2][$key3];
                            $obj_3->v_page_element_link_internal = $v_page_element_link_internal[$key][$key2][$key3];
                            $obj_3->v_page_element_link_external = $v_page_element_link_external[$key][$key2][$key3];
                            $obj_3->v_page_element_link_target = $v_page_element_link_target[$key][$key2][$key3];
                            $obj_3->v_page_element_status_item = $value3;

                            // IF UPLOAD NEW IMAGE
                            if (isset($v_page_element_image[$key][$key2][$key3])) {
                                // PROCESSING IMAGE - v_page_element_image
                                $dir_path = 'uploads/page/';
                                $image_file = $v_page_element_image[$key][$key2][$key3];
                                $format_image_name = $key3 . '-' . $value2 . '-' . Helper::unique_string();
                                $image = Helper::upload_image($dir_path, $image_file, true, $format_image_name);
                                if ($image['status'] != 'true') {
                                    return back()
                                        ->withInput()
                                        ->with('error', lang('Oops, failed to upload image #item - #name. Please try again or try upload another one.', $this->translation, ['#item' => $value2, '#name' => $obj_3->v_page_element_image_title]));
                                }
                                $obj_3->v_page_element_image = $dir_path . $image['data'];
                            } elseif (isset($v_page_element_imagex[$key][$key2][$key3])) {
                                // USE EXISTING IMAGE
                                $obj_3->v_page_element_image = $v_page_element_imagex[$key][$key2][$key3];
                            } else {
                                //  ERROR: MUST UPLOAD IMAGE FOR NEW ELEMENT
                                return back()
                                    ->withInput()
                                    ->with('error', lang('Oops, you must upload image #item for #name. Please try again or try upload another one.', $this->translation, ['#item' => $value2, '#name' => $obj_3->v_page_element_image_title]));
                            }

                            // SAVE OBJECT 3 IN ARRAY 3
                            $array3[$key3] = $obj_3;
                        }

                        break;

                    case 'image + text + button':
                        /**
                         * ARRAY 2 - CONTENTS
                         * 
                         * array[]
                         * - array[]
                         * - - item
                         */
                        $v_page_element_alignment = $request->v_page_element_alignment;
                        $v_page_element_total_item = $request->v_page_element_total_item;

                        /**
                         * ARRAY 3 - ITEMS
                         * 
                         * array[]
                         * - array[]
                         * - - array[]
                         * - - - item
                         */
                        $v_page_element_image_title = $request->v_page_element_image_title;
                        $v_page_element_text = $request->v_page_element_text;
                        $v_page_element_button_link_type = $request->v_page_element_button_link_type;
                        $v_page_element_button_link_internal = $request->v_page_element_button_link_internal;
                        $v_page_element_button_link_external = $request->v_page_element_button_link_external;
                        $v_page_element_button_link_target = $request->v_page_element_button_link_target;
                        $v_page_element_button_label = $request->v_page_element_button_label;
                        $v_page_element_button_style = $request->v_page_element_button_style;
                        $v_page_element_image = $request->v_page_element_image;
                        if ($request->v_page_element_imagex) {
                            $v_page_element_imagex = $request->v_page_element_imagex;
                        }

                        // PROCESSING ARRAY 2 DATA
                        $obj_2->v_page_element_alignment = $v_page_element_alignment[$key][$key2];
                        $obj_2->v_page_element_total_item = $v_page_element_total_item[$key][$key2];

                        // SUPPORT 3 ITEMS - PROCESSING ARRAY 3 DATA
                        for ($i = 0; $i < 3; $i++) {
                            // SAVE ARRAY 3 DATA USING OBJECT
                            $obj_3 = new \stdClass();
                            $obj_3->v_page_element_image_title = $v_page_element_image_title[$key][$key2][$i];
                            $obj_3->v_page_element_text = $v_page_element_text[$key][$key2][$i];
                            $obj_3->v_page_element_button_link_type = $v_page_element_button_link_type[$key][$key2][$i];
                            $obj_3->v_page_element_button_link_internal = $v_page_element_button_link_internal[$key][$key2][$i];
                            $obj_3->v_page_element_button_link_external = $v_page_element_button_link_external[$key][$key2][$i];
                            $obj_3->v_page_element_button_link_target = $v_page_element_button_link_target[$key][$key2][$i];
                            $obj_3->v_page_element_button_label = $v_page_element_button_label[$key][$key2][$i];
                            $obj_3->v_page_element_button_style = $v_page_element_button_style[$key][$key2][$i];

                            // IF UPLOAD NEW IMAGE
                            if (isset($v_page_element_image[$key][$key2][$i])) {
                                // PROCESSING IMAGE - v_page_element_image
                                $dir_path = 'uploads/page/';
                                $image_file = $v_page_element_image[$key][$key2][$i];
                                $format_image_name = $key2 . '-' . $i . '-imagetextbutton' . '-' . Helper::unique_string();
                                $image = Helper::upload_image($dir_path, $image_file, true, $format_image_name);
                                if ($image['status'] != 'true') {
                                    return back()
                                        ->withInput()
                                        ->with('error', lang('Oops, failed to upload image #item - #name. Please try again or try upload another one.', $this->translation, ['#item' => $value2, '#name' => $obj_3->v_page_element_image_title]));
                                }
                                $obj_3->v_page_element_image = $dir_path . $image['data'];
                            } elseif (isset($v_page_element_imagex[$key][$key2][$i])) {
                                // USE EXISTING IMAGE
                                $obj_3->v_page_element_image = $v_page_element_imagex[$key][$key2][$i];
                            } else {
                                $obj_3->v_page_element_image = null;
                            }

                            // SAVE OBJECT 3 IN ARRAY 3
                            $array3[] = $obj_3;
                        }
                        break;

                    case 'video':
                        /**
                         * ARRAY 2 - CONTENTS
                         * 
                         * array[]
                         * - array[]
                         * - - item
                         */
                        $v_page_element_video_type = $request->v_page_element_video_type; // "video" or "video + text"
                        $v_page_element_video_title = $request->v_page_element_video_title;
                        $v_page_element_video = $request->v_page_element_video;
                        $v_page_element_alignment = $request->v_page_element_vidv_page_element_alignmenteo;
                        $v_page_element_text = $request->v_page_element_text;
                        $v_page_element_button_link_type = $request->v_page_element_button_link_type;
                        $v_page_element_button_link_internal = $request->v_page_element_button_link_internal;
                        $v_page_element_button_link_external = $request->v_page_element_button_link_external;
                        $v_page_element_button_link_target = $request->v_page_element_button_link_target;
                        $v_page_element_button_label = $request->v_page_element_button_label;
                        $v_page_element_button_style = $request->v_page_element_button_style;

                        // PROCESSING ARRAY 2 DATA
                        $obj_2->v_page_element_video_type = $v_page_element_video_type[$key][$key2];
                        $obj_2->v_page_element_video_title = $v_page_element_video_title[$key][$key2];
                        $obj_2->v_page_element_video = $v_page_element_video[$key][$key2];
                        $obj_2->v_page_element_alignment = $v_page_element_alignment[$key][$key2];
                        $obj_2->v_page_element_text = $v_page_element_text[$key][$key2];
                        $obj_2->v_page_element_button_link_type = $v_page_element_button_link_type[$key][$key2];
                        $obj_2->v_page_element_button_link_internal = $v_page_element_button_link_internal[$key][$key2];
                        $obj_2->v_page_element_button_link_external = $v_page_element_button_link_external[$key][$key2];
                        $obj_2->v_page_element_button_link_target = $v_page_element_button_link_target[$key][$key2];
                        $obj_2->v_page_element_button_label = $v_page_element_button_label[$key][$key2];
                        $obj_2->v_page_element_button_style = $v_page_element_button_style[$key][$key2];
                        break;

                    case 'button':
                        /**
                         * ARRAY 2 - CONTENTS
                         * 
                         * array[]
                         * - array[]
                         * - - item
                         */
                        $v_page_element_alignment = $request->v_page_element_alignment;

                        /**
                         * ARRAY 3 - ITEMS
                         * 
                         * array[]
                         * - array[]
                         * - - array[]
                         * - - - item
                         */
                        $v_page_element_button_label = $request->v_page_element_button_label;
                        $v_page_element_button_style = $request->v_page_element_button_style;
                        $v_page_element_button_link_type = $request->v_page_element_button_link_type;
                        $v_page_element_button_link_internal = $request->v_page_element_button_link_internal;
                        $v_page_element_button_link_external = $request->v_page_element_button_link_external;
                        $v_page_element_button_link_target = $request->v_page_element_button_link_target;
                        $v_page_element_status_item = $request->v_page_element_status_item;

                        // PROCESSING ARRAY 2 DATA
                        $obj_2->v_page_element_alignment = $v_page_element_alignment[$key][$key2];

                        // SUPPORT MULTIPLE ITEM
                        foreach ($v_page_element_status_item[$key][$key2] as $key3 => $value3) {
                            // SAVE ARRAY 3 DATA USING OBJECT
                            $obj_3 = new \stdClass();
                            $obj_3->v_page_element_button_label = $v_page_element_button_label[$key][$key2][$key3];
                            $obj_3->v_page_element_button_style = $v_page_element_button_style[$key][$key2][$key3];
                            $obj_3->v_page_element_button_link_type = $v_page_element_button_link_type[$key][$key2][$key3];
                            $obj_3->v_page_element_button_link_internal = $v_page_element_button_link_internal[$key][$key2][$key3];
                            $obj_3->v_page_element_button_link_external = $v_page_element_button_link_external[$key][$key2][$key3];
                            $obj_3->v_page_element_button_link_target = $v_page_element_button_link_target[$key][$key2][$key3];
                            $obj_3->v_page_element_status_item = $value3;

                            // SAVE OBJECT 3 IN ARRAY 3
                            $array3[$key3] = $obj_3;
                        }
                        break;

                    default:
                        return back()
                            ->withInput()
                            ->with('error', lang('Oops, there is an unknown content element type', $this->translation));
                        break;
                }

                // SAVE ARRAY 3 TO OBJECT 2
                $obj_2->items = $array3;

                // SAVE OBJECT 2 TO ARRAY 2
                $array2[$key2] = $obj_2;
            }

            // SAVE ARRAY 2 TO OBJECT CONTENT
            $obj_content->contents = $array2;

            // SAVE ALL DATA
            $full_content[$key] = $obj_content;
        }

        return $full_content;
    }

    private function update_content($request, $data)
    {
        // PROCESSING IMAGE - CONTENT ELEMENT
        $types = $request->v_element_type;
        $sections = $request->v_element_section;
        $content_text = $request->v_element_content_text;
        $positions = $request->v_text_position;
        $content_image = $request->v_element_content_image;
        $content_video = $request->v_element_content_video;
        $full_content = [];

        // GET EXISTING DATA
        $exist_content = json_decode($data->details, true);
        foreach ($types as $key => $value) {
            // SAVE PER ELEMENT TYPE USING OBJECT
            $obj_content = new \stdClass();
            $obj_content->type = $types[$key];
            $obj_content->section = $sections[$key];
        
            // VALIDATE CONTENT BASED ON TYPE
            switch ($obj_content->type) {
                case 'text':
                    $obj_content->text = $content_text[$key];
                    break;

                case 'image':
                    // IF UPLOAD NEW IMAGE
                    if (isset($content_image[$key])) {
                        // PROCESSING IMAGE
                        $image = $content_image[$key];
                        $destinationPath_content = public_path('uploads/page/');
                        $extension  = strtolower($image->getClientOriginalExtension());
                        // FORMAT IMAGE NAME : XXXX-content.ext
                        $image_name = $key . '-content.' . $extension;
                        // UPLOADING...
                        if (!$image->move($destinationPath_content, $image_name)) {
                            return back()->withInput()->with('error', 'Ups, gagal mengupload gambar salah satu konten. Mohon coba lagi atau upload gambar lain.');
                        }
                        // GET IMAGE DATA FOR UPLOAD TO API
                        // $obj_content->image = base64_encode(file_get_contents($destinationPath_content . $image_name));
                        $obj_content->image = $image_name;
                        $obj_content->image_ext = $extension;
                    } else {
                        // GET OLD DATA
                        if (isset($exist_content[$key]['image'])) {
                            $obj_content->image = $exist_content[$key]['image'];
                        } else {
                            // ERROR - IMAGE IS REQUIRED FOR THIS ELEMENT
                            return back()->withInput()->with('error', 'Ups, gambar untuk konten Section "' . $obj_content->section . '" wajib diisi. Mohon upload suatu gambar kemudian submit lagi.');
                        }
                    }
                    break;

                case 'image & text':
                    // IF UPLOAD NEW IMAGE
                    if (isset($content_image[$key])) {
                        // PROCESSING IMAGE
                        $image = $content_image[$key];
                        $destinationPath_content = public_path('uploads/page/');
                        $extension  = strtolower($image->getClientOriginalExtension());
                        // FORMAT IMAGE NAME : XXXX-content.ext
                        $image_name = $key . '-content.' . $extension;
                        // UPLOADING...
                        if (!$image->move($destinationPath_content, $image_name)) {
                            return back()->withInput()->with('error', 'Ups, gagal mengupload gambar salah satu konten. Mohon coba lagi atau upload gambar lain.');
                        }
                        // GET IMAGE DATA FOR UPLOAD TO API
                        // $obj_content->image = base64_encode(file_get_contents($destinationPath_content . $image_name));
                        $obj_content->image = $image_name;
                        $obj_content->image_ext = $extension;
                    } else {
                        // GET OLD DATA
                        if (isset($exist_content[$key]['image'])) {
                            $obj_content->image = $exist_content[$key]['image'];
                        } else {
                            // ERROR - IMAGE IS REQUIRED FOR THIS ELEMENT
                            return back()->withInput()->with('error', 'Ups, gambar untuk konten Section "' . $obj_content->section . '" wajib diisi. Mohon upload suatu gambar kemudian submit lagi.');
                        }
                    }
                    $obj_content->text = $content_text[$key];
                    $obj_content->text_position = $positions[$key];
                    break;

                case 'video':
                    $obj_content->video = $content_video[$key];
                    break;

                case 'video & text':
                    $obj_content->video = $content_video[$key];
                    $obj_content->text = $content_text[$key];
                    $obj_content->text_position = $positions[$key];
                    break;

                case 'plain text':
                    $obj_content->text = $content_text[$key];
                    break;

                default:
                    return back()->withInput()->with('error', 'Ups, ada tipe konten yang tidak dikenali: ' . $obj_content->type);
                    break;
            }
            $full_content[$key] = $obj_content;
        }

        return $full_content;
    }

    /**
     * TOP
     */

    public function top_view()
    {
        // authorizing...
        $authorize = Helper::authorizing($this->module, 'View Details');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        $data = Home_dynamic_content::find(2);

        return view('admin.home_dynamic_content.form_top', compact('data'));
    }

    public function top_update(Request $request)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'Edit');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        // SET THIS OBJECT/ITEM NAME BASED ON TRANSLATION
        $this->item = ucwords(lang($this->item, $this->translations));

        // LARAVEL VALIDATION
        $validation = [
            'v_page_section' => 'required'
        ];
        $message = [
            'required' => ':attribute ' . lang('is required', $this->translation)
        ];
        $names = [
            'v_page_section' => ucwords(lang('content element', $this->translation))
        ];
        $this->validate($request, $validation, $message, $names);

        // GET EXISTING DATA
        $data = Home_dynamic_content::find(2);
        $msg = 'Successfully updated #item';
        if (!$data) {
            $data = new Home_dynamic_content();
            $msg = 'Successfully created #item';
            $full_content = $this->insert_content($request);
        } else {
            $full_content = $this->update_content($request);
        }

        // SAVE THE DATA
        $data->content = json_encode($full_content);
        $data->status = (int) $request->status;

        if ($data->save()) {
            // SUCCESS
            return redirect()
                ->route('admin.dynamic_content_top')
                ->with('success', lang($msg, $this->translation, ['#item' => $this->item]));
        }

        // FAILED
        return back()
            ->withInput()
            ->with('error', lang('Oops, failed to update #item . Please try again.', $this->translation, ['#item' => $this->item]));
    }
}