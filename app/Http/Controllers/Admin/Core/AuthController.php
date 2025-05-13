<?php

namespace App\Http\Controllers\Admin\Core;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

// Libraries
use App\Libraries\Helper;

// Models
use App\Models\admin;
use App\Models\admin_group;
use App\Models\admin_group_access;
use App\Models\admin_group_branch;
use App\Models\admin_group_member;
use App\Models\module;
use App\Models\module_rule;
use App\Models\office;
use App\Models\office_branch;
use App\Models\blocked_ip;

class AuthController extends Controller
{
    /**
     * Display login page.
     *
     * @return view()
     */
    public function login()
    {
        if (Session::get(env('SESSION_ADMIN_NAME', 'sysadmin'))) {
            return redirect()->route('admin.home');
        }

        return view('admin.core.login');
    }

    /**
     * Login authentication.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return back() / redirect()
     */
    public function login_auth(Request $request)
    {
        // LARAVEL VALIDATION
        $validation = [
            'login_id' => 'required',
            'login_pass' => 'required'
        ];
        $message = [
            'required' => ':attribute ' . lang('should not be empty', $this->translations)
        ];
        $names = [
            'login_id' => ucwords(lang('username', $this->translations)),
            'login_pass' => ucwords(lang('password', $this->translations))
        ];
        $this->validate($request, $validation, $message, $names);

        try {
            // SECURE LOGIN
            if ($this->global_config->secure_login) {
                // check is user's IP address blocked
                $blocked = blocked_ip::withTrashed()
                    ->where('ip_address', Session::get('user_ip_address'))
                    ->orderBy('id', 'desc')
                    ->first();
                if ($blocked) {
                    if (empty($blocked->deleted_at)) {
                        // still blocked
                        # ERROR
                        $block_response = $this->block();
                        return back()
                            ->withInput()
                            ->with('error', $block_response);
                    } else {
                        // after release the block status
                        $diff_hours = (time() - strtotime($blocked->deleted_at)) / 3600;
                        $session_lifetime = env('SESSION_LIFETIME') / 60;
                        if ($diff_hours < $session_lifetime) {
                            Session::put('admin_login_trial', 0);
                        }
                    }
                }

                // setup session 'admin_login_trial'
                if (Session::has('admin_login_trial')) {
                    $admin_login_trial = Session::get('admin_login_trial');
                } else {
                    $admin_login_trial = 0;
                    Session::put('admin_login_trial', $admin_login_trial);
                }

                // get login trial limit
                $admin_login_trial_limit = $this->global_config->login_trial;
                if ($admin_login_trial_limit <= 0) {
                    $admin_login_trial_limit = 1;
                }

                // validate session 'admin_login_trial'
                if ($admin_login_trial >= $admin_login_trial_limit) {
                    # ERROR
                    $block_response = $this->block();
                    return back()
                        ->withInput()
                        ->with('error', $block_response);
                }
            }

            if (!empty($this->global_config->recaptcha_secret_key_admin)) {
                // reCAPTCHA checking...
                $recaptcha_check = Helper::validate_recaptcha($request->input('g-recaptcha-response'), $this->global_config->recaptcha_secret_key_admin);
                if (!$recaptcha_check) {
                    # ERROR - reCAPTCHA FAILED
                    if ($this->global_config->secure_login) {
                        $admin_login_trial++;
                        Session::put('admin_login_trial', $admin_login_trial);
                        // validate session 'admin_login_trial'
                        if ($admin_login_trial >= $admin_login_trial_limit) {
                            # ERROR
                            $block_response = $this->block();
                            return back()
                                ->withInput()
                                ->with('error', $block_response);
                        }
                    }
                    return back()
                        ->withInput()
                        ->with('error', lang('reCAPTCHA validation unsuccessful, please try again', $this->translations));
                }
            }

            // validate the login ID
            $login_id = Helper::validate_input_text($request->login_id);

            // get the password
            $password = $request->login_pass;

            // check is data exist
            $data = admin::where('username', $login_id)->first();

            if (!$data) {
                # ERROR
                if ($this->global_config->secure_login) {
                    $admin_login_trial++;
                    Session::put('admin_login_trial', $admin_login_trial);
                    // validate session 'admin_login_trial'
                    if ($admin_login_trial >= $admin_login_trial_limit) {
                        # ERROR
                        $block_response = $this->block();
                        return back()
                            ->withInput()
                            ->with('error', $block_response);
                    }
                }

                return back()
                    ->withInput()
                    ->with('error', lang('Incorrect credentials, please try again.', $this->translations));
            }

            $hash_password = $data->password;

            // verify the password
            if (!password_verify($password, $hash_password)) {
                # ERROR
                if ($this->global_config->secure_login) {
                    $admin_login_trial++;
                    Session::put('admin_login_trial', $admin_login_trial);
                    // validate session 'admin_login_trial'
                    if ($admin_login_trial >= $admin_login_trial_limit) {
                        # ERROR
                        $block_response = $this->block($data->id);
                        return back()
                            ->withInput()
                            ->with('error', $block_response);
                    }
                }

                return back()
                    ->withInput()
                    ->with('error', lang('Incorrect credentials, please try again.', $this->translations));
            }

            // check is active/inactive data
            if ($data->status == 0) {
                # ERROR
                if ($this->global_config->secure_login) {
                    $admin_login_trial++;
                    Session::put('admin_login_trial', $admin_login_trial);
                    // validate session 'admin_login_trial'
                    if ($admin_login_trial >= $admin_login_trial_limit) {
                        # ERROR
                        $block_response = $this->block($data->id);
                        return back()
                            ->withInput()
                            ->with('error', $block_response);
                    }
                }

                $error_msg = lang('Login failed! Because your account is no longer active.', $this->translations);
                if ($data->remarks) {
                    $error_msg .= '<br>(' . $data->remarks . ')';
                }
                return back()
                    ->withInput()
                    ->with('error', $error_msg);
            }

            // update "force logout" status
            if ($data->force_logout) {
                $data->force_logout = 0;
                $data->save();
            }

            $obj = new \stdClass();
            $obj->id = $data->id;
            $obj->firstname = Helper::validate_token($data->firstname);
            $obj->lastname = Helper::validate_token($data->lastname);
            $obj->username = $data->username;
            $obj->phone = Helper::validate_token($data->phone);
            $obj->avatar = $data->avatar;
            $obj->avatar_path = $data->avatar_path;
            $obj->status = $data->status;
            $obj->force_logout = $data->force_logout;

            // setup avatar source
            $obj->avatar_with_path = $data->avatar_path . $data->avatar;

            // set fullname by merging firstname & lastname
            $obj->fullname = $obj->firstname . ' ' . $obj->lastname;

            // store the admin data to session
            Session::put(env('SESSION_ADMIN_NAME', 'sysadmin'), $obj);
            Session::put('token_auth', Helper::generate_token($hash_password));

            // get table name
            $admin_group_member_table = (new admin_group_member())->getTable();
            $admin_group_branch_table = (new admin_group_branch())->getTable();
            $admin_group_table = (new admin_group())->getTable();
            $office_branch_table = (new office_branch())->getTable();
            $office_table = (new office())->getTable();

            // get admin's office access
            $get_office_access = admin_group_member::select(
                $admin_group_member_table . '.admin_group_id AS group_id',
                $admin_group_table . '.name AS group_name',
                $office_branch_table . '.office_id',
                $office_table . '.name AS office_name',
                $office_branch_table . '.id AS office_branch_id',
                $office_branch_table . '.name AS office_branch_name'
            )
                ->leftJoin($admin_group_table, $admin_group_member_table . '.admin_group_id', $admin_group_table . '.id')
                ->leftJoin($admin_group_branch_table, $admin_group_table . '.id', $admin_group_branch_table . '.admin_group_id')
                ->leftJoin($office_branch_table, $admin_group_branch_table . '.office_branch_id', $office_branch_table . '.id')
                ->leftJoin($office_table, $office_branch_table . '.office_id', $office_table . '.id')
                ->where($admin_group_member_table . '.admin_id', $data->id)
                ->get();

            $admin_group_id = [];
            $admin_group = [];
            $office_access = [];
            if (isset($get_office_access[0])) {
                foreach ($get_office_access as $oa) {
                    if (!array_key_exists($oa->group_id, $admin_group)) {
                        $admin_group_id[] = $oa->group_id;
                        $admin_group[$oa->group_id] = [
                            'group_id' => $oa->group_id,
                            'group_name' => $oa->group_name
                        ];
                    }
                }
                /** dumping $admin_group */
                // array:1 [▼
                //   2 => array:2 [▼
                //     "group_id" => 2
                //     "group_name" => "Administrator"
                //   ]
                // ]

                $params_child = ['office_id', 'office_name', 'office_branch_id', 'office_branch_name'];
                $office_access = Helper::generate_parent_child_data($get_office_access, 'office_id', $params_child);
                /** dumping $office_access */
                // array:5 [▼
                //   1 => array:2 [▼
                //     0 => {#553 ▼
                //       +"office_id": 1
                //       +"office_name": "KINIDI Tech"
                //       +"office_branch_id": 2
                //       +"office_branch_name": "Bekasi"
                //     }
                //     1 => {#555 ▼
                //       +"office_id": 1
                //       +"office_name": "KINIDI Tech"
                //       +"office_branch_id": 1
                //       +"office_branch_name": "Pusat"
                //     }
                //   ]
                //   3 => array:1 [▼
                //     0 => {#551 ▼
                //       +"office_id": 3
                //       +"office_name": "MOKUY"
                //       +"office_branch_id": 4
                //       +"office_branch_name": "Headquarter"
                //     }
                //   ]
                //   7 => array:1 [▼
                //     0 => {#552 ▼
                //       +"office_id": 7
                //       +"office_name": "MPA Grup"
                //       +"office_branch_id": 3
                //       +"office_branch_name": "Sunter"
                //     }
                //   ]
                // ]
            }

            // store to session
            Session::put('sysadmin_group', $admin_group);
            Session::put('sysadmin_branch', $office_access);

            // get table name
            $admin_group_access_table = (new admin_group_access())->getTable();
            $module_rule_table = (new module_rule())->getTable();
            $module_table = (new module())->getTable();

            // get admin access
            $get_access = admin_group_access::select(
                $module_rule_table . '.module_id',
                $module_table . '.name AS module_name',
                $admin_group_access_table . '.module_rule_id AS rule_id',
                $module_rule_table . '.name AS rule_name',
                $module_rule_table . '.description AS rule_description'
            )
                ->leftJoin($admin_group_table, $admin_group_access_table . '.admin_group_id', $admin_group_table . '.id')
                ->leftJoin($module_rule_table, $admin_group_access_table . '.module_rule_id', $module_rule_table . '.id')
                ->leftJoin($module_table, $module_rule_table . '.module_id', $module_table . '.id')
                ->where($module_table . '.status', 1)
                ->whereIn($admin_group_access_table . '.admin_group_id', $admin_group_id)
                ->orderBy($admin_group_access_table . '.admin_group_id')
                ->get();

            $access = [];
            foreach ($get_access as $item) {
                $obj = new \stdClass();
                $obj->module_id = $item->module_id;
                $obj->module_name = $item->module_name;
                $obj->rule_id = $item->rule_id;
                $obj->rule_name = $item->rule_name;
                $obj->rule_description = $item->rule_description;
                $access[] = $obj;
            }

            // store to session
            Session::put('sysadmin_access', $access);

            // logging
            $log_detail_id = 1;
            $module_id = null;
            $target_id = null;
            $note = 'from IP ' . $request->ip();
            $value_before = null;
            $value_after = null;
            $ip_address = $request->ip();
            Helper::logging($log_detail_id, $module_id, $target_id, $note, $value_before, $value_after, $ip_address);

            // SET REDIRECT URI FROM SESSION (IF ANY)
            $redirect_uri = route('admin.home');
            if (Session::has('redirect_uri_admin')) {
                $redirect_uri = Session::get('redirect_uri_admin');
                Session::forget('redirect_uri_admin');
            }

            // remove secure login sessions
            Session::remove('admin_login_trial');

            # SUCCESS
            return redirect($redirect_uri);
        } catch (\Exception $ex) {
            $error_msg = $ex->getMessage() . ' in ' . $ex->getFile() . ' at line ' . $ex->getLine();
            Helper::error_logging($error_msg, null, null, 'Admin Login');

            if (env('APP_DEBUG') == false) {
                $error_msg = lang('Oops, something went wrong please try again later.', $this->translations);
            }

            # ERROR
            return back()
                ->withInput()
                ->with('error', $error_msg);
        }
    }

