<?php

namespace App\Http\Controllers\Admin\Core;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;

// Libraries
use App\Libraries\Helper;

// Models
use App\Models\page;

class PageController extends Controller
{
    // SET THIS MODULE
    private $module = 'Page';
    private $module_id = 15;

    // SET THIS OBJECT/ITEM NAME
    private $item = 'page';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'View List');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        return view('admin.core.page.list');
    }

    /**
     * Get a listing of the resource using DataTables.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_data(Datatables $datatables, Request $request)
    {
        $query = page::whereNotNull('id');

        return $datatables->eloquent($query)
            ->addColumn('action', function ($data) {
                $object_id = $data->id;
                if (env('CRYPTOGRAPHY_MODE', false)) {
                    $object_id = Helper::generate_token($data->id);
                }

                $wording_edit = ucwords(lang('edit', $this->translations));
                $html = '<a href="' . route('admin.page.edit', $object_id) . '" class="btn btn-xs btn-primary" title="' . $wording_edit . '"><i class="fa fa-pencil"></i>&nbsp; ' . $wording_edit . '</a>';

                $wording_view = ucwords(lang('preview page', $this->translations));
                $html .= '<a href="' . url($data->slug) . '?preview=' . date('YmdHis') . '" target="_blank" class="btn btn-xs btn-warning" title="' . $wording_view . '"><i class="fa fa-external-link"></i>&nbsp; ' . $wording_view . '</a>';

                $wording_delete = ucwords(lang('delete', $this->translations));
                $html .= '<form action="' . route('admin.page.delete') . '" method="POST" onsubmit="return confirm(\'' . lang('Are you sure to delete this #item?', $this->translations, ['#item' => $this->item]) . '\');" style="display: inline"> ' . csrf_field() . ' <input type="hidden" name="id" value="' . $object_id . '">
                <button type="submit" class="btn btn-xs btn-danger" title="' . $wording_delete . '"><i class="fa fa-trash"></i>&nbsp; ' . $wording_delete . '</button></form>';

                return $html;
            })
            ->addColumn('status', function ($data) {
                if ($data->status != 1) {
                    return '<span class="label label-danger"><i>' . ucwords(lang('draft', $this->translations)) . '</i></span>';
                }
                return '<span class="label label-success">' . ucwords(lang('published', $this->translations)) . '</span>';
            })
            ->editColumn('updated_at', function ($data) {
                return Helper::time_ago(strtotime($data->updated_at), lang('ago', $this->translations), Helper::get_periods($this->translations));
            })
            ->editColumn('created_at', function ($data) {
                return Helper::locale_timestamp($data->created_at);
            })
            ->rawColumns(['action', 'status'])
            ->toJson();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'Add New');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        return view('admin.core.page.form');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'Add New');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        // SET THIS OBJECT/ITEM NAME BASED ON TRANSLATION
        $this->item = ucwords(lang($this->item, $this->translations));

        // LARAVEL VALIDATION
        $validation = [
            'title' => 'required',
            'v_page_section' => 'required',
        ];
        $message = [
            'required' => ':attribute ' . lang('should not be empty', $this->translations)
        ];
        $names = [
            'title' => ucwords(lang('title', $this->translations)),
            'v_page_section' => ucwords(lang('content', $this->translations)),
        ];
        $this->validate($request, $validation, $message, $names);

        DB::beginTransaction();
        try {
            // DB PROCESS BELOW

            // get table name
            $table_page = (new page())->getTable();

            // CREATE NEW DATA
            $data = new page();

            // HELPER VALIDATION FOR PREVENT SQL INJECTION & XSS ATTACK
            $title = Helper::validate_input_text($request->title);
            if (!$title) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => $names['title']]));
            }
            $data->title = $title;

            if ($request->slug) {
                $slug = Helper::generate_slug($request->slug);
            } else {
                $slug = Helper::generate_slug($title);
            }
            // MAKE SURE SLUG IS UNIQUE
            $data->slug = Helper::check_unique($table_page, $slug);

            $form_element_name = 'thumbnail';
            if ($request->file($form_element_name)) {
                // UPLOAD IMAGE
                $dir_path = 'uploads/page/';
                $image_file = $request->file($form_element_name);
                $format_image_name = Helper::unique_string() . '-thumbnail';
                $allowed_extensions = ['jpeg', 'jpg', 'png', 'gif'];
                $image = Helper::upload_image($dir_path, $image_file, true, $format_image_name, $allowed_extensions);
                if ($image['status'] != 'true') {
                    # FAILED TO UPLOAD IMAGE
                    return back()
                        ->withInput()
                        ->with('error', lang($image['message'], $this->translations, $image['dynamic_objects']));
                }

                $data->dir_path = $dir_path;
                $data->$form_element_name = $image['data'];
            }

            $form_element_name = 'summary';
            if ($request->$form_element_name) {
                $$form_element_name = Helper::validate_input_text($request->$form_element_name);
                if (!$$form_element_name) {
                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            lang(
                                'Invalid format for #item',
                                $this->translations,
                                ['#item' => ucwords(lang($form_element_name, $this->translations))]
                            )
                        );
                }
                $data->$form_element_name = $$form_element_name;
            }

            // PROCESSING CONTENT ELEMENT - BEGIN
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
                            $v_page_element_image_link_type = $request->v_page_element_image_link_type;
                            $v_page_element_image_link_internal = $request->v_page_element_image_link_internal;
                            $v_page_element_image_link_external = $request->v_page_element_image_link_external;
                            $v_page_element_image_link_target = $request->v_page_element_image_link_target;
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
                                $obj_3->v_page_element_image_link_type = $v_page_element_image_link_type[$key][$key2][$key3];
                                $obj_3->v_page_element_image_link_internal = $v_page_element_image_link_internal[$key][$key2][$key3];
                                $obj_3->v_page_element_image_link_external = $v_page_element_image_link_external[$key][$key2][$key3];
                                $obj_3->v_page_element_image_link_target = $v_page_element_image_link_target[$key][$key2][$key3];
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
                                            ->with(
                                                'error',
                                                lang(
                                                    'Oops, failed to upload image #item - #name. Please try again or try upload another one.',
                                                    $this->translations,
                                                    [
                                                        '#item' => $value2,
                                                        '#name' => $obj_3->v_page_element_image_title
                                                    ]
                                                )
                                            );
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

                                if (isset($v_page_element_image_mobile[$key][$key2][$key3])) {
                                    // IF UPLOAD NEW IMAGE

                                    // PROCESSING IMAGE - v_page_element_image_mobile
                                    $dir_path = 'uploads/page/';
                                    $image_file = $v_page_element_image_mobile[$key][$key2][$key3];
                                    $format_image_name = $key3 . '-' . $value2 . '_mobile' . '-' . Helper::unique_string();
                                    $image = Helper::upload_image($dir_path, $image_file, true, $format_image_name);
                                    if ($image['status'] != 'true') {
                                        return back()
                                            ->withInput()
                                            ->with(
                                                'error',
                                                lang(
                                                    'Oops, failed to upload image #item - #name. Please try again or try upload another one.',
                                                    $this->translations,
                                                    [
                                                        '#item' => $value2,
                                                        '#name' => $obj_3->v_page_element_image_title
                                                    ]
                                                )
                                            );
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
                                            ->with(
                                                'error',
                                                lang(
                                                    'Oops, failed to upload image #item - #name. Please try again or try upload another one.',
                                                    $this->translations,
                                                    [
                                                        '#item' => $value2,
                                                        '#name' => $obj_3->v_page_element_image_title
                                                    ]
                                                )
                                            );
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

                                if (isset($v_page_element_image[$key][$key2][$i])) {
                                    // IF UPLOAD NEW IMAGE

                                    // PROCESSING IMAGE - v_page_element_image
                                    $dir_path = 'uploads/page/';
                                    $image_file = $v_page_element_image[$key][$key2][$i];
                                    $format_image_name = $key2 . '-' . $i . '-imagetextbutton' . '-' . Helper::unique_string();
                                    $image = Helper::upload_image($dir_path, $image_file, true, $format_image_name);
                                    if ($image['status'] != 'true') {
                                        return back()
                                            ->withInput()
                                            ->with(
                                                'error',
                                                lang(
                                                    'Oops, failed to upload image #item - #name. Please try again or try upload another one.',
                                                    $this->translations,
                                                    [
                                                        '#item' => $value2,
                                                        '#name' => $obj_3->v_page_element_image_title
                                                    ]
                                                )
                                            );
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
            $content = json_encode($full_content);
            // PROCESSING CONTENT ELEMENT - END
            $data->content = $content;

            // SEO
            $form_element_name = 'meta_title';
            $form_element_label = 'meta title';
            if ($request->$form_element_name) {
                $$form_element_name = Helper::validate_input_text($request->$form_element_name);
                if (!$$form_element_name) {
                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            lang(
                                'Invalid format for #item',
                                $this->translations,
                                ['#item' => ucwords(lang($form_element_label, $this->translations))]
                            )
                        );
                }
                $data->$form_element_name = $$form_element_name;
            } else {
                // default set value from Title
                $data->$form_element_name = $title;
            }

            $form_element_name = 'meta_description';
            $form_element_label = 'meta description';
            if ($request->$form_element_name) {
                $$form_element_name = Helper::validate_input_text($request->$form_element_name);
                if (!$$form_element_name) {
                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            lang(
                                'Invalid format for #item',
                                $this->translations,
                                ['#item' => ucwords(lang($form_element_label, $this->translations))]
                            )
                        );
                }
                $data->$form_element_name = $$form_element_name;
            } else {
                // default set value from Summary
                $data->$form_element_name = $summary;
            }

            $form_element_name = 'meta_keywords';
            $form_element_label = 'meta keywords';
            if ($request->$form_element_name) {
                $$form_element_name = Helper::validate_input_text($request->$form_element_name);
                if (!$$form_element_name) {
                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            lang(
                                'Invalid format for #item',
                                $this->translations,
                                ['#item' => ucwords(lang($form_element_label, $this->translations))]
                            )
                        );
                }
                $data->$form_element_name = $$form_element_name;
            } else {
                // default set value from Meta Keywords in App Config
                $data->$form_element_name = $this->global_config->$form_element_name;
            }

            $form_element_name = 'meta_author';
            $form_element_label = 'meta author';
            if ($request->$form_element_name) {
                $$form_element_name = Helper::validate_input_text($request->$form_element_name);
                if (!$$form_element_name) {
                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            lang(
                                'Invalid format for #item',
                                $this->translations,
                                ['#item' => ucwords(lang($form_element_label, $this->translations))]
                            )
                        );
                }
                $data->$form_element_name = $$form_element_name;
            }

            $form_element_name = 'og_type';
            $form_element_label = 'open graph type';
            if ($request->$form_element_name) {
                $$form_element_name = Helper::validate_input_text($request->$form_element_name);
                if (!$$form_element_name) {
                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            lang(
                                'Invalid format for #item',
                                $this->translations,
                                ['#item' => ucwords(lang($form_element_label, $this->translations))]
                            )
                        );
                }
                $data->$form_element_name = $$form_element_name;
            } else {
                // default set value
                $data->$form_element_name = 'article';
            }

            $form_element_name = 'og_site_name';
            $form_element_label = 'open graph site name';
            if ($request->$form_element_name) {
                $$form_element_name = Helper::validate_input_text($request->$form_element_name);
                if (!$$form_element_name) {
                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            lang(
                                'Invalid format for #item',
                                $this->translations,
                                ['#item' => ucwords(lang($form_element_label, $this->translations))]
                            )
                        );
                }
                $data->$form_element_name = $$form_element_name;
            } else {
                // default set value from OG Site Name in App Config
                $data->$form_element_name = $this->global_config->$form_element_name;
            }

            $form_element_name = 'og_title';
            $form_element_label = 'open graph title';
            if ($request->$form_element_name) {
                $$form_element_name = Helper::validate_input_text($request->$form_element_name);
                if (!$$form_element_name) {
                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            lang(
                                'Invalid format for #item',
                                $this->translations,
                                ['#item' => ucwords(lang($form_element_label, $this->translations))]
                            )
                        );
                }
                $data->$form_element_name = $$form_element_name;
            } else {
                // default set value from Title
                $data->$form_element_name = $title;
            }

            $form_element_name = 'og_image';
            if ($request->file($form_element_name)) {
                // UPLOAD IMAGE
                $dir_path = 'uploads/page/';
                $image_file = $request->file($form_element_name);
                $format_image_name = Helper::unique_string() . '-og_image';
                $allowed_extensions = ['jpeg', 'jpg', 'png', 'gif'];
                $image = Helper::upload_image($dir_path, $image_file, true, $format_image_name, $allowed_extensions);
                if ($image['status'] != 'true') {
                    # FAILED TO UPLOAD IMAGE
                    return back()
                        ->withInput()
                        ->with('error', lang($image['message'], $this->translations, $image['dynamic_objects']));
                }
                $data->$form_element_name = $dir_path . $image['data'];
            } elseif (isset($data->thumbnail)) {
                // set value using Thumbnail
                $data->$form_element_name = $dir_path . $data->thumbnail;
            } else {
                // default set value using OG Image from App Config
                $data->$form_element_name = $this->global_config->$form_element_name;
            }

            $form_element_name = 'og_description';
            $form_element_label = 'open graph description';
            if ($request->$form_element_name) {
                $$form_element_name = Helper::validate_input_text($request->$form_element_name);
                if (!$$form_element_name) {
                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            lang(
                                'Invalid format for #item',
                                $this->translations,
                                ['#item' => ucwords(lang($form_element_label, $this->translations))]
                            )
                        );
                }
                $data->$form_element_name = $$form_element_name;
            } else {
                // default set value from Summary
                $data->$form_element_name = $summary;
            }

            $form_element_name = 'twitter_card';
            $form_element_label = 'Twitter Card';
            if ($request->$form_element_name) {
                $$form_element_name = Helper::validate_input_text($request->$form_element_name);
                if (!$$form_element_name || !in_array($$form_element_name, ["summary", "summary_large_image", "app", "player"])) {
                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            lang(
                                'Invalid format for #item',
                                $this->translations,
                                ['#item' => ucwords(lang($form_element_label, $this->translations))]
                            )
                        );
                }
                $data->$form_element_name = $$form_element_name;
            } else {
                // default set value
                $data->$form_element_name = 'summary';
            }

            $form_element_name = 'twitter_site';
            $form_element_label = 'Twitter Site';
            if ($request->$form_element_name) {
                $$form_element_name = Helper::validate_input_text($request->$form_element_name);
                if (!$$form_element_name) {
                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            lang(
                                'Invalid format for #item',
                                $this->translations,
                                ['#item' => ucwords(lang($form_element_label, $this->translations))]
                            )
                        );
                }
                $data->$form_element_name = $$form_element_name;
            }

            $form_element_name = 'twitter_site_id';
            $form_element_label = 'Twitter Site ID';
            if ($request->$form_element_name) {
                $$form_element_name = (int) $request->$form_element_name;
                if (!$$form_element_name) {
                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            lang(
                                'Invalid format for #item',
                                $this->translations,
                                ['#item' => ucwords(lang($form_element_label, $this->translations))]
                            )
                        );
                }
                $data->$form_element_name = $$form_element_name;
            }

            $form_element_name = 'twitter_creator';
            $form_element_label = 'Twitter Creator';
            if ($request->$form_element_name) {
                $$form_element_name = Helper::validate_input_text($request->$form_element_name);
                if (!$$form_element_name) {
                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            lang(
                                'Invalid format for #item',
                                $this->translations,
                                ['#item' => ucwords(lang($form_element_label, $this->translations))]
                            )
                        );
                }
                $data->$form_element_name = $$form_element_name;
            }

            $form_element_name = 'twitter_creator_id';
            $form_element_label = 'Twitter Creator ID';
            if ($request->$form_element_name) {
                $$form_element_name = (int) $request->$form_element_name;
                if (!$$form_element_name) {
                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            lang(
                                'Invalid format for #item',
                                $this->translations,
                                ['#item' => ucwords(lang($form_element_label, $this->translations))]
                            )
                        );
                }
                $data->$form_element_name = $$form_element_name;
            }

            $form_element_name = 'fb_app_id';
            $form_element_label = 'FB App ID';
            if ($request->$form_element_name) {
                $$form_element_name = (int) $request->$form_element_name;
                if (!$$form_element_name) {
                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            lang(
                                'Invalid format for #item',
                                $this->translations,
                                ['#item' => ucwords(lang($form_element_label, $this->translations))]
                            )
                        );
                }
                $data->$form_element_name = $$form_element_name;
            }

            $data->header_script = $request->header_script;
            $data->body_script = $request->body_script;
            $data->footer_script = $request->footer_script;

            $data->status = (int) $request->status;

            $data->save();

            // logging
            $log_detail_id = 5; // add new
            $module_id = $this->module_id;
            $target_id = $data->id;
            $note = '"' . $title . '"';
            $value_before = null;
            $value_after = $data;
            $ip_address = $request->ip();
            Helper::logging($log_detail_id, $module_id, $target_id, $note, $value_before, $value_after, $ip_address);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();

            $error_msg = $ex->getMessage() . ' in ' . $ex->getFile() . ' at line ' . $ex->getLine();
            Helper::error_logging($error_msg, $this->module_id);

            if (env('APP_DEBUG') == false) {
                $error_msg = lang('Oops, something went wrong please try again later.', $this->translations);
            }

            # ERROR
            return back()
                ->withInput()
                ->with('error', $error_msg);
        }

        // SET REDIRECT URL
        $redirect_url = 'admin.page';
        if ($request->stay_on_page) {
            $redirect_url = 'admin.page.create';
        }

        # SUCCESS
        return redirect()
            ->route($redirect_url)
            ->with('success', lang(
                'Successfully added a new #item : #name',
                $this->translations,
                [
                    '#item' => $this->item,
                    '#name' => $title
                ]
            ));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  id   $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function edit($id, Request $request)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'View Details');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        // SET THIS OBJECT/ITEM NAME BASED ON TRANSLATION
        $this->item = ucwords(lang($this->item, $this->translations));

        $raw_id = $id;

        if (env('CRYPTOGRAPHY_MODE', false)) {
            $id = Helper::validate_token($id);
        }

        // CHECK OBJECT ID
        if ((int) $id < 1) {
            // INVALID OBJECT ID
            return redirect()
                ->route('admin.page')
                ->with('error', lang('Invalid #item ID, please check your link again', $this->translations, ['#item' => $this->item]));
        }

        // GET DATA BY ID
        $data = page::find($id);

        // CHECK IS DATA FOUND
        if (!$data) {
            # FAILED - DATA NOT FOUND
            return redirect()
                ->route('admin.page')
                ->with('error', lang('#item not found, please check your link again', $this->translations, ['#item' => $this->item]));
        }

        return view('admin.core.page.form', compact('data', 'raw_id'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  id   $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'Edit');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        // SET THIS OBJECT/ITEM NAME BASED ON TRANSLATION
        $this->item = ucwords(lang($this->item, $this->translations));

        $raw_id = $id;

        if (env('CRYPTOGRAPHY_MODE', false)) {
            $id = Helper::validate_token($id);
        }

        // GET DATA BY ID
        $data = page::find($id);

        // CHECK IS DATA FOUND
        if (!$data) {
            # FAILED - DATA NOT FOUND
            return back()
                ->withInput()
                ->with('error', lang('#item not found, please reload your page before resubmit', $this->translations, ['#item' => $this->item]));
        }

        // store data before updated
        $value_before = $data->toJson();

        // LARAVEL VALIDATION
        $validation = [
            'title' => 'required',
            'v_page_section' => 'required',
        ];
        $message = [
            'required' => ':attribute ' . lang('should not be empty', $this->translations)
        ];
        $names = [
            'title' => ucwords(lang('title', $this->translations)),
            'v_page_section' => ucwords(lang('content', $this->translations)),
        ];
        $this->validate($request, $validation, $message, $names);

        DB::beginTransaction();
        try {
            // DB PROCESS BELOW

            // get table name
            $table_page = (new page())->getTable();

            // HELPER VALIDATION FOR PREVENT SQL INJECTION & XSS ATTACK
            $title = Helper::validate_input_text($request->title);
            if (!$title) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => $names['title']]));
            }
            $data->title = $title;

            if ($request->slug) {
                $slug = Helper::generate_slug($request->slug);
            } else {
                $slug = Helper::generate_slug($title);
            }
            if ($data->slug != $slug) {
                // MAKE SURE SLUG IS UNIQUE
                $data->slug = Helper::check_unique($table_page, $slug);
            }

            $form_element_name = 'thumbnail';
            if ($request->file($form_element_name)) {
                // UPLOAD IMAGE
                $dir_path = 'uploads/page/';
                $image_file = $request->file($form_element_name);
                $format_image_name = Helper::unique_string() . '-thumbnail';
                $allowed_extensions = ['jpeg', 'jpg', 'png', 'gif'];
                $image = Helper::upload_image($dir_path, $image_file, true, $format_image_name, $allowed_extensions);
                if ($image['status'] != 'true') {
                    # FAILED TO UPLOAD IMAGE
                    return back()
                        ->withInput()
                        ->with('error', lang($image['message'], $this->translations, $image['dynamic_objects']));
                }

                $data->dir_path = $dir_path;
                $data->$form_element_name = $image['data'];
            }

            $form_element_name = 'summary';
            if ($request->$form_element_name) {
                $$form_element_name = Helper::validate_input_text($request->$form_element_name);
                if (!$$form_element_name) {
                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            lang(
                                'Invalid format for #item',
                                $this->translations,
                                ['#item' => ucwords(lang($form_element_name, $this->translations))]
                            )
                        );
                }
                $data->$form_element_name = $$form_element_name;
            }

            // PROCESSING CONTENT ELEMENT - BEGIN
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
                            $v_page_element_image_link_type = $request->v_page_element_image_link_type;
                            $v_page_element_image_link_internal = $request->v_page_element_image_link_internal;
                            $v_page_element_image_link_external = $request->v_page_element_image_link_external;
                            $v_page_element_image_link_target = $request->v_page_element_image_link_target;
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
                                $obj_3->v_page_element_image_link_type = $v_page_element_image_link_type[$key][$key2][$key3];
                                $obj_3->v_page_element_image_link_internal = $v_page_element_image_link_internal[$key][$key2][$key3];
                                $obj_3->v_page_element_image_link_external = $v_page_element_image_link_external[$key][$key2][$key3];
                                $obj_3->v_page_element_image_link_target = $v_page_element_image_link_target[$key][$key2][$key3];
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
                                            ->with(
                                                'error',
                                                lang(
                                                    'Oops, failed to upload image #item - #name. Please try again or try upload another one.',
                                                    $this->translations,
                                                    [
                                                        '#item' => $value2,
                                                        '#name' => $obj_3->v_page_element_image_title
                                                    ]
                                                )
                                            );
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

                                if (isset($v_page_element_image_mobile[$key][$key2][$key3])) {
                                    // IF UPLOAD NEW IMAGE

                                    // PROCESSING IMAGE - v_page_element_image_mobile
                                    $dir_path = 'uploads/page/';
                                    $image_file = $v_page_element_image_mobile[$key][$key2][$key3];
                                    $format_image_name = $key3 . '-' . $value2 . '_mobile' . '-' . Helper::unique_string();
                                    $image = Helper::upload_image($dir_path, $image_file, true, $format_image_name);
                                    if ($image['status'] != 'true') {
                                        return back()
                                            ->withInput()
                                            ->with(
                                                'error',
                                                lang(
                                                    'Oops, failed to upload image #item - #name. Please try again or try upload another one.',
                                                    $this->translations,
                                                    [
                                                        '#item' => $value2,
                                                        '#name' => $obj_3->v_page_element_image_title
                                                    ]
                                                )
                                            );
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
                                            ->with(
                                                'error',
                                                lang(
                                                    'Oops, failed to upload image #item - #name. Please try again or try upload another one.',
                                                    $this->translations,
                                                    [
                                                        '#item' => $value2,
                                                        '#name' => $obj_3->v_page_element_image_title
                                                    ]
                                                )
                                            );
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

                                if (isset($v_page_element_image[$key][$key2][$i])) {
                                    // IF UPLOAD NEW IMAGE

                                    // PROCESSING IMAGE - v_page_element_image
                                    $dir_path = 'uploads/page/';
                                    $image_file = $v_page_element_image[$key][$key2][$i];
                                    $format_image_name = $key2 . '-' . $i . '-imagetextbutton' . '-' . Helper::unique_string();
                                    $image = Helper::upload_image($dir_path, $image_file, true, $format_image_name);
                                    if ($image['status'] != 'true') {
                                        return back()
                                            ->withInput()
                                            ->with(
                                                'error',
                                                lang(
                                                    'Oops, failed to upload image #item - #name. Please try again or try upload another one.',
                                                    $this->translations,
                                                    [
                                                        '#item' => $value2,
                                                        '#name' => $obj_3->v_page_element_image_title
                                                    ]
                                                )
                                            );
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
            $content = json_encode($full_content);
            // PROCESSING CONTENT ELEMENT - END
            $data->content = $content;

            // SEO
            $form_element_name = 'meta_title';
            $form_element_label = 'meta title';
            if ($request->$form_element_name) {
                $$form_element_name = Helper::validate_input_text($request->$form_element_name);
                if (!$$form_element_name) {
                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            lang(
                                'Invalid format for #item',
                                $this->translations,
                                ['#item' => ucwords(lang($form_element_label, $this->translations))]
                            )
                        );
                }
                $data->$form_element_name = $$form_element_name;
            }

            $form_element_name = 'meta_description';
            $form_element_label = 'meta description';
            if ($request->$form_element_name) {
                $$form_element_name = Helper::validate_input_text($request->$form_element_name);
                if (!$$form_element_name) {
                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            lang(
                                'Invalid format for #item',
                                $this->translations,
                                ['#item' => ucwords(lang($form_element_label, $this->translations))]
                            )
                        );
                }
                $data->$form_element_name = $$form_element_name;
            }

            $form_element_name = 'meta_keywords';
            $form_element_label = 'meta keywords';
            if ($request->$form_element_name) {
                $$form_element_name = Helper::validate_input_text($request->$form_element_name);
                if (!$$form_element_name) {
                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            lang(
                                'Invalid format for #item',
                                $this->translations,
                                ['#item' => ucwords(lang($form_element_label, $this->translations))]
                            )
                        );
                }
                $data->$form_element_name = $$form_element_name;
            }

            $form_element_name = 'meta_author';
            $form_element_label = 'meta author';
            if ($request->$form_element_name) {
                $$form_element_name = Helper::validate_input_text($request->$form_element_name);
                if (!$$form_element_name) {
                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            lang(
                                'Invalid format for #item',
                                $this->translations,
                                ['#item' => ucwords(lang($form_element_label, $this->translations))]
                            )
                        );
                }
                $data->$form_element_name = $$form_element_name;
            }

            $form_element_name = 'og_type';
            $form_element_label = 'open graph type';
            if ($request->$form_element_name) {
                $$form_element_name = Helper::validate_input_text($request->$form_element_name);
                if (!$$form_element_name) {
                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            lang(
                                'Invalid format for #item',
                                $this->translations,
                                ['#item' => ucwords(lang($form_element_label, $this->translations))]
                            )
                        );
                }
                $data->$form_element_name = $$form_element_name;
            } else {
                // default set value
                $data->$form_element_name = 'article';
            }

            $form_element_name = 'og_site_name';
            $form_element_label = 'open graph site name';
            if ($request->$form_element_name) {
                $$form_element_name = Helper::validate_input_text($request->$form_element_name);
                if (!$$form_element_name) {
                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            lang(
                                'Invalid format for #item',
                                $this->translations,
                                ['#item' => ucwords(lang($form_element_label, $this->translations))]
                            )
                        );
                }
                $data->$form_element_name = $$form_element_name;
            } else {
                // default set value from OG Site Name in App Config
                $data->$form_element_name = $this->global_config->$form_element_name;
            }

            $form_element_name = 'og_title';
            $form_element_label = 'open graph title';
            if ($request->$form_element_name) {
                $$form_element_name = Helper::validate_input_text($request->$form_element_name);
                if (!$$form_element_name) {
                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            lang(
                                'Invalid format for #item',
                                $this->translations,
                                ['#item' => ucwords(lang($form_element_label, $this->translations))]
                            )
                        );
                }
                $data->$form_element_name = $$form_element_name;
            }

            $form_element_name = 'og_image';
            if ($request->file($form_element_name)) {
                // UPLOAD IMAGE
                $dir_path = 'uploads/page/';
                $image_file = $request->file($form_element_name);
                $format_image_name = Helper::unique_string() . '-og_image';
                $allowed_extensions = ['jpeg', 'jpg', 'png', 'gif'];
                $image = Helper::upload_image($dir_path, $image_file, true, $format_image_name, $allowed_extensions);
                if ($image['status'] != 'true') {
                    # FAILED TO UPLOAD IMAGE
                    return back()
                        ->withInput()
                        ->with('error', lang($image['message'], $this->translations, $image['dynamic_objects']));
                }
                $data->$form_element_name = $dir_path . $image['data'];
            }

            $form_element_name = 'og_description';
            $form_element_label = 'open graph description';
            if ($request->$form_element_name) {
                $$form_element_name = Helper::validate_input_text($request->$form_element_name);
                if (!$$form_element_name) {
                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            lang(
                                'Invalid format for #item',
                                $this->translations,
                                ['#item' => ucwords(lang($form_element_label, $this->translations))]
                            )
                        );
                }
                $data->$form_element_name = $$form_element_name;
            }

            $form_element_name = 'twitter_card';
            $form_element_label = 'Twitter Card';
            if ($request->$form_element_name) {
                $$form_element_name = Helper::validate_input_text($request->$form_element_name);
                if (!$$form_element_name || !in_array($$form_element_name, ["summary", "summary_large_image", "app", "player"])) {
                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            lang(
                                'Invalid format for #item',
                                $this->translations,
                                ['#item' => ucwords(lang($form_element_label, $this->translations))]
                            )
                        );
                }
                $data->$form_element_name = $$form_element_name;
            } else {
                // default set value
                $data->$form_element_name = 'summary';
            }

            $form_element_name = 'twitter_site';
            $form_element_label = 'Twitter Site';
            if ($request->$form_element_name) {
                $$form_element_name = Helper::validate_input_text($request->$form_element_name);
                if (!$$form_element_name) {
                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            lang(
                                'Invalid format for #item',
                                $this->translations,
                                ['#item' => ucwords(lang($form_element_label, $this->translations))]
                            )
                        );
                }
                $data->$form_element_name = $$form_element_name;
            }

            $form_element_name = 'twitter_site_id';
            $form_element_label = 'Twitter Site ID';
            if ($request->$form_element_name) {
                $$form_element_name = (int) $request->$form_element_name;
                if (!$$form_element_name) {
                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            lang(
                                'Invalid format for #item',
                                $this->translations,
                                ['#item' => ucwords(lang($form_element_label, $this->translations))]
                            )
                        );
                }
                $data->$form_element_name = $$form_element_name;
            }

            $form_element_name = 'twitter_creator';
            $form_element_label = 'Twitter Creator';
            if ($request->$form_element_name) {
                $$form_element_name = Helper::validate_input_text($request->$form_element_name);
                if (!$$form_element_name) {
                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            lang(
                                'Invalid format for #item',
                                $this->translations,
                                ['#item' => ucwords(lang($form_element_label, $this->translations))]
                            )
                        );
                }
                $data->$form_element_name = $$form_element_name;
            }

            $form_element_name = 'twitter_creator_id';
            $form_element_label = 'Twitter Creator ID';
            if ($request->$form_element_name) {
                $$form_element_name = (int) $request->$form_element_name;
                if (!$$form_element_name) {
                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            lang(
                                'Invalid format for #item',
                                $this->translations,
                                ['#item' => ucwords(lang($form_element_label, $this->translations))]
                            )
                        );
                }
                $data->$form_element_name = $$form_element_name;
            }

            $form_element_name = 'fb_app_id';
            $form_element_label = 'FB App ID';
            if ($request->$form_element_name) {
                $$form_element_name = (int) $request->$form_element_name;
                if (!$$form_element_name) {
                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            lang(
                                'Invalid format for #item',
                                $this->translations,
                                ['#item' => ucwords(lang($form_element_label, $this->translations))]
                            )
                        );
                }
                $data->$form_element_name = $$form_element_name;
            }

            $data->header_script = $request->header_script;
            $data->body_script = $request->body_script;
            $data->footer_script = $request->footer_script;

            $data->status = (int) $request->status;

            $data->save();

            // logging
            $value_after = $data->toJson();
            if ($value_before != $value_after) {
                $log_detail_id = 7; // update
                $module_id = $this->module_id;
                $target_id = $data->id;
                $note = '"' . $title . '"';
                $ip_address = $request->ip();
                Helper::logging($log_detail_id, $module_id, $target_id, $note, $value_before, $value_after, $ip_address);
            }

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();

            $error_msg = $ex->getMessage() . ' in ' . $ex->getFile() . ' at line ' . $ex->getLine();
            Helper::error_logging($error_msg, $this->module_id, $id);

            if (env('APP_DEBUG') == false) {
                $error_msg = lang('Oops, something went wrong please try again later.', $this->translations);
            }

            # ERROR
            return back()
                ->withInput()
                ->with('error', $error_msg);
        }

        # SUCCESS
        $success_message = lang(
            'Successfully updated #item : #name',
            $this->translations,
            [
                '#item' => $this->item,
                '#name' => $title
            ]
        );
        if ($request->stay_on_page) {
            return redirect()
                ->route('admin.page.edit', $raw_id)
                ->with('success', $success_message);
        } else {
            return redirect()
                ->route('admin.page')
                ->with('success', $success_message);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'Delete');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        // SET THIS OBJECT/ITEM NAME BASED ON TRANSLATION
        $this->item = ucwords(lang($this->item, $this->translations));

        $raw_id = $request->id;

        if (env('CRYPTOGRAPHY_MODE', false)) {
            $id = Helper::validate_token($raw_id);
        }

        // GET DATA BY ID
        $data = page::find($id);

        // CHECK IS DATA FOUND
        if (!$data) {
            # FAILED - DATA NOT FOUND
            return back()
                ->withInput()
                ->with('error', lang('#item not found, please reload your page before resubmit', $this->translations, ['#item' => $this->item]));
        }

        // store data before updated
        $value_before = $data->toJson();

        // DELETE THE DATA
        if ($data->delete()) {
            // logging
            $log_detail_id = 8; // delete
            $module_id = $this->module_id;
            $target_id = $data->id;
            $note = '"' . $data->name . '"';
            $value_after = $data;
            $ip_address = $request->ip();
            Helper::logging($log_detail_id, $module_id, $target_id, $note, $value_before, $value_after, $ip_address);

            # SUCCESS
            return redirect()
                ->route('admin.page')
                ->with('success', lang('Successfully deleted #item', $this->translations, ['#item' => $this->item]));
        }

        # FAILED
        return back()
            ->with('error', lang('Oops, failed to delete #item. Please try again.', $this->translations, ['#item' => $this->item]));
    }

    /**
     * Display a listing of the deleted resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function deleted_data()
    {
        $deleted_data = true;

        return view('admin.core.page.list', compact('deleted_data'));
    }

    /**
     * Get a listing of the deleted resource using DataTables.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_deleted_data(Datatables $datatables, Request $request)
    {
        $query = page::onlyTrashed();

        return $datatables->eloquent($query)
            ->addColumn('action', function ($data) {
                $object_id = $data->id;
                if (env('CRYPTOGRAPHY_MODE', false)) {
                    $object_id = Helper::generate_token($data->id);
                }

                $wording_restore = ucwords(lang('restore', $this->translations));
                return '<form action="' . route('admin.page.restore') . '" method="POST" onsubmit="return confirm(\'' . lang('Are you sure to restore this #item?', $this->translations, ['#item' => $this->item]) . '\');" style="display: inline"> ' . csrf_field() . ' <input type="hidden" name="id" value="' . $object_id . '">
                <button type="submit" class="btn btn-xs btn-success" title="' . $wording_restore . '"><i class="fa fa-check"></i>&nbsp; ' . $wording_restore . '</button></form>';
            })
            ->editColumn('deleted_at', function ($data) {
                return Helper::time_ago(strtotime($data->deleted_at), lang('ago', $this->translations), Helper::get_periods($this->translations));
            })
            ->editColumn('created_at', function ($data) {
                return Helper::locale_timestamp($data->created_at);
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    /**
     * Restore the specified deleted resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function restore(Request $request)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'Restore');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        // SET THIS OBJECT/ITEM NAME BASED ON TRANSLATION
        $this->item = ucwords(lang($this->item, $this->translations));

        $raw_id = $request->id;

        if (env('CRYPTOGRAPHY_MODE', false)) {
            $id = Helper::validate_token($raw_id);
        }

        // GET DATA BY ID
        $data = page::onlyTrashed()->find($id);

        // CHECK IS DATA FOUND
        if (!$data) {
            # FAILED - DATA NOT FOUND
            return back()
                ->withInput()
                ->with('error', lang('#item not found, please reload your page before resubmit', $this->translations, ['#item' => $this->item]));
        }

        // store data before updated
        $value_before = $data->toJson();

        // RESTORE THE DATA
        if ($data->restore()) {
            // logging
            $log_detail_id = 9; // restore
            $module_id = $this->module_id;
            $target_id = $data->id;
            $note = '"' . $data->name . '"';
            $value_after = $data->toJson();
            $ip_address = $request->ip();
            Helper::logging($log_detail_id, $module_id, $target_id, $note, $value_before, $value_after, $ip_address);

            # SUCCESS
            return redirect()
                ->route('admin.page.deleted_data')
                ->with('success', lang('Successfully restored #item', $this->translations, ['#item' => $this->item]));
        }

        # FAILED
        return back()
            ->with('error', lang('Oops, failed to restore #item. Please try again.', $this->translations, ['#item' => $this->item]));
    }
}
