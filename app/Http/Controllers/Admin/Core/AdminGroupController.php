<?php

namespace App\Http\Controllers\Admin\Core;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

// Libraries
use App\Libraries\Helper;

// Models
use App\Models\admin_group;
use App\Models\admin_group_access;
use App\Models\admin_group_branch;
use App\Models\office;
use App\Models\office_branch;
use App\Models\module;
use App\Models\module_rule;

class AdminGroupController extends Controller
{
    // SET THIS MODULE
    private $module = 'Admin Group';
    private $module_id = 6;

    // SET THIS OBJECT/ITEM NAME
    private $item = 'admin group';

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

        return view('admin.core.admin_group.list');
    }

    /**
     * Get a listing of the resource using DataTables.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_data(Datatables $datatables, Request $request)
    {
        $query = admin_group::where('name', '!=', '*root');

        return $datatables->eloquent($query)
            ->addColumn('action', function ($data) {
                $object_id = $data->id;
                if (env('CRYPTOGRAPHY_MODE', false)) {
                    $object_id = Helper::generate_token($data->id);
                }

                $wording_edit = ucwords(lang('edit', $this->translations));
                $html = '<a href="' . route('admin.group.edit', $object_id) . '" class="btn btn-xs btn-primary" title="' . $wording_edit . '"><i class="fa fa-pencil"></i>&nbsp; ' . $wording_edit . '</a>';

                $wording_delete = ucwords(lang('delete', $this->translations));
                $html .= '<form action="' . route('admin.group.delete') . '" method="POST" onsubmit="return confirm(\'' . lang('Are you sure to delete this #item?', $this->translations, ['#item' => $this->item]) . '\');" style="display: inline"> ' . csrf_field() . ' <input type="hidden" name="id" value="' . $object_id . '">
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

        // get table name
        $office_table = (new office())->getTable();
        $office_branch_table = (new office_branch())->getTable();

        // get list of office
        $offices_raw = office_branch::select(
            $office_table . '.name AS office_name',
            $office_branch_table . '.*'
        )
            ->leftJoin($office_table, $office_branch_table . '.office_id', $office_table . '.id')
            ->where($office_table . '.status', 1)
            ->where($office_branch_table . '.status', 1)
            ->orderBy($office_table . '.ordinal')
            ->orderBy($office_branch_table . '.ordinal')
            ->get();

        $params_child = ['id', 'name', 'office_id'];
        $offices = Helper::generate_parent_child_data($offices_raw, 'office_name', $params_child);

        // get table name
        $module_table = (new module())->getTable();
        $module_rule_table = (new module_rule())->getTable();

        // get list of module & its rules
        $modules_raw = module_rule::select(
            $module_table . '.name AS module_name',
            $module_rule_table . '.*'
        )
            ->leftJoin($module_table, $module_rule_table . '.module_id', $module_table . '.id')
            ->where($module_table . '.status', 1)
            ->orderBy($module_table . '.name')
            ->orderBy($module_rule_table . '.id')
            ->get();

        $params_child = ['id', 'name', 'module_id', 'description'];
        $modules = Helper::generate_parent_child_data($modules_raw, 'module_name', $params_child);

        return view('admin.core.admin_group.form', compact('offices', 'modules'));
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
        $admin_group_table = (new admin_group())->getTable();

        // LARAVEL VALIDATION
        $validation = [
            'name' => 'required|unique:' . $admin_group_table . ',name'
        ];
        $message = [
            'required' => ':attribute ' . lang('should not be empty', $this->translations),
            'unique' => ':attribute ' . lang('has already been taken, please input another data', $this->translations)
        ];
        $names = [
            'name' => ucwords(lang('name', $this->translations))
        ];
        $this->validate($request, $validation, $message, $names);

        DB::beginTransaction();
        try {
            // DB PROCESS BELOW

            // HELPER VALIDATION FOR PREVENT SQL INJECTION & XSS ATTACK
            $name = Helper::validate_input_text($request->name);
            if (!$name) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('name', $this->translations))]));
            }

            // SAVE THE DATA
            $data = new admin_group();
            $data->name = $name;
            $data->status = (int) $request->status;
            $data->save();

            $group_id = $data->id;

            // set office access
            if (isset($request->branch)) {
                $data_branch = [];
                foreach ($request->branch as $item) {
                    $data_branch[] = [
                        'admin_group_id' => $group_id,
                        'office_branch_id' => $item,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                }
                admin_group_branch::insert($data_branch);

                $data->branch = $data_branch;
            }

            // set module access
            if (isset($request->access)) {
                $data_access = [];
                foreach ($request->access as $item) {
                    $data_access[] = [
                        'admin_group_id' => $group_id,
                        'module_rule_id' => $item,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                }
                admin_group_access::insert($data_access);

                $data->access = $data_access;
            }

            // logging
            $log_detail_id = 5; // add new
            $module_id = $this->module_id;
            $target_id = $data->id;
            $note = '"' . $name . '"';
            $value_before = null;
            $value_after = $data;
            $ip_address = $request->ip();
            Helper::logging($log_detail_id, $module_id, $target_id, $note, $value_before, $value_after, $ip_address);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();

            $error_msg = $ex->getMessage() . ' in ' . $ex->getFile() . ' at line ' . $ex->getLine();

            $err = new \App\Models\error_log();
            $err->url_get_error = url()->full();
            $err->url_prev = url()->previous();
            $err->err_message = $error_msg;
            $err->admin_id = Session::get(env('SESSION_ADMIN_NAME', 'sysadmin'))->id;
            $err->module_id = $this->module_id;
            $err->save();

            if (env('APP_DEBUG') == false) {
                $error_msg = lang('Oops, something went wrong please try again later.', $this->translations);
            }

            # ERROR
            return back()
                ->withInput()
                ->with('error', $error_msg);
        }

        // SET REDIRECT URL
        $redirect_url = 'admin.group';
        if ($request->stay_on_page) {
            $redirect_url = 'admin.group.create';
        }

        # SUCCESS
        return redirect()
            ->route($redirect_url)
            ->with('success', lang('Successfully added a new #item : #name', $this->translations, ['#item' => $this->item, '#name' => $name]));
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
                ->route('admin.module')
                ->with('error', lang('Invalid #item ID, please check your link again', $this->translations, ['#item' => $this->item]));
        }

        // GET DATA BY ID
        $data = admin_group::find($id);

        // CHECK IS DATA FOUND
        if (!$data) {
            # FAILED - DATA NOT FOUND
            return redirect()
                ->route('admin.module')
                ->with('error', lang('#item not found, please check your link again', $this->translations, ['#item' => $this->item]));
        }

        // get table name
        $office_table = (new office())->getTable();
        $office_branch_table = (new office_branch())->getTable();

        // get list of office
        $offices_raw = office_branch::select(
            $office_table . '.name AS office_name',
            $office_branch_table . '.*'
        )
            ->leftJoin($office_table, $office_branch_table . '.office_id', $office_table . '.id')
            ->where($office_table . '.status', 1)
            ->where($office_branch_table . '.status', 1)
            ->orderBy($office_table . '.ordinal')
            ->orderBy($office_branch_table . '.ordinal')
            ->get();

        $params_child = ['id', 'name', 'office_id'];
        $offices = Helper::generate_parent_child_data($offices_raw, 'office_name', $params_child);

        // get table name
        $module_table = (new module())->getTable();
        $module_rule_table = (new module_rule())->getTable();

        // get list of module & its rules
        $modules_raw = module_rule::select(
            $module_table . '.name AS module_name',
            $module_rule_table . '.*'
        )
            ->leftJoin($module_table, $module_rule_table . '.module_id', $module_table . '.id')
            ->where($module_table . '.status', 1)
            ->orderBy($module_table . '.name')
            ->orderBy($module_rule_table . '.id')
            ->get();

        $params_child = ['id', 'name', 'module_id', 'description'];
        $modules = Helper::generate_parent_child_data($modules_raw, 'module_name', $params_child);

        // get group's module access
        $access = [];
        $get_access = admin_group_access::select('module_rule_id')->where('admin_group_id', $id)->get();
        if (isset($get_access[0])) {
            foreach ($get_access as $item) {
                $access[] = $item->module_rule_id;
            }
        }

        // get group's office access
        $office_allowed = [];
        $get_office_allowed = admin_group_branch::select('office_branch_id')->where('admin_group_id', $id)->get();
        if (isset($get_office_allowed[0])) {
            foreach ($get_office_allowed as $item) {
                $office_allowed[] = $item->office_branch_id;
            }
        }

        return view('admin.core.admin_group.form', compact('data', 'raw_id', 'offices', 'modules', 'access', 'office_allowed'));
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
        $data = admin_group::find($id);

        // CHECK IS DATA FOUND
        if (!$data) {
            # FAILED - DATA NOT FOUND
            return back()
                ->withInput()
                ->with('error', lang('#item not found, please reload your page before resubmit', $this->translations, ['#item' => $this->item]));
        }

        // get group's module access
        $access = [];
        $get_access = admin_group_access::select('module_rule_id')->where('admin_group_id', $id)->get();
        if (isset($get_access[0])) {
            foreach ($get_access as $item) {
                $access[] = $item->module_rule_id;
            }
        }
        $data->access = $access;

        // get group's office access
        $office_allowed = [];
        $get_office_allowed = admin_group_branch::select('office_branch_id')->where('admin_group_id', $id)->get();
        if (isset($get_office_allowed[0])) {
            foreach ($get_office_allowed as $item) {
                $office_allowed[] = $item->office_branch_id;
            }
        }
        $data->branch = $office_allowed;

        // store data before updated
        $value_before = $data->toJson();

        // unset attributes: access & branch
        unset($data->access);
        unset($data->branch);

        // LARAVEL VALIDATION
        $validation = [
            'name' => 'required'
        ];
        $message = [
            'required' => ':attribute ' . lang('should not be empty', $this->translations)
        ];
        $names = [
            'name' => ucwords(lang('name', $this->translations))
        ];
        $this->validate($request, $validation, $message, $names);

        DB::beginTransaction();
        try {
            // DB PROCESS BELOW

            // HELPER VALIDATION FOR PREVENT SQL INJECTION & XSS ATTACK
            $name = Helper::validate_input_text($request->name);
            if (!$name) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('name', $this->translations))]));
            }
            $data->name = $name;

            $data->status = (int) $request->status;

            $data->save();

            $group_id = $data->id;

            // delete existing office access first
            admin_group_branch::where('admin_group_id', $group_id)->delete();

            // set office access
            if (isset($request->branch)) {
                $data_branch = [];
                foreach ($request->branch as $item) {
                    $data_branch[] = [
                        'admin_group_id' => $group_id,
                        'office_branch_id' => $item,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                }
                admin_group_branch::insert($data_branch);

                $data->branch = $data_branch;
            }

            // delete existing module access first
            admin_group_access::where('admin_group_id', $group_id)->delete();

            // set module access
            if (isset($request->access)) {
                $data_access = [];
                foreach ($request->access as $item) {
                    $data_access[] = [
                        'admin_group_id' => $group_id,
                        'module_rule_id' => $item,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                }
                admin_group_access::insert($data_access);

                $data->access = $data_access;
            }

            // logging
            $value_after = $data->toJson();
            if ($value_before != $value_after) {
                $log_detail_id = 7; // update
                $module_id = $this->module_id;
                $target_id = $data->id;
                $note = '"' . $name . '"';
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
        $success_message = lang('Successfully updated #item : #name', $this->translations, ['#item' => $this->item, '#name' => $name]);
        if ($request->stay_on_page) {
            return redirect()
                ->route('admin.group.edit', $raw_id)
                ->with('success', $success_message);
        } else {
            return redirect()
                ->route('admin.group')
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
        $data = admin_group::find($id);

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
                ->route('admin.group')
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

        return view('admin.core.admin_group.list', compact('deleted_data'));
    }

    /**
     * Get a listing of the deleted resource using DataTables.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_deleted_data(Datatables $datatables, Request $request)
    {
        $query = admin_group::onlyTrashed();

        return $datatables->eloquent($query)
            ->addColumn('action', function ($data) {
                $object_id = $data->id;
                if (env('CRYPTOGRAPHY_MODE', false)) {
                    $object_id = Helper::generate_token($data->id);
                }

                $wording_restore = ucwords(lang('restore', $this->translations));
                return '<form action="' . route('admin.group.restore') . '" method="POST" onsubmit="return confirm(\'' . lang('Are you sure to restore this #item?', $this->translations, ['#item' => $this->item]) . '\');" style="display: inline"> ' . csrf_field() . ' <input type="hidden" name="id" value="' . $object_id . '">
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
        $data = admin_group::onlyTrashed()->find($id);

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
                ->route('admin.group.deleted_data')
                ->with('success', lang('Successfully restored #item', $this->translations, ['#item' => $this->item]));
        }

        # FAILED
        return back()
            ->with('error', lang('Oops, failed to restore #item. Please try again.', $this->translations, ['#item' => $this->item]));
    }
}
