<?php

namespace App\Http\Controllers\Admin\Core;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Yajra\Datatables\Datatables;

// Libraries
use App\Libraries\Helper;

// Models
use App\Models\admin;
use App\Models\admin_group;
use App\Models\admin_group_member;
use App\Models\log;
use App\Models\log_detail;
use App\Models\module;
use App\Models\language;
use App\Models\country;

class AdminController extends Controller
{
    // SET THIS MODULE
    private $module = 'Administrator';
    private $module_id = 7;

    // SET THIS OBJECT/ITEM NAME
    private $item = 'administrator';

    /**
     * Change country.
     *
     * @return view()
     */
    public function change_country($alias)
    {
        try {
            // update session
            Session::put('country_used', $alias);

            // get table name
            $language_table = (new language())->getTable();
            $country_table = (new country())->getTable();

            $new_language_used = language::select(
                $language_table . '.*'
            )
                ->leftJoin($country_table, $language_table . '.country_id', $country_table . '.id')
                ->where($country_table . '.country_alias', $alias)
                ->orderBy($language_table . '.ordinal')
                ->first();

            if ($new_language_used) {
                $this->change_language($new_language_used->alias);
            } else {
                // remove session "sio_languages" & "sio_translations", so Controller can reset the value for that session
                Session::forget('sio_languages');
                Session::forget('sio_translations');
            }
        } catch (\Exception $ex) {
            $error_msg = $ex->getMessage() . ' in ' . $ex->getFile() . ' at line ' . $ex->getLine();
            Helper::error_logging($error_msg, $this->module_id);

            if (env('APP_DEBUG') == false) {
                $error_msg = lang('Oops, something went wrong please try again later.', $this->translations);
            }

            # ERROR
            return back()
                ->with('error', $error_msg);
        }

        # SUCCESS
        return redirect()->back();
    }

    /**
     * Change language.
     *
     * @return view()
     */
    public function change_language($alias)
    {
        try {
            // update session
            Session::put('language_used', $alias);

            // remove session "sio_languages" & "sio_translations", so Controller can reset the value for that session
            Session::forget('sio_languages');
            Session::forget('sio_translations');
        } catch (\Exception $ex) {
            $error_msg = $ex->getMessage() . ' in ' . $ex->getFile() . ' at line ' . $ex->getLine();
            Helper::error_logging($error_msg, $this->module_id);

            if (env('APP_DEBUG') == false) {
                $error_msg = lang('Oops, something went wrong please try again later.', $this->translations);
            }

            # ERROR
            return back()
                ->with('error', $error_msg);
        }

        # SUCCESS
        return redirect()->back();
    }

