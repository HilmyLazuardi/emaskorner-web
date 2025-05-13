<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;

// Libraries
use App\Libraries\Helper;

// Models
use App\Models\news;
use App\Models\news_category;

class NewsController extends Controller
{
    // SET THIS MODULE
    private $module     = 'News';
    private $module_id  = 36;

    // SET THIS OBJECT/ITEM NAME
    private $item       = 'news';

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

        return view('admin.news.list');
    }

    /**
     * Get a listing of the resource using DataTables.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_data(Datatables $datatables, Request $request)
    {
        $query = news::select(
                'news.id',
                'news.title',
                'news.slug',
                'news.status',
                'news.created_at',
                'news.updated_at',

                'news_categories.name as category_name'
            )
            ->leftJoin('news_categories', 'news.news_category_id', 'news_categories.id');

        return $datatables->eloquent($query)
            ->addColumn('action', function ($data) {
                $object_id = $data->id;
                if (env('CRYPTOGRAPHY_MODE', false)) {
                    $object_id = Helper::generate_token($data->id);
                }

                $wording_edit = ucwords(lang('edit', $this->translations));
                $html = '<a href="' . route('admin.news.edit', $object_id) . '" class="btn btn-xs btn-primary" title="' . $wording_edit . '"><i class="fa fa-pencil"></i>&nbsp; ' . $wording_edit . '</a>';

                $wording_delete = ucwords(lang('delete', $this->translations));
                $html .= '<form action="' . route('admin.news.delete') . '" method="POST" onsubmit="return confirm(\'' . lang('Are you sure to delete this #item?', $this->translations, ['#item' => $this->item]) . '\');" style="display: inline"> ' . csrf_field() . ' <input type="hidden" name="id" value="' . $object_id . '">
                <button type="submit" class="btn btn-xs btn-danger" title="' . $wording_delete . '"><i class="fa fa-trash"></i>&nbsp; ' . $wording_delete . '</button></form>';

                return $html;
            })
            ->addColumn('status', function ($data) {
                if ($data->status != 1) {
                    return '<span class="label label-danger"><i>' . ucwords(lang('inactive', $this->translations)) . '</i></span>';
                }
                return '<span class="label label-success">' . ucwords(lang('active', $this->translations)) . '</span>';
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

        $news_category = news_category::where('status', 1)->get();

        return view('admin.news.form', compact('news_category'));
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
            // 'v_page_section' => 'required',
            'v_element_type' => 'required',
            'news_category_id' => 'required|integer'
        ];
        $message = [
            'required'  => ':attribute ' . lang('should not be empty', $this->translations),
            'integer'   => ':attribute ' . lang('must be a number', $this->translations),
        ];
        $names = [
            'title' => ucwords(lang('title', $this->translations)),
            // 'v_page_section' => ucwords(lang('content', $this->translations)),
            'v_element_type' => ucwords(lang('content element', $this->translations)),
            'news_category_id' => ucwords(lang('category', $this->translations)),
        ];
        $this->validate($request, $validation, $message, $names);

        DB::beginTransaction();
        try {
            // DB PROCESS BELOW

            // get table name
            $table_news = (new news())->getTable();

            // CREATE NEW DATA
            $data = new news();

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
            $data->slug = Helper::check_unique($table_news, $slug);

            $form_element_name = 'thumbnail';
            if ($request->file($form_element_name)) {
                // UPLOAD IMAGE
                $dir_path = 'uploads/news/';
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

            // AUTHOR
            $author = Helper::validate_input_text($request->author);
            $data->author = $author;

            // LOCATION
            $location = Helper::validate_input_text($request->location);
            $data->location = $location;

            $form_element_name = 'posted_at';
            if ($request->$form_element_name) {
                $converted_date = Helper::convert_datepicker($request->$form_element_name);
                $data->$form_element_name = $converted_date . ' 00:00:00';
            } else {
                $data->$form_element_name = date('Y-m-d H:i:s');
            }

            // PROCESSING CONTENT ELEMENT
            $full_content = $this->insert_content($request);
            $content = json_encode($full_content);
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
                $dir_path = 'uploads/news/';
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

            $data->header_script = $request->header_script;
            $data->body_script = $request->body_script;
            $data->footer_script = $request->footer_script;

            $data->news_category_id = (int) $request->news_category_id;
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
        $redirect_url = 'admin.news';
        if ($request->stay_on_page) {
            $redirect_url = 'admin.news.create';
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
                ->route('admin.news_category')
                ->with('error', lang('Invalid #item ID, please check your link again', $this->translations, ['#item' => $this->item]));
        }

        // GET DATA BY ID
        $data = news::find($id);

        // CHECK IS DATA FOUND
        if (!$data) {
            # FAILED - DATA NOT FOUND
            return redirect()
                ->route('admin.news')
                ->with('error', lang('#item not found, please check your link again', $this->translations, ['#item' => $this->item]));
        }

        $news_category = news_category::where('status', 1)->get();

        return view('admin.news.form', compact('data', 'raw_id', 'news_category'));
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
        $data = news::find($id);

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
            // 'v_page_section' => 'required',
            'v_element_type' => 'required',
            'news_category_id' => 'required|integer'
        ];
        $message = [
            'required' => ':attribute ' . lang('should not be empty', $this->translations),
            'integer'   => ':attribute ' . lang('must be a number', $this->translations),
        ];
        $names = [
            'title' => ucwords(lang('title', $this->translations)),
            // 'v_page_section' => ucwords(lang('content', $this->translations)),
            'v_element_type' => ucwords(lang('content element', $this->translations)),
            'news_category_id' => ucwords(lang('category', $this->translations))
        ];
        $this->validate($request, $validation, $message, $names);

        DB::beginTransaction();
        try {
            // DB PROCESS BELOW

            // get table name
            $table_news = (new news())->getTable();

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
                $data->slug = Helper::check_unique($table_news, $slug);
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
            
            // AUTHOR
            $author = Helper::validate_input_text($request->author);
            $data->author = $author;

            // LOCATION
            $location = Helper::validate_input_text($request->location);
            $data->location = $location;

            $form_element_name = 'posted_at';
            if ($request->$form_element_name) {
                $converted_date = Helper::convert_datepicker($request->$form_element_name);
                $data->$form_element_name = $converted_date . ' 00:00:00';
            } else {
                $data->$form_element_name = date('Y-m-d H:i:s');
            }

            // PROCESSING CONTENT ELEMENT
            $full_content = $this->update_content($request, $data);
            $content = json_encode($full_content);
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

            $data->header_script = $request->header_script;
            $data->body_script = $request->body_script;
            $data->footer_script = $request->footer_script;

            $data->news_category_id = (int) $request->news_category_id;
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
                ->route('admin.news.edit', $raw_id)
                ->with('success', $success_message);
        } else {
            return redirect()
                ->route('admin.news')
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

        $id = $request->id;
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $id = Helper::validate_token($id);
        }

        // GET DATA BY ID
        $data = news::find($id);

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
            $log_detail_id  = 8; // delete
            $module_id      = $this->module_id;
            $target_id      = $data->id;
            $note           = '"' . $data->name . '"';
            $value_after    = $data;
            $ip_address     = $request->ip();
            Helper::logging($log_detail_id, $module_id, $target_id, $note, $value_before, $value_after, $ip_address);

            # SUCCESS
            return redirect()
                ->route('admin.news')
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

        return view('admin.news.list', compact('deleted_data'));
    }

    /**
     * Get a listing of the deleted resource using DataTables.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_deleted_data(Datatables $datatables, Request $request)
    {
        $query = news::onlyTrashed();

        return $datatables->eloquent($query)
            ->addColumn('action', function ($data) {
                $object_id = $data->id;
                if (env('CRYPTOGRAPHY_MODE', false)) {
                    $object_id = Helper::generate_token($data->id);
                }

                $wording_restore = ucwords(lang('restore', $this->translations));
                return '<form action="' . route('admin.news.restore') . '" method="POST" onsubmit="return confirm(\'' . lang('Are you sure to restore this #item?', $this->translations, ['#item' => $this->item]) . '\');" style="display: inline"> ' . csrf_field() . ' <input type="hidden" name="id" value="' . $object_id . '">
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

        $id = $request->id;
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $id = Helper::validate_token($id);
        }

        // GET DATA BY ID
        $data = news::onlyTrashed()->find($id);

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
            $log_detail_id  = 9; // restore
            $module_id      = $this->module_id;
            $target_id      = $data->id;
            $note           = '"' . $data->name . '"';
            $value_after    = $data->toJson();
            $ip_address     = $request->ip();
            Helper::logging($log_detail_id, $module_id, $target_id, $note, $value_before, $value_after, $ip_address);

            # SUCCESS
            return redirect()
                ->route('admin.news.deleted_data')
                ->with('success', lang('Successfully restored #item', $this->translations, ['#item' => $this->item]));
        }

        # FAILED
        return back()
            ->with('error', lang('Oops, failed to restore #item. Please try again.', $this->translations, ['#item' => $this->item]));
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
                    $destinationPath_content = public_path('uploads/news/');
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
                    $destinationPath_content = public_path('uploads/news/');
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
        $exist_content = json_decode($data->content, true);
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
                        $destinationPath_content = public_path('uploads/news/');
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
                        $destinationPath_content = public_path('uploads/news/');
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
}