    /**
     * Logout session.
     *
     * @return redirect()
     */
    public function logout(Request $request)
    {
        $session = Session::get(env('SESSION_ADMIN_NAME', 'sysadmin'));
        if (isset($session)) {
            // logging
            $log_detail_id = 2;
            $module_id = null;
            $target_id = null;
            $note = 'from IP ' . $request->ip();
            $value_before = null;
            $value_after = null;
            $ip_address = $request->ip();
            Helper::logging($log_detail_id, $module_id, $target_id, $note, $value_before, $value_after, $ip_address);
        }

        Session::forget('token_auth');
        Session::forget(env('SESSION_ADMIN_NAME', 'sysadmin'));
        Session::forget('sysadmin_group');
        Session::forget('sysadmin_branch');
        Session::forget('sysadmin_access');

        return redirect()
            ->route('admin.login')
            ->with('success', lang('Logout successfully', $this->translations));
    }

    private function block($user_id = null)
    {
        DB::beginTransaction();
        try {
            $ip_address = Session::get('user_ip_address');

            // check is this IP address blocked
            $found = blocked_ip::where('ip_address', $ip_address)->first();

            if (!$found) {
                // insert to database
                $data = new blocked_ip();
                $data->ip_address = $ip_address;
                $data->admin_id = $user_id;
                $data->remarks = 'Failed to login to Admin Panel (' . Session::get('admin_login_trial') . 'x)';
                $data->save();

                DB::commit();
            }

            return lang('Sorry, you have been blocked because you have failed several times to try to login from', $this->translations) . ' ' . $ip_address;
        } catch (\Exception $ex) {
            DB::rollback();

            $error_msg = $ex->getMessage() . ' in ' . $ex->getFile() . ' at line ' . $ex->getLine();
            Helper::error_logging($error_msg, null, null, 'Block IP address in Admin Login');

            if (env('APP_DEBUG') == false) {
                $error_msg = lang('Oops, something went wrong please try again later.', $this->translations);
            }

            return $error_msg;
        }
    }
}