    /**
     * Display user profile data & update it.
     *
     * @return view()
     */
    public function profile(Request $request)
    {
        if ($request->isMethod('post')) {

            // LARAVEL VALIDATION
            $validation = [
                'firstname' => 'required',
                'lastname' => 'required',
                'email' => 'required|email'
            ];

            // if upload avatar
            if ($request->file('avatar_with_path')) {
                $validation['avatar_with_path'] = 'required|image|max:2048';
            }

            $message = [
                'required' => ':attribute ' . lang('should not be empty', $this->translations),
                'image' => ':attribute ' . lang('must be an image', $this->translations),
                'max' => ':attribute ' . lang('may not be greater than #item', $this->translations, ['#item' => '2MB']),
            ];
            $names = [
                'firstname' => ucwords(lang('firstname', $this->translations)),
                'lastname' => ucwords(lang('lastname', $this->translations)),
                'email' => ucwords(lang('email', $this->translations)),
                'avatar_with_path' => ucwords(lang('avatar', $this->translations)),
            ];
            $this->validate($request, $validation, $message, $names);

            DB::beginTransaction();
            try {
                // DB PROCESS BELOW

                // get data based on session
                $data = admin::find(Session::get(env('SESSION_ADMIN_NAME', 'sysadmin'))->id);

                // store data before updated
                $value_before = $data->toJson();

                // HELPER VALIDATION FOR PREVENT SQL INJECTION & XSS ATTACK
                $firstname = Helper::validate_input_text($request->firstname);
                if (!$firstname) {
                    return back()
                        ->withInput()
                        ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('firstname', $this->translations))]));
                }
                $data->firstname = Helper::generate_token($firstname);

                $lastname = Helper::validate_input_text($request->lastname);
                if (!$lastname) {
                    return back()
                        ->withInput()
                        ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('lastname', $this->translations))]));
                }
                $data->lastname = Helper::generate_token($lastname);

                $email = Helper::validate_input_email($request->email);
                if (!$email) {
                    return back()
                        ->withInput()
                        ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('email', $this->translations))]));
                }
                $data->email = Helper::generate_token($email);

                if ($request->phone) {
                    $data->phone = Helper::generate_token($request->phone);
                } else {
                    $data->phone = null;
                }

                if ($request->file('avatar_with_path')) {
                    // UPLOAD IMAGE
                    $dir_path = 'uploads/avatar/';
                    $image_file = $request->file('avatar_with_path');
                    $format_image_name = $data->id . '-' . time() . '-avatar';
                    $allowed_extensions = ['jpeg', 'jpg', 'png', 'gif'];
                    $generate_thumbnail = true;
                    $thumbnail_width = 200;
                    $thumbnail_height = 200;
                    $thumbnail_quality_percentage = 80;
                    $image = Helper::upload_image($dir_path, $image_file, true, $format_image_name, $allowed_extensions, $generate_thumbnail, $thumbnail_width, $thumbnail_height, $thumbnail_quality_percentage);
                    if ($image['status'] != 'true') {
                        # FAILED TO UPLOAD IMAGE
                        return back()
                            ->withInput()
                            ->with('error', lang($image['message'], $this->translations, $image['dynamic_objects']));
                    }

                    $data->avatar_path = $dir_path;
                    $data->avatar = $image['thumbnail'];
                }

                // if delete existing image
                if ($request->avatar_with_path_delete == 'yes') {
                    $data->avatar_path = null;
                    $data->avatar = null;
                }

                $data->save();

                // logging
                $value_after = $data->toJson();
                if ($value_before != $value_after) {
                    $log_detail_id = 3; // edit profile
                    $module_id = null;
                    $target_id = null;
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

            // setup avatar source
            if ($data->avatar) {
                $data->avatar_with_path = $data->avatar_path . $data->avatar;
            }

            // set fullname by merging firstname & lastname
            $data->fullname = $firstname . ' ' . $lastname;

            // update data to session
            Session::put(env('SESSION_ADMIN_NAME', 'sysadmin'), $data);

            # SUCCESS
            return redirect()
                ->route('admin.profile')
                ->with('success', lang('#item has been updated successfully', $this->translations, ['#item' => ucwords(lang('profile', $this->translations))]));
        } else {

            $admin_id = Session::get(env('SESSION_ADMIN_NAME', 'sysadmin'))->id;

            $data = admin::find($admin_id);

            // decrypt the value
            $data->firstname = Helper::validate_token($data->firstname);
            $data->lastname = Helper::validate_token($data->lastname);
            $data->email = Helper::validate_token($data->email);
            if ($data->phone) {
                $data->phone = Helper::validate_token($data->phone);
            }

            // set fullname
            $data->fullname = $data->firstname . ' ' . $data->lastname;

            // setup avatar source
            if ($data->avatar) {
                $data->avatar_with_path = $data->avatar_path . $data->avatar;
            }

            // get recent activity
            $table_log = (new log())->getTable();
            $table_log_detail = (new log_detail())->getTable();
            $table_module = (new module())->getTable();

            $logs = log::select(
                $table_log . '.*',
                $table_log_detail . '.action',
                $table_module . '.name AS module_name'
            )
                ->leftJoin($table_log_detail, $table_log . '.log_detail_id', '=', $table_log_detail . '.id')
                ->leftJoin($table_module, $table_log . '.module_id', '=', $table_module . '.id')
                ->where($table_log . '.admin_id', $admin_id)
                ->orderBy($table_log . '.id', 'desc')
                ->limit(5)
                ->get();

            $last_login = log::where('admin_id', $admin_id)->where('log_detail_id', 1)->orderBy('id', 'desc')->first();

            return view('admin.core.profile', compact('data', 'logs', 'last_login'));
        }
    }

    /**
     * Change password.
     *
     * @return view()
     */
    public function change_password(Request $request)
    {
        // LARAVEL VALIDATION
        $validation = [
            'current_pass' => 'required',
            'new_pass' => [
                'required',
                'confirmed',
                'string',
                'min:8',                                // must be at least 8 characters in length
                'regex:/[a-z]/',                        // must contain at least one lowercase letter
                'regex:/[A-Z]/',                        // must contain at least one uppercase letter
                'regex:/[0-9]/',                        // must contain at least one digit numeric
                'regex:/[?!@#$%^&*~`_+=:;.,"><\'-]/',   // must contain a special character
            ]
        ];
        $message = [
            'required' => ':attribute ' . lang('should not be empty', $this->translations),
            'confirmed' => ':attribute ' . lang('confirmation does not match', $this->translations),
            'min' => ':attribute ' . lang('must be at least #item characters', $this->translations, ['#item' => '8']),
            'regex' => ':attribute ' . lang('format is invalid', $this->translations),
        ];
        $names = [
            'current_pass' => ucwords(lang('current #item', $this->translations, ['#item' => ucwords(lang('password', $this->translations))])),
            'new_pass' => ucwords(lang('new #item', $this->translations, ['#item' => ucwords(lang('password', $this->translations))])),
        ];
        $this->validate($request, $validation, $message, $names);

        DB::beginTransaction();
        try {
            // DB PROCESS BELOW

            // get data based on session
            $data = admin::find(Session::get(env('SESSION_ADMIN_NAME', 'sysadmin'))->id);

            // verify current password first
            $current_password = $request->input('current_pass');
            $old_pass_hash = $data->password;
            if (!password_verify($current_password, $old_pass_hash)) {
                return back()
                    ->withInput()
                    ->with('error', lang('#item is incorrect', $this->translations, ['#item' => ucwords(lang('password', $this->translations))]));
            }

            // update the password
            $data->password = Helper::hashing_this($request->new_pass);
            $new_pass_hash = $data->password;

            $data->save();

            // logging
            $log_detail_id = 4; // change password
            $module_id = null;
            $target_id = null;
            $note = null;
            $value_before = $old_pass_hash;
            $value_after = $new_pass_hash;
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

        # SUCCESS
        return redirect()
            ->route('admin.profile')
            ->with('success', lang('#item has been updated successfully', $this->translations, ['#item' => ucwords(lang('password', $this->translations))]));
    }

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

        return view('admin.core.administrator.list');
    }

    /**
     * Get a listing of the resource using DataTables.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_data(Datatables $datatables, Request $request)
    {
        $query = admin::where('username', '!=', 'superuser');

        return $datatables->eloquent($query)
            ->addColumn('action', function ($data) {
                $object_id = $data->id;
                if (env('CRYPTOGRAPHY_MODE', false)) {
                    $object_id = Helper::generate_token($data->id);
                }

                $wording_edit = ucwords(lang('edit', $this->translations));
                $html = '<a href="' . route('admin.user_admin.edit', $object_id) . '" class="btn btn-xs btn-primary" title="' . $wording_edit . '"><i class="fa fa-pencil"></i>&nbsp; ' . $wording_edit . '</a>';

                $wording_delete = ucwords(lang('delete', $this->translations));
                $html .= '<form action="' . route('admin.user_admin.delete') . '" method="POST" onsubmit="return confirm(\'' . lang('Are you sure to delete this #item?', $this->translations, ['#item' => $this->item]) . '\');" style="display: inline"> ' . csrf_field() . ' <input type="hidden" name="id" value="' . $object_id . '">
                <button type="submit" class="btn btn-xs btn-danger" title="' . $wording_delete . '"><i class="fa fa-trash"></i>&nbsp; ' . $wording_delete . '</button></form>';

                return $html;
            })
            ->addColumn('status', function ($data) {
                if ($data->status != 1) {
                    $html = '<span class="label label-danger"><i class="fa fa-times-circle"></i>&nbsp; <i>' . ucwords(lang('inactive', $this->translations)) . '</i></span>';
                    if ($data->remarks) {
                        $html .= '&nbsp; <span class="label label-info" style="cursor:default;" data-html="true" data-toggle="tooltip" data-placement="right" title="' . str_replace("\r\n", "<br />", $data->remarks) . '"><i class="fa fa-info-circle"></i>&nbsp; ' . ucwords(lang('remarks', $this->translations)) . '</span>';
                    }
                    return $html;
                }
                return '<span class="label label-success"><i class="fa fa-check-circle"></i>&nbsp; ' . ucwords(lang('active', $this->translations)) . '</span>';
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

        $groups = Helper::get_admin_groups();

        return view('admin.core.administrator.form', compact('groups'));
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

        // get table name
        $admin_table = (new admin())->getTable();

        // LARAVEL VALIDATION
        $validation = [
            'firstname' => 'required',
            'lastname' => 'required',
            'username' => 'required|unique:' . $admin_table . ',username',
            'email' => 'required|unique:' . $admin_table . ',email|email',
            'password' => [
                'required',
                'confirmed',
                'string',
                'min:8',                                // must be at least 8 characters in length
                'regex:/[a-z]/',                        // must contain at least one lowercase letter
                'regex:/[A-Z]/',                        // must contain at least one uppercase letter
                'regex:/[0-9]/',                        // must contain at least one digit numeric
                'regex:/[?!@#$%^&*~`_+=:;.,"><\'-]/',   // must contain a special character
            ],
            'group' => 'required|integer',
        ];
        $message = [
            'required' => ':attribute ' . lang('should not be empty', $this->translations),
            'unique' => ':attribute ' . lang('has already been taken, please input another data', $this->translations),
            'email' => ':attribute ' . lang('must be a valid email address', $this->translations),
            'confirmed' => ':attribute ' . lang('confirmation does not match', $this->translations),
            'min' => ':attribute ' . lang('must be at least #item characters', $this->translations, ['#item' => '8']),
            'regex' => ':attribute ' . lang('format is invalid', $this->translations),
            'integer' => ':attribute ' . lang('must be an integer', $this->translations),
        ];
        $names = [
            'firstname' => ucwords(lang('firstname', $this->translations)),
            'lastname' => ucwords(lang('lastname', $this->translations)),
            'username' => ucwords(lang('username', $this->translations)),
            'email' => ucwords(lang('email', $this->translations)),
            'password' => ucwords(lang('password', $this->translations)),
            'group' => ucwords(lang('group', $this->translations)),
            'phone' => ucwords(lang('phone', $this->translations)),
        ];
        $this->validate($request, $validation, $message, $names);

        DB::beginTransaction();
        try {
            // DB PROCESS BELOW

            // HELPER VALIDATION FOR PREVENT SQL INJECTION & XSS ATTACK
            $firstname = Helper::validate_input_text($request->firstname);
            if (!$firstname) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => $names['firstname']]));
            }

            $lastname = Helper::validate_input_text($request->lastname);
            if (!$lastname) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => $names['lastname']]));
            }

            $username = Helper::validate_input_word($request->username);
            if (!$username) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => $names['username']]));
            }

            $email = Helper::validate_input_email($request->email);
            if (!$email) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => $names['email']]));
            }

            $phone = $request->phone;
            if ($phone) {
                // check unique value
                $exist = admin::where('phone', $phone)->count();
                if ($exist) {
                    return back()
                        ->withInput()
                        ->with('error', $names['phone'] . ' ' . lang('has already been taken, please input another data', $this->translations));
                }
            }

            $group = (int) $request->group;
            if (!$group) {
                return back()
                    ->withInput()
                    ->with('error', lang('#item must be chosen at least one', $this->translations, ['#item' => $names['group']]));
            }

            // SAVE THE DATA
            $data = new admin();
            $data->firstname = Helper::generate_token($firstname);
            $data->lastname = Helper::generate_token($lastname);
            $data->username = $username;
            $data->password = Helper::hashing_this($request->password);
            $data->email = Helper::generate_token($email);
            if ($phone) {
                $data->phone = Helper::generate_token($phone);
            }
            $data->remarks = Helper::validate_input_text($request->remarks);
            $data->status = (int) $request->status;
            $data->save();

            // save user's group
            $admin_group_member = new admin_group_member();
            $admin_group_member->admin_id = $data->id;
            $admin_group_member->admin_group_id = $group;
            $admin_group_member->save();

            // add user's group in log data
            $data->group = $group;

            // logging
            $log_detail_id = 5; // add new
            $module_id = $this->module_id;
            $target_id = $data->id;
            $note = '"' . $username . '"';
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
        $redirect_url = 'admin.user_admin';
        if ($request->stay_on_page) {
            $redirect_url = 'admin.user_admin.create';
        }

        # SUCCESS
        return redirect()
            ->route($redirect_url)
            ->with('success', lang('Successfully added a new #item : #name', $this->translations, ['#item' => $this->item, '#name' => $username]));
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
                ->route('admin.user_admin')
                ->with('error', lang('Invalid #item ID, please check your link again', $this->translations, ['#item' => $this->item]));
        }

        // check if the user session is the same as the user you want to change
        if (Session::get(env('SESSION_ADMIN_NAME', 'sysadmin'))->id == $id) {
            // redirect to user profile page
            return redirect()
                ->route('admin.profile');
        }

        // get table name
        $admin_table = (new admin())->getTable();
        $admin_group_member_table = (new admin_group_member())->getTable();

        // GET DATA BY ID
        $data = admin::select(
            $admin_table . '.*',
            $admin_group_member_table . '.admin_group_id AS group'
        )
            ->leftJoin($admin_group_member_table, $admin_table . '.id', $admin_group_member_table . '.admin_id')
            ->where($admin_table . '.id', $id)
            ->first();

        // CHECK IS DATA FOUND
        if (!$data) {
            # FAILED - DATA NOT FOUND
            return redirect()
                ->route('admin.user_admin')
                ->with('error', lang('#item not found, please check your link again', $this->translations, ['#item' => $this->item]));
        }

        // decrypt the value
        $data->firstname = Helper::validate_token($data->firstname);
        $data->lastname = Helper::validate_token($data->lastname);
        $data->email = Helper::validate_token($data->email);
        if ($data->phone) {
            $data->phone = Helper::validate_token($data->phone);
        }

        $groups = Helper::get_admin_groups();

        return view('admin.core.administrator.form', compact('data', 'raw_id', 'groups'));
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

        // LARAVEL VALIDATION
        $validation = [
            'firstname' => 'required',
            'lastname' => 'required',
            'username' => 'required',
            'email' => 'required|email',
            'group' => 'required|integer',
        ];
        $message = [
            'required' => ':attribute ' . lang('should not be empty', $this->translations),
            'email' => ':attribute ' . lang('must be a valid email address', $this->translations),
            'integer' => ':attribute ' . lang('must be an integer', $this->translations),
        ];
        $names = [
            'firstname' => ucwords(lang('firstname', $this->translations)),
            'lastname' => ucwords(lang('lastname', $this->translations)),
            'username' => ucwords(lang('username', $this->translations)),
            'email' => ucwords(lang('email', $this->translations)),
            'password' => ucwords(lang('password', $this->translations)),
            'group' => ucwords(lang('group', $this->translations)),
            'phone' => ucwords(lang('phone', $this->translations)),
        ];
        $this->validate($request, $validation, $message, $names);

        $raw_id = $id;

        if (env('CRYPTOGRAPHY_MODE', false)) {
            $id = Helper::validate_token($id);
        }

        // get table name
        $admin_table = (new admin())->getTable();
        $admin_group_member_table = (new admin_group_member())->getTable();

        // GET DATA BY ID
        $data = admin::select(
            $admin_table . '.*',
            $admin_group_member_table . '.admin_group_id AS group'
        )
            ->leftJoin($admin_group_member_table, $admin_table . '.id', $admin_group_member_table . '.admin_id')
            ->where($admin_table . '.id', $id)
            ->first();

        // CHECK IS DATA FOUND
        if (!$data) {
            # FAILED - DATA NOT FOUND
            return back()
                ->withInput()
                ->with('error', lang('#item not found, please reload your page before resubmit', $this->translations, ['#item' => $this->item]));
        }

        // store data before updated
        $value_before = $data->toJson();

        // unset attributes: group
        unset($data->group);

        // get table name
        $admin_table = (new admin())->getTable();

        DB::beginTransaction();
        try {
            // DB PROCESS BELOW

            // HELPER VALIDATION FOR PREVENT SQL INJECTION & XSS ATTACK
            $firstname = Helper::validate_input_text($request->firstname);
            if (!$firstname) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => $names['firstname']]));
            }

            $lastname = Helper::validate_input_text($request->lastname);
            if (!$lastname) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => $names['lastname']]));
            }

            $username = Helper::validate_input_word($request->username);
            if (!$username) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => $names['username']]));
            }

            // check whether the value entered is different from the existing data
            if ($data->username != $username) {
                // check unique value
                $exist = admin::where('username', $username)->count();
                if ($exist) {
                    return back()
                        ->withInput()
                        ->with('error', $names['username'] . ' ' . lang('has already been taken, please input another data', $this->translations));
                }
            }

            $email = Helper::validate_input_email($request->email);
            if (!$email) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => $names['email']]));
            }

            // check whether the value entered is different from the existing data
            if ($data->email != $email) {
                // check unique value
                $exist = admin::where('email', $email)->count();
                if ($exist) {
                    return back()
                        ->withInput()
                        ->with('error', $names['email'] . ' ' . lang('has already been taken, please input another data', $this->translations));
                }
            }

            $phone = $request->phone;
            if ($phone) {
                // check whether the value entered is different from the existing data
                if ($data->phone != $phone) {
                    // check unique value
                    $exist = admin::where('phone', $phone)->count();
                    if ($exist) {
                        return back()
                            ->withInput()
                            ->with('error', $names['phone'] . ' ' . lang('has already been taken, please input another data', $this->translations));
                    }
                }
            }

            $group = (int) $request->group;
            if (!$group) {
                return back()
                    ->withInput()
                    ->with('error', lang('#item must be chosen at least one', $this->translations, ['#item' => $names['group']]));
            }

            // SAVE THE DATA
            $data->firstname = Helper::generate_token($firstname);
            $data->lastname = Helper::generate_token($lastname);
            $data->username = $username;
            $data->email = Helper::generate_token($email);
            if ($phone) {
                $data->phone = Helper::generate_token($phone);
            }
            $data->remarks = Helper::validate_input_text($request->remarks);
            $data->status = (int) $request->status;
            $data->save();

            // delete existing user's group first
            admin_group_member::where('admin_id', $data->id)->delete();

            // save user's group
            $admin_group_member = new admin_group_member();
            $admin_group_member->admin_id = $data->id;
            $admin_group_member->admin_group_id = $group;
            $admin_group_member->save();

            // add user's group in log data
            $data->group = $group;

            // logging
            $value_after = $data->toJson();
            if ($value_before != $value_after) {
                $log_detail_id = 7; // update
                $module_id = $this->module_id;
                $target_id = $data->id;
                $note = '"' . $username . '"';
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
        $success_message = lang('Successfully updated #item : #name', $this->translations, ['#item' => $this->item, '#name' => $username]);
        if ($request->stay_on_page) {
            return redirect()
                ->route('admin.user_admin.edit', $raw_id)
                ->with('success', $success_message);
        } else {
            return redirect()
                ->route('admin.user_admin')
                ->with('success', $success_message);
        }
    }

    /**
     * Reset password.
     *
     * @return view()
     */
    public function reset_password($id, Request $request)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'Edit');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        // LARAVEL VALIDATION
        $validation = [
            'new_pass' => [
                'required',
                'confirmed',
                'string',
                'min:8',                                // must be at least 8 characters in length
                'regex:/[a-z]/',                        // must contain at least one lowercase letter
                'regex:/[A-Z]/',                        // must contain at least one uppercase letter
                'regex:/[0-9]/',                        // must contain at least one digit numeric
                'regex:/[?!@#$%^&*~`_+=:;.,"><\'-]/',   // must contain a special character
            ]
        ];
        $message = [
            'required' => ':attribute ' . lang('should not be empty', $this->translations),
            'confirmed' => ':attribute ' . lang('confirmation does not match', $this->translations),
            'min' => ':attribute ' . lang('must be at least #item characters', $this->translations, ['#item' => '8']),
            'regex' => ':attribute ' . lang('format is invalid', $this->translations),
        ];
        $names = [
            'new_pass' => ucwords(lang('new #item', $this->translations, ['#item' => ucwords(lang('password', $this->translations))])),
        ];
        $this->validate($request, $validation, $message, $names);

        $raw_id = $id;

        if (env('CRYPTOGRAPHY_MODE', false)) {
            $id = Helper::validate_token($id);
        }

        DB::beginTransaction();
        try {
            // DB PROCESS BELOW

            // get data based on session
            $data = admin::find($id);

            $old_pass_hash = $data->password;

            // update the password
            $data->password = Helper::hashing_this($request->new_pass);
            $new_pass_hash = $data->password;

            $data->save();

            // logging
            $log_detail_id = 10; // reset password
            $module_id = $this->module_id;
            $target_id = $data->id;
            $note = '"' . $data->username . '"';
            $value_before = $old_pass_hash;
            $value_after = $new_pass_hash;
            $ip_address = $request->ip();
            Helper::logging($log_detail_id, $module_id, $target_id, $note, $value_before, $value_after, $ip_address);

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
        return redirect()
            ->route('admin.user_admin.edit', $raw_id)
            ->with('success', lang('#item has been reset successfully', $this->translations, ['#item' => ucwords(lang('password', $this->translations))]));
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
        $data = admin::find($id);

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
            $note = '"' . $data->username . '"';
            $value_after = $data;
            $ip_address = $request->ip();
            Helper::logging($log_detail_id, $module_id, $target_id, $note, $value_before, $value_after, $ip_address);

            # SUCCESS
            return redirect()
                ->route('admin.user_admin')
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

        return view('admin.core.administrator.list', compact('deleted_data'));
    }

    /**
     * Get a listing of the deleted resource using DataTables.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_deleted_data(Datatables $datatables, Request $request)
    {
        $query = admin::onlyTrashed();

        return $datatables->eloquent($query)
            ->addColumn('action', function ($data) {
                $object_id = $data->id;
                if (env('CRYPTOGRAPHY_MODE', false)) {
                    $object_id = Helper::generate_token($data->id);
                }

                $wording_restore = ucwords(lang('restore', $this->translations));
                return '<form action="' . route('admin.user_admin.restore') . '" method="POST" onsubmit="return confirm(\'' . lang('Are you sure to restore this #item?', $this->translations, ['#item' => $this->item]) . '\');" style="display: inline"> ' . csrf_field() . ' <input type="hidden" name="id" value="' . $object_id . '">
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
        $data = admin::onlyTrashed()->find($id);

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
            $note = '"' . $data->username . '"';
            $value_after = $data->toJson();
            $ip_address = $request->ip();
            Helper::logging($log_detail_id, $module_id, $target_id, $note, $value_before, $value_after, $ip_address);

            # SUCCESS
            return redirect()
                ->route('admin.user_admin.deleted_data')
                ->with('success', lang('Successfully restored #item', $this->translations, ['#item' => $this->item]));
        }

        # FAILED
        return back()
            ->with('error', lang('Oops, failed to restore #item. Please try again.', $this->translations, ['#item' => $this->item]));
    }
}
