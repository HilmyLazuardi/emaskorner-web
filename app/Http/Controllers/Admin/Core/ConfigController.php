<?php

namespace App\Http\Controllers\Admin\Core;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ConfigExportView;
use App\Imports\ConfigImport;

// Libraries
use App\Libraries\Helper;

// Models
use App\Models\config;

class ConfigController extends Controller
{
    // SET THIS MODULE
    private $module = 'Config';
    private $module_id = 10;
    private $item = 'Config';

    /**
     * Display & update application configuration.
     *
     * @return view()
     */
    public function index(Request $request)
    {
        $input_names_bg_images = ['login', '404', '405', '419'];

        if ($request->isMethod('post')) {
            // AUTHORIZING...
            $authorize = Helper::authorizing($this->module, 'Update');
            if ($authorize['status'] != 'true') {
                return back()->with('error', $authorize['message']);
            }

            // LARAVEL VALIDATION
            $validation = [
                'app_name' => 'required',
                'app_version' => 'required',
                'app_copyright_year' => 'required',
                'app_url_site' => 'required',
                'app_skin' => 'required',
                'app_info' => 'required',
                'meta_title' => 'required',
                'meta_description' => 'required',
                'meta_keywords' => 'required',
                'meta_author' => 'required',
                'og_type' => 'required',
                'og_site_name' => 'required',
                'og_title' => 'required',
                'og_description' => 'required',
                'twitter_card' => 'required'
            ];

            // if upload images
            if ($request->app_favicon) {
                $validation['app_favicon'] = 'required|max:2048';
            }
            if ($request->app_logo) {
                $validation['app_logo'] = 'required|image|max:2048';
            }
            if ($request->og_image) {
                $validation['og_image'] = 'required|image|max:2048';
            }

            $message = [
                'required' => ':attribute ' . lang('should not be empty', $this->translations),
                'image' => ':attribute ' . lang('must be an image', $this->translations),
                'max' => ':attribute ' . lang('may not be greater than #item', $this->translations, ['#item' => '2MB'])
            ];

            $names = [
                'app_name' => ucwords(lang('application name', $this->translations)),
                'app_version' => ucwords(lang('application version', $this->translations)),
                'app_copyright_year' => ucwords(lang('application copyright year', $this->translations)),
                'app_url_site' => ucwords(lang('application URL', $this->translations)),
                'app_skin' => ucwords(lang('application theme', $this->translations)),
                'app_info' => ucwords(lang('info', $this->translations)),
                'meta_keywords' => ucwords(lang('meta keywords', $this->translations)),
                'meta_title' => ucwords(lang('meta title', $this->translations)),
                'meta_description' => ucwords(lang('meta description', $this->translations)),
                'meta_author' => ucwords(lang('meta author', $this->translations)),
                'og_type' => ucwords(lang('open graph type', $this->translations)),
                'og_site_name' => ucwords(lang('open graph site name', $this->translations)),
                'og_title' => ucwords(lang('open graph title', $this->translations)),
                'og_description' => ucwords(lang('open graph description', $this->translations)),
                'twitter_card' => ucwords(lang('twitter card', $this->translations)),
                'app_favicon' => ucwords(lang('favicon', $this->translations)),
                'app_logo' => ucwords(lang('application logo image', $this->translations)),
                'og_image' => ucwords(lang('open graph image', $this->translations))
            ];
            $this->validate($request, $validation, $message, $names);

            DB::beginTransaction();
            try {
                // DB PROCESS BELOW

                // get existing data
                $data = config::first();

                if (!$data) {
                    $data = new config();
                }

                // store data before updated
                $value_before = $data->toJson();

                // HELPER VALIDATION FOR PREVENT SQL INJECTION & XSS ATTACK
                $app_name = Helper::validate_input_text($request->app_name);
                if (!$app_name) {
                    return back()
                        ->withInput()
                        ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('application name', $this->translations))]));
                }
                $data->app_name = $app_name;

                $app_version = Helper::validate_input_text($request->app_version);
                if (!$app_version) {
                    return back()
                        ->withInput()
                        ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('application version', $this->translations))]));
                }
                $data->app_version = $app_version;

                $app_copyright_year = (int) $request->app_copyright_year;
                if (strlen($app_copyright_year) != 4) {
                    $app_copyright_year = date('Y');
                }
                $data->app_copyright_year = $app_copyright_year;

                $app_url_site = Helper::validate_input_url($request->app_url_site);
                if (!$app_url_site) {
                    return back()
                        ->withInput()
                        ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('application URL', $this->translations))]));
                }
                $data->app_url_site = $app_url_site;

                $data->app_url_main = Helper::validate_input_url($request->app_url_main);

                // IF UPLOAD NEW IMAGE
                if ($request->file('app_favicon')) {
                    // PROCESSING IMAGE
                    $dir_path = 'uploads/config/';
                    $image_file = $request->file('app_favicon');
                    $format_image_name = 'favicon-' . time();
                    $image = Helper::upload_image($dir_path, $image_file, true, $format_image_name, ['ico', 'png', 'jpg', 'jpeg']);
                    if ($image['status'] != 'true') {
                        return back()
                            ->withInput()
                            ->with('error', lang($image['message'], $this->translations, $image['dynamic_objects']));
                    }
                    // GET THE UPLOADED IMAGE RESULT
                    $data->app_favicon = $dir_path . $image['data'];
                }

                // IF UPLOAD NEW IMAGE
                if ($request->file('app_logo')) {
                    // PROCESSING IMAGE
                    $dir_path = 'uploads/config/';
                    $image_file = $request->file('app_logo');
                    $format_image_name = Helper::generate_slug($app_name) . '-' . time();
                    $image = Helper::upload_image($dir_path, $image_file, true, $format_image_name);
                    if ($image['status'] != 'true') {
                        return back()
                            ->withInput()
                            ->with('error', lang($image['message'], $this->translations, $image['dynamic_objects']));
                    }
                    // GET THE UPLOADED IMAGE RESULT
                    $data->app_logo = $dir_path . $image['data'];
                }

                $app_info = Helper::validate_input_text($request->app_info);
                if (!$app_info) {
                    return back()
                        ->withInput()
                        ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('info', $this->translations))]));
                }
                $data->app_info = $app_info;

                $data->app_skin = Helper::validate_input_word($request->app_skin);

                $bg_images_existing = json_decode($data->bg_images);
                $bg_images_new = [];
                foreach ($input_names_bg_images as $input_name) {
                    // IF UPLOAD NEW IMAGE
                    if ($request->file('bg_img_' . $input_name)) {
                        // PROCESSING IMAGE
                        $dir_path = 'uploads/config/';
                        $image_file = $request->file('bg_img_' . $input_name);
                        $format_image_name = 'bg_image_' . $input_name . '-' . time();
                        $image = Helper::upload_image($dir_path, $image_file, true, $format_image_name, ['png', 'jpg', 'jpeg']);
                        if ($image['status'] != 'true') {
                            return back()
                                ->withInput()
                                ->with('error', lang($image['message'], $this->translations, $image['dynamic_objects']));
                        }
                        // GET THE UPLOADED IMAGE RESULT
                        $bg_images_new[$input_name] = $dir_path . $image['data'];
                    } elseif (isset($bg_images_existing->$input_name)) {
                        $bg_images_new[$input_name] = $bg_images_existing->$input_name;
                    }
                }
                $data->bg_images = json_encode($bg_images_new);

                $data->powered_by = Helper::validate_input_text($request->powered_by);

                if ($request->powered_by_url) {
                    $powered_by_url = Helper::validate_input_url($request->powered_by_url);
                    if (!$powered_by_url) {
                        return back()
                            ->withInput()
                            ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('powered by URL', $this->translations))]));
                    }
                    $data->powered_by_url = $powered_by_url;
                } else {
                    $data->powered_by_url = null;
                }

                // === meta data ===

                $meta_title = Helper::validate_input_text($request->meta_title);
                if (!$meta_title) {
                    return back()
                        ->withInput()
                        ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('meta title', $this->translations))]));
                }
                $data->meta_title = $meta_title;

                $meta_description = Helper::validate_input_text($request->meta_description);
                if (!$meta_description) {
                    return back()
                        ->withInput()
                        ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('meta description', $this->translations))]));
                }
                $data->meta_description = $meta_description;

                $meta_keywords = Helper::validate_input_text($request->meta_keywords);
                if (!$meta_keywords) {
                    return back()
                        ->withInput()
                        ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('meta keywords', $this->translations))]));
                }
                $data->meta_keywords = $meta_keywords;

                $meta_author = Helper::validate_input_text($request->meta_author);
                if (!$meta_author) {
                    return back()
                        ->withInput()
                        ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('meta author', $this->translations))]));
                }
                $data->meta_author = $meta_author;

                // Open Graph
                $og_type = Helper::validate_input_text($request->og_type);
                $data->og_type = $og_type;

                $og_site_name = Helper::validate_input_text($request->og_site_name);
                $data->og_site_name = $og_site_name;

                $og_title = Helper::validate_input_text($request->og_title);
                $data->og_title = $og_title;

                // IF UPLOAD NEW IMAGE
                if ($request->file('og_image')) {
                    // PROCESSING IMAGE
                    $dir_path = 'uploads/config/';
                    $image_file = $request->file('og_image');
                    $format_image_name = Helper::generate_slug($app_name) . '-og-' . time();
                    $image = Helper::upload_image($dir_path, $image_file, true, $format_image_name);
                    if ($image['status'] != 'true') {
                        return back()
                            ->withInput()
                            ->with('error', lang($image['message'], $this->translations, $image['dynamic_objects']));
                    }
                    // GET THE UPLOADED IMAGE RESULT
                    $data->og_image = $dir_path . $image['data'];
                }

                $og_description = Helper::validate_input_text($request->og_description);
                $data->og_description = $og_description;

                // Twitter OG
                $twitter_card = Helper::validate_input_text($request->twitter_card);
                $data->twitter_card = $twitter_card;

                $twitter_site = Helper::validate_input_text($request->twitter_site);
                $data->twitter_site = $twitter_site;

                $twitter_site_id = Helper::validate_input_text($request->twitter_site_id);
                $data->twitter_site_id = $twitter_site_id;

                $twitter_creator = Helper::validate_input_text($request->twitter_creator);
                $data->twitter_creator = $twitter_creator;

                $twitter_creator_id = Helper::validate_input_text($request->twitter_creator_id);
                $data->twitter_creator_id = $twitter_creator_id;

                // FB
                $fb_app_id = Helper::validate_input_text($request->fb_app_id);
                $data->fb_app_id = $fb_app_id;

                // reCAPTCHA
                $data->recaptcha_site_key_admin = $request->recaptcha_site_key_admin;
                $data->recaptcha_secret_key_admin = $request->recaptcha_secret_key_admin;
                $data->recaptcha_site_key_public = $request->recaptcha_site_key_public;
                $data->recaptcha_secret_key_public = $request->recaptcha_secret_key_public;

                $data->secure_login = (int) $request->secure_login;
                $data->login_trial = (int) $request->login_trial;

                $data->header_script = $request->header_script;
                $data->body_script = $request->body_script;
                $data->footer_script = $request->footer_script;

                $data->save();

                // logging
                $value_after = $data->toJson();
                if ($value_before != $value_after) {
                    $log_detail_id = 7; // update
                    $module_id = $this->module_id;
                    $target_id = $data->id;
                    $note = null;
                    $ip_address = $request->ip();
                    Helper::logging($log_detail_id, $module_id, $target_id, $note, $value_before, $value_after, $ip_address);
                }

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

            # SUCCESS
            return redirect()
                ->route('admin.config')
                ->with('success', lang('#item has been successfully updated', $this->translations, ['#item' => ucwords(lang('application configuration', $this->translations))]));
        } else {
            // AUTHORIZING...
            $authorize = Helper::authorizing($this->module, 'View');
            if ($authorize['status'] != 'true') {
                return back()->with('error', $authorize['message']);
            }

            $data = config::first();

            if ($data->bg_images) {
                $bg_images_existing = json_decode($data->bg_images);
                foreach ($bg_images_existing as $key => $value) {
                    $bg_image_label = 'bg_img_' . $key;
                    $data->$bg_image_label = $value;
                }
            }

            return view('admin.core.config.index', compact('data'));
        }
    }

    public function export(Request $request)
    {
        // logging
        $log_detail_id = 11; // export data
        $module_id = $this->module_id;
        $target_id = null;
        $note = null;
        $value_before = null;
        $value_after = null;
        $ip_address = $request->ip();
        Helper::logging($log_detail_id, $module_id, $target_id, $note, $value_before, $value_after, $ip_address);

        // SET FILE NAME
        $filename = date('YmdHis') . '_' . $this->module . '_' . $this->global_config->app_name;

        return Excel::download(new ConfigExportView, $filename . '.xlsx');
    }

    public function import(Request $request)
    {
        // SET THIS OBJECT/ITEM NAME BASED ON TRANSLATION
        $this->item = ucwords(lang($this->item, $this->translations));

        // LARAVEL VALIDATION
        $this->validate($request, [
            'file' => 'required|mimes:csv,xls,xlsx'
        ]);

        // GET THE UPLOADED FILE
        $file = $request->file('file');

        // RENAME THE FILE
        $filename = date('YmdHis') . '_' . $file->getClientOriginalName();

        // SET PATH
        $dir_path = 'uploads/tmp';
        $destination_path = public_path($dir_path);

        // UPLOAD TO THE DESTINATION PATH ($dir_path) IN PUBLIC FOLDER
        if ($file->move($destination_path, $filename)) {
            // store data before updated
            $value_before = config::first()->toJson();

            $file_local_path = public_path($dir_path . '/' . $filename);

            // IMPORT DATA
            Excel::import(new ConfigImport, $file_local_path);

            // logging
            $log_detail_id = 12; // import data
            $module_id = $this->module_id;
            $target_id = null;
            $note = null;
            $value_after = config::first()->toJson();;
            $ip_address = $request->ip();
            Helper::logging($log_detail_id, $module_id, $target_id, $note, $value_before, $value_after, $ip_address);

            // remove the uploaded file
            $uploaded_file_path = $file_local_path;
            if (file_exists($uploaded_file_path)) {
                unlink($uploaded_file_path);
            }

            // SUCCESS
            return redirect()
                ->route('admin.config')
                ->with('success', lang('Successfully imported #item', $this->translations, ['#item' => $this->item]));
        }

        // FAILED
        return back()
            ->withInput()
            ->with('error', lang('Oops, failed to imported #item. Please try again.', $this->translations, ['#item' => $this->item]));
    }
}
