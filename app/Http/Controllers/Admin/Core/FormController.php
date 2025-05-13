<?php

namespace App\Http\Controllers\Admin\Core;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;

// Libraries
use App\Libraries\Helper;

// Models
use App\Models\form;
use App\Models\form_item;

class FormController extends Controller
{
    // SET THIS MODULE
    private $module = 'Form';
    private $module_id = 19;

    // SET THIS OBJECT/ITEM NAME
    private $item = 'form';

    private $types = ['questionnaire', 'quiz'];

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

        return view('admin.core.form.list');
    }

    /**
     * Get a listing of the resource using DataTables.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_data(Datatables $datatables, Request $request)
    {
        $query = form::whereNotNull('id');

        return $datatables->eloquent($query)
            ->addColumn('action', function ($data) {
                $object_id = $data->id;
                if (env('CRYPTOGRAPHY_MODE', false)) {
                    $object_id = Helper::generate_token($data->id);
                }

                $wording_edit = ucwords(lang('edit', $this->translations));
                $html = '<a href="' . route('admin.form.edit', $object_id) . '" class="btn btn-xs btn-primary" title="' . $wording_edit . '"><i class="fa fa-pencil"></i>&nbsp; ' . $wording_edit . '</a>';

                // $wording_view = ucwords(lang('preview page', $this->translations));
                // $html .= '<a href="' . url($data->slug) . '?preview=' . date('YmdHis') . '" target="_blank" class="btn btn-xs btn-warning" title="' . $wording_view . '"><i class="fa fa-external-link"></i>&nbsp; ' . $wording_view . '</a>';

                $wording_delete = ucwords(lang('delete', $this->translations));
                $html .= '<form action="' . route('admin.form.delete') . '" method="POST" onsubmit="return confirm(\'' . lang('Are you sure to delete this #item?', $this->translations, ['#item' => $this->item]) . '\');" style="display: inline"> ' . csrf_field() . ' <input type="hidden" name="id" value="' . $object_id . '">
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
    public function create($type)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'Add New');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        // SET THIS OBJECT/ITEM NAME BASED ON TRANSLATION
        $this->item = ucwords(lang($this->item, $this->translations));

        if (!in_array($type, $this->types)) {
            return redirect()
                ->route('admin.home')
                ->with('error', lang('Invalid type for #item', $this->translations, ['#item' => $this->item]));
        }

        return view('admin.core.form.form', compact('type'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($type, Request $request)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'Add New');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        // SET THIS OBJECT/ITEM NAME BASED ON TRANSLATION
        $this->item = ucwords(lang($this->item, $this->translations));

        if (!in_array($type, $this->types)) {
            return redirect()
                ->route('admin.home')
                ->with('error', lang('Invalid type for #item', $this->translations, ['#item' => $this->item]));
        }

        // LARAVEL VALIDATION
        $validation = [
            'title' => 'required',
        ];
        $message = [
            'required' => ':attribute ' . lang('should not be empty', $this->translations)
        ];
        $names = [
            'title' => ucwords(lang('title', $this->translations)),
        ];
        $this->validate($request, $validation, $message, $names);

        DB::beginTransaction();
        try {
            // DB PROCESS BELOW

            // get table name
            $table_form = (new form())->getTable();

            // CREATE NEW DATA
            $data = new form();

            $data->type = $type;

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
            $data->slug = Helper::check_unique($table_form, $slug);

            $form_element_name = 'thumbnail';
            if ($request->file($form_element_name)) {
                // UPLOAD IMAGE
                $dir_path = 'uploads/form/';
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

                $data->$form_element_name = $dir_path . $image['data'];
            }

            $intro_title = '';
            $form_element_label = 'introduction title';
            $form_element_name = 'intro_title';
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

            $form_element_label = 'introduction description';
            $form_element_name = 'intro_description';
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

            switch ($request->intro_media) {
                case 'image':
                    $data->intro_media = $request->intro_media;
                    $form_element_name = 'intro_src_image';
                    if ($request->file($form_element_name)) {
                        // UPLOAD IMAGE
                        $dir_path = 'uploads/form/';
                        $image_file = $request->file($form_element_name);
                        $format_image_name = Helper::unique_string() . '-intro_src_image';
                        $allowed_extensions = ['jpeg', 'jpg', 'png', 'gif'];
                        $image = Helper::upload_image($dir_path, $image_file, true, $format_image_name, $allowed_extensions);
                        if ($image['status'] != 'true') {
                            # FAILED TO UPLOAD IMAGE
                            return back()
                                ->withInput()
                                ->with('error', lang($image['message'], $this->translations, $image['dynamic_objects']));
                        }

                        $data->intro_src = $dir_path . $image['data'];
                    }
                    break;

                case 'video':
                    $data->intro_media = $request->intro_media;
                    $form_element_label = 'introduction media - video';
                    $form_element_name = 'intro_src_video';
                    if ($request->$form_element_name) {
                        $$form_element_name = Helper::validate_input_url($request->$form_element_name);
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
                        $data->intro_src = $$form_element_name;
                    }
                    break;

                default:
                    // none
                    $data->intro_media = 'none';
                    $data->intro_src = null;
                    break;
            }

            $data->form_point = (int) $request->form_point;
            $data->must_complete = (int) $request->must_complete;
            $data->status = (int) $request->status;

            $form_element_name = 'started_at';
            if ($request->$form_element_name) {
                $dates = explode(' ', $request->$form_element_name);
                $the_date = $dates[0];
                $the_time = $dates[1];
                $converted_date = Helper::convert_datepicker($the_date);
                $data->$form_element_name = $converted_date . ' ' . $the_time;
            } else {
                $data->$form_element_name = null;
            }

            $form_element_name = 'finished_at';
            if ($request->$form_element_name) {
                $dates = explode(' ', $request->$form_element_name);
                $the_date = $dates[0];
                $the_time = $dates[1];
                $converted_date = Helper::convert_datepicker($the_date);
                $data->$form_element_name = $converted_date . ' ' . $the_time;
            } else {
                $data->$form_element_name = null;
            }

            // SEO
            $form_element_label = 'meta title';
            $form_element_name = 'meta_title';
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

            $form_element_label = 'meta description';
            $form_element_name = 'meta_description';
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
                // default set value from intro_title
                $data->$form_element_name = $intro_title;
            }

            $form_element_label = 'meta keywords';
            $form_element_name = 'meta_keywords';
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

            $form_element_label = 'meta author';
            $form_element_name = 'meta_author';
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

            $form_element_label = 'open graph type';
            $form_element_name = 'og_type';
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

            $form_element_label = 'open graph site name';
            $form_element_name = 'og_site_name';
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

            $form_element_label = 'open graph title';
            $form_element_name = 'og_title';
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
                $dir_path = 'uploads/form/';
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
                $data->$form_element_name = $data->thumbnail;
            } else {
                // default set value using OG Image from App Config
                $data->$form_element_name = $this->global_config->$form_element_name;
            }

            $form_element_label = 'open graph description';
            $form_element_name = 'og_description';
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
                // default set value from intro_title
                $data->$form_element_name = $intro_title;
            }

            $form_element_label = 'Twitter Card';
            $form_element_name = 'twitter_card';
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

            $form_element_label = 'Twitter Site';
            $form_element_name = 'twitter_site';
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

            $form_element_label = 'Twitter Site ID';
            $form_element_name = 'twitter_site_id';
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

            $form_element_label = 'Twitter Creator';
            $form_element_name = 'twitter_creator';
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

            $form_element_label = 'Twitter Creator ID';
            $form_element_name = 'twitter_creator_id';
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

            $form_element_label = 'FB App ID';
            $form_element_name = 'fb_app_id';
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

            $data->save();

            // PROCESSING CONTENT ELEMENT - BEGIN
            /**
             * ARRAY 1 - QUESTION
             * 
             * array[]
             * - item
             */
            $v_question_text = $request->v_question_text;
            $v_response_wording = $request->v_response_wording;
            $v_question_media = $request->v_question_media;
            $v_question_src_image = $request->v_question_src_image;
            $v_question_src_image_exist = $request->v_question_src_image_exist;
            $v_question_src_video = $request->v_question_src_video;
            $v_question_type = $request->v_question_type;

            $v_option_type = $request->v_option_type;
            $v_option_other = $request->v_option_other; // only for Questionnaire

            $v_point_per_item = $request->v_point_per_item; // only for Quiz
            $v_option_answer = $request->v_option_answer; // only for Quiz

            $v_is_required = $request->v_is_required;

            $v_option_start = $request->v_option_start; // only for Linear Scale
            $v_option_start_label = $request->v_option_start_label; // only for Linear Scale
            $v_option_until = $request->v_option_until; // only for Linear Scale
            $v_option_until_label = $request->v_option_until_label; // only for Linear Scale

            /**
             * ARRAY 2 - OPTIONS
             * 
             * array[]
             * - array[]
             * - - item
             */
            $v_option_text = $request->v_option_text;
            $v_option_media = $request->v_option_media;
            $v_option_media_exist = $request->v_option_media_exist;

            $ordinal = 1;
            // looping based on amount questions (question_type)
            foreach ($v_question_type as $key => $value) {
                $option = new form_item();
                $option->form_id = $data->id;
                $option->ordinal = $ordinal;
                $option->question_text = Helper::validate_input_text($v_question_text[$key]);
                if (isset($v_response_wording[$key])) {
                    $option->response_wording = Helper::validate_input_text($v_response_wording[$key]);
                }

                // TODO question_media & question_src
                $option->question_media = null;
                $option->question_src = null;
                if (isset($v_question_media[$key])) {
                    switch ($v_question_media[$key]) {
                        case 'image':
                            break;

                        case 'video':
                            break;
                    }
                }

                $option->question_type = Helper::validate_input_text($value); // ['multiple_choice', 'checkboxes', 'drop-down', 'linear_scale']
                $option->option_type = Helper::validate_input_text($v_option_type[$key]); // ['text', 'image']
                if (isset($v_option_other[$key])) {
                    $option->option_other = (int) $v_option_other[$key]; // 1/0
                }

                if (isset($v_option_text[$key])) {
                    $the_options_text = [];

                    // for Quiz
                    $identifier = null;
                    if (isset($v_option_answer[$key])) {
                        $identifier = $v_option_answer[$key]; // identifier of option as answer
                    }

                    $index_answer = 0;
                    $opt_index = 0;
                    foreach ($v_option_text[$key] as $key2 => $value2) {
                        $the_options_text[] = $value2;
                        if ($key2 == $identifier) {
                            $index_answer = $opt_index;
                        }
                        $opt_index++;
                    }
                    $option->options_text = json_encode($the_options_text);

                    $the_options_media = [];
                    $opt_index = 0;
                    if (isset($v_option_media_exist[$key])) {
                        foreach ($v_option_media_exist[$key] as $key2 => $value2) {
                            if (isset($v_option_media[$key][$key2])) {
                                # UPLOAD NEW IMAGE
                                $dir_path = 'uploads/form/';
                                $image_file = $v_option_media[$key][$key2];
                                $format_image_name = Helper::unique_string() . '-opt_media';
                                $allowed_extensions = ['jpeg', 'jpg', 'png', 'gif'];
                                $image = Helper::upload_image($dir_path, $image_file, true, $format_image_name, $allowed_extensions);
                                if ($image['status'] != 'true') {
                                    # FAILED TO UPLOAD IMAGE
                                    return back()
                                        ->withInput()
                                        ->with('error', lang($image['message'], $this->translations, $image['dynamic_objects']));
                                }

                                $the_option_media = $dir_path . $image['data'];
                            } else {
                                # SET VALUE FROM EXISTING IMAGE
                                $the_option_media = $value2;
                            }

                            $the_options_media[] = $the_option_media;
                            $opt_index++;
                        }
                        $option->options_media = json_encode($the_options_media);
                    }

                    // for Quiz
                    if (isset($v_option_answer[$key])) {
                        $option->option_answer_index = $index_answer;
                        $option->option_answer_text = $the_options_text[$index_answer];

                        if (isset($the_options_media[$index_answer])) {
                            $option->option_answer_media = $the_options_media[$index_answer];
                        }
                    }
                }

                if (isset($v_point_per_item[$key])) {
                    $option->point_per_item = (int) $v_point_per_item[$key];
                }

                if (isset($v_is_required[$key])) {
                    $option->is_required = (int) $v_is_required[$key];
                }

                if ($option->question_type == 'linear_scale') {
                    $the_options_text = [];
                    for ($i=$v_option_start[$key]; $i <= $v_option_until[$key]; $i++) { 
                        $the_options_text[] = (int) $i;
                    }
                    $option->options_text = json_encode($the_options_text); // 0,1,2

                    // store the labels
                    if (isset($v_option_start_label[$key]) && isset($v_option_until_label[$key])) {
                        $the_options_media = [];
                        $the_options_media[] = $v_option_start_label[$key];
                        $the_options_media[] = $v_option_until_label[$key];
                        $option->options_media = json_encode($the_options_media);
                    }
                }

                // TODO checkpoint_status

                $option->save();

                $ordinal++;
            }
            // PROCESSING CONTENT ELEMENT - END

            // logging
            $item_name = $title;
            $log_detail_id = 5; // add new
            $module_id = $this->module_id;
            $target_id = $data->id;
            $note = '"' . $item_name . '"';
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
        $redirect_url = 'admin.form';
        $param_url = [];
        if ($request->stay_on_page) {
            $redirect_url = 'admin.form.create';
            $param_url[] = $type;
        }

        # SUCCESS
        return redirect()
            ->route($redirect_url, $param_url)
            ->with('success', lang(
                'Successfully added a new #item : #name',
                $this->translations,
                [
                    '#item' => $this->item,
                    '#name' => $item_name
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
                ->route('admin.form')
                ->with('error', lang('Invalid #item ID, please check your link again', $this->translations, ['#item' => $this->item]));
        }

        // GET DATA BY ID
        $data = form::find($id);

        // CHECK IS DATA FOUND
        if (!$data) {
            # FAILED - DATA NOT FOUND
            return redirect()
                ->route('admin.form')
                ->with('error', lang('#item not found, please check your link again', $this->translations, ['#item' => $this->item]));
        }

        // GET FORM TYPE
        $type = $data->type;

        // GET intro_src
        switch ($data->intro_media) {
            case 'image':
                $data->intro_src_image = $data->intro_src;
                break;

            case 'video':
                $data->intro_src_video = $data->intro_src;
                break;
        }

        // GET FORM ITEMS DATA
        $data_items = form_item::where('form_id', $id)->orderBy('ordinal')->get();

        return view('admin.core.form.form', compact('data', 'raw_id', 'data_items', 'type'));
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
        $data = form::find($id);

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
        ];
        $message = [
            'required' => ':attribute ' . lang('should not be empty', $this->translations)
        ];
        $names = [
            'title' => ucwords(lang('title', $this->translations)),
        ];
        $this->validate($request, $validation, $message, $names);

        DB::beginTransaction();
        try {
            // DB PROCESS BELOW

            // get table name
            $table_form = (new form())->getTable();

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
                $data->slug = Helper::check_unique($table_form, $slug);
            }

            $form_element_name = 'thumbnail';
            if ($request->file($form_element_name)) {
                // UPLOAD IMAGE
                $dir_path = 'uploads/form/';
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

                $data->$form_element_name = $dir_path . $image['data'];
            }

            $intro_title = '';
            $form_element_label = 'introduction title';
            $form_element_name = 'intro_title';
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

            $form_element_label = 'introduction description';
            $form_element_name = 'intro_description';
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

            switch ($request->intro_media) {
                case 'image':
                    $data->intro_media = $request->intro_media;
                    $form_element_name = 'intro_src_image';
                    if ($request->file($form_element_name)) {
                        // UPLOAD IMAGE
                        $dir_path = 'uploads/form/';
                        $image_file = $request->file($form_element_name);
                        $format_image_name = Helper::unique_string() . '-intro_src_image';
                        $allowed_extensions = ['jpeg', 'jpg', 'png', 'gif'];
                        $image = Helper::upload_image($dir_path, $image_file, true, $format_image_name, $allowed_extensions);
                        if ($image['status'] != 'true') {
                            # FAILED TO UPLOAD IMAGE
                            return back()
                                ->withInput()
                                ->with('error', lang($image['message'], $this->translations, $image['dynamic_objects']));
                        }

                        $data->intro_src = $dir_path . $image['data'];
                    }
                    break;

                case 'video':
                    $data->intro_media = $request->intro_media;
                    $form_element_label = 'introduction media - video';
                    $form_element_name = 'intro_src_video';
                    if ($request->$form_element_name) {
                        $$form_element_name = Helper::validate_input_url($request->$form_element_name);
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
                        $data->intro_src = $$form_element_name;
                    }
                    break;

                default:
                    // none
                    $data->intro_media = 'none';
                    $data->intro_src = null;
                    break;
            }

            $data->form_point = (int) $request->form_point;
            $data->must_complete = (int) $request->must_complete;
            $data->status = (int) $request->status;

            $form_element_name = 'started_at';
            if ($request->$form_element_name) {
                $dates = explode(' ', $request->$form_element_name);
                $the_date = $dates[0];
                $the_time = $dates[1];
                $converted_date = Helper::convert_datepicker($the_date);
                $data->$form_element_name = $converted_date . ' ' . $the_time;
            } else {
                $data->$form_element_name = null;
            }

            $form_element_name = 'finished_at';
            if ($request->$form_element_name) {
                $dates = explode(' ', $request->$form_element_name);
                $the_date = $dates[0];
                $the_time = $dates[1];
                $converted_date = Helper::convert_datepicker($the_date);
                $data->$form_element_name = $converted_date . ' ' . $the_time;
            } else {
                $data->$form_element_name = null;
            }

            // SEO
            $form_element_label = 'meta title';
            $form_element_name = 'meta_title';
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

            $form_element_label = 'meta description';
            $form_element_name = 'meta_description';
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
                // default set value from intro_title
                $data->$form_element_name = $intro_title;
            }

            $form_element_label = 'meta keywords';
            $form_element_name = 'meta_keywords';
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

            $form_element_label = 'meta author';
            $form_element_name = 'meta_author';
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

            $form_element_label = 'open graph type';
            $form_element_name = 'og_type';
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

            $form_element_label = 'open graph site name';
            $form_element_name = 'og_site_name';
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

            $form_element_label = 'open graph title';
            $form_element_name = 'og_title';
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
                $dir_path = 'uploads/form/';
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
                $data->$form_element_name = $data->thumbnail;
            } else {
                // default set value using OG Image from App Config
                $data->$form_element_name = $this->global_config->$form_element_name;
            }

            $form_element_label = 'open graph description';
            $form_element_name = 'og_description';
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
                // default set value from intro_title
                $data->$form_element_name = $intro_title;
            }

            $form_element_label = 'Twitter Card';
            $form_element_name = 'twitter_card';
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

            $form_element_label = 'Twitter Site';
            $form_element_name = 'twitter_site';
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

            $form_element_label = 'Twitter Site ID';
            $form_element_name = 'twitter_site_id';
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

            $form_element_label = 'Twitter Creator';
            $form_element_name = 'twitter_creator';
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

            $form_element_label = 'Twitter Creator ID';
            $form_element_name = 'twitter_creator_id';
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

            $form_element_label = 'FB App ID';
            $form_element_name = 'fb_app_id';
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

            $data->save();

            // delete the items first
            form_item::where('form_id', $id)->delete();

            // PROCESSING CONTENT ELEMENT - BEGIN
            /**
             * ARRAY 1 - QUESTION
             * 
             * array[]
             * - item
             */
            $v_question_text = $request->v_question_text;
            $v_response_wording = $request->v_response_wording;
            $v_question_media = $request->v_question_media;
            $v_question_src_image = $request->v_question_src_image;
            $v_question_src_image_exist = $request->v_question_src_image_exist;
            $v_question_src_video = $request->v_question_src_video;
            $v_question_type = $request->v_question_type;

            $v_option_type = $request->v_option_type;
            $v_option_other = $request->v_option_other; // only for Questionnaire

            $v_point_per_item = $request->v_point_per_item; // only for Quiz
            $v_option_answer = $request->v_option_answer; // only for Quiz

            $v_is_required = $request->v_is_required;

            $v_option_start = $request->v_option_start; // only for Linear Scale
            $v_option_start_label = $request->v_option_start_label; // only for Linear Scale
            $v_option_until = $request->v_option_until; // only for Linear Scale
            $v_option_until_label = $request->v_option_until_label; // only for Linear Scale

            /**
             * ARRAY 2 - OPTIONS
             * 
             * array[]
             * - array[]
             * - - item
             */
            $v_option_text = $request->v_option_text;
            $v_option_media = $request->v_option_media;
            $v_option_media_exist = $request->v_option_media_exist;

            $ordinal = 1;
            // looping based on amount questions (question_type)
            foreach ($v_question_type as $key => $value) {
                $option = new form_item();
                $option->form_id = $data->id;
                $option->ordinal = $ordinal;
                $option->question_text = Helper::validate_input_text($v_question_text[$key]);
                if (isset($v_response_wording[$key])) {
                    $option->response_wording = Helper::validate_input_text($v_response_wording[$key]);
                }

                // TODO question_media & question_src
                $option->question_media = null;
                $option->question_src = null;
                if (isset($v_question_media[$key])) {
                    switch ($v_question_media[$key]) {
                        case 'image':
                            break;

                        case 'video':
                            break;
                    }
                }

                $option->question_type = Helper::validate_input_text($value); // ['multiple_choice', 'checkboxes', 'drop-down', 'linear_scale']
                $option->option_type = Helper::validate_input_text($v_option_type[$key]); // ['text', 'image']
                if (isset($v_option_other[$key])) {
                    $option->option_other = (int) $v_option_other[$key]; // 1/0
                }

                if (isset($v_option_text[$key])) {
                    $the_options_text = [];

                    // for Quiz
                    $identifier = null;
                    if (isset($v_option_answer[$key])) {
                        $identifier = $v_option_answer[$key]; // identifier of option as answer
                    }

                    $index_answer = 0;
                    $opt_index = 0;
                    foreach ($v_option_text[$key] as $key2 => $value2) {
                        $the_options_text[] = $value2;
                        if ($key2 == $identifier) {
                            $index_answer = $opt_index;
                        }
                        $opt_index++;
                    }
                    $option->options_text = json_encode($the_options_text);

                    $the_options_media = [];
                    $opt_index = 0;
                    if (isset($v_option_media_exist[$key])) {
                        foreach ($v_option_media_exist[$key] as $key2 => $value2) {
                            if (isset($v_option_media[$key][$key2])) {
                                # UPLOAD NEW IMAGE
                                $dir_path = 'uploads/form/';
                                $image_file = $v_option_media[$key][$key2];
                                $format_image_name = Helper::unique_string() . '-opt_media';
                                $allowed_extensions = ['jpeg', 'jpg', 'png', 'gif'];
                                $image = Helper::upload_image($dir_path, $image_file, true, $format_image_name, $allowed_extensions);
                                if ($image['status'] != 'true') {
                                    # FAILED TO UPLOAD IMAGE
                                    return back()
                                        ->withInput()
                                        ->with('error', lang($image['message'], $this->translations, $image['dynamic_objects']));
                                }

                                $the_option_media = $dir_path . $image['data'];
                            } else {
                                # SET VALUE FROM EXISTING IMAGE
                                $the_option_media = $value2;
                            }

                            $the_options_media[] = $the_option_media;
                            $opt_index++;
                        }
                        $option->options_media = json_encode($the_options_media);
                    }

                    // for Quiz
                    if (isset($v_option_answer[$key])) {
                        $option->option_answer_index = $index_answer;
                        $option->option_answer_text = $the_options_text[$index_answer];

                        if (isset($the_options_media[$index_answer])) {
                            $option->option_answer_media = $the_options_media[$index_answer];
                        }
                    }
                }

                if (isset($v_point_per_item[$key])) {
                    $option->point_per_item = (int) $v_point_per_item[$key];
                }

                if (isset($v_is_required[$key])) {
                    $option->is_required = (int) $v_is_required[$key];
                }

                if ($option->question_type == 'linear_scale') {
                    $the_options_text = [];
                    for ($i=$v_option_start[$key]; $i <= $v_option_until[$key]; $i++) { 
                        $the_options_text[] = (int) $i;
                    }
                    $option->options_text = json_encode($the_options_text); // 0,1,2

                    // store the labels
                    if (isset($v_option_start_label[$key]) && isset($v_option_until_label[$key])) {
                        $the_options_media = [];
                        $the_options_media[] = $v_option_start_label[$key];
                        $the_options_media[] = $v_option_until_label[$key];
                        $option->options_media = json_encode($the_options_media);
                    }
                }

                // TODO checkpoint_status

                $option->save();

                $ordinal++;
            }
            // PROCESSING CONTENT ELEMENT - END

            // logging
            $item_name = $title;
            $value_after = $data->toJson();
            if ($value_before != $value_after) {
                $log_detail_id = 7; // update
                $module_id = $this->module_id;
                $target_id = $data->id;
                $note = '"' . $item_name . '"';
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
                '#name' => $item_name
            ]
        );
        if ($request->stay_on_page) {
            return redirect()
                ->route('admin.form.edit', $raw_id)
                ->with('success', $success_message);
        } else {
            return redirect()
                ->route('admin.form')
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
        $data = form::find($id);

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
                ->route('admin.form')
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
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'Restore');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        $deleted_data = true;

        return view('admin.core.form.list', compact('deleted_data'));
    }

    /**
     * Get a listing of the deleted resource using DataTables.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_deleted_data(Datatables $datatables, Request $request)
    {
        $query = form::onlyTrashed();

        return $datatables->eloquent($query)
            ->addColumn('action', function ($data) {
                $object_id = $data->id;
                if (env('CRYPTOGRAPHY_MODE', false)) {
                    $object_id = Helper::generate_token($data->id);
                }

                $wording_restore = ucwords(lang('restore', $this->translations));
                return '<form action="' . route('admin.form.restore') . '" method="POST" onsubmit="return confirm(\'' . lang('Are you sure to restore this #item?', $this->translations, ['#item' => $this->item]) . '\');" style="display: inline"> ' . csrf_field() . ' <input type="hidden" name="id" value="' . $object_id . '">
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
        $data = form::onlyTrashed()->find($id);

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
                ->route('admin.form.deleted_data')
                ->with('success', lang('Successfully restored #item', $this->translations, ['#item' => $this->item]));
        }

        # FAILED
        return back()
            ->with('error', lang('Oops, failed to restore #item. Please try again.', $this->translations, ['#item' => $this->item]));
    }
}
