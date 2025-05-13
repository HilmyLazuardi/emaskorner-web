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
use App\Models\module_rule;
use App\Models\module;

class ModuleRuleController extends Controller
{
    // SET THIS MODULE
    private $module = 'Rules';
    private $module_id = 5;

    // SET THIS OBJECT/ITEM NAME
    private $item = 'rule';

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

        return view('admin.core.module_rule.list');
    }

    /**
     * Get a listing of the resource using DataTables.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_data(Datatables $datatables, Request $request)
    {
        // get table name
        $module_rule_table = (new module_rule())->getTable();
        $module_table = (new module())->getTable();

        $query = module_rule::select(
            $module_rule_table . '.*',
            'modules.name AS module_name'
        )
            ->leftJoin($module_table . ' AS modules', $module_rule_table . '.module_id', 'modules.id');

        return $datatables->eloquent($query)
            ->addColumn('action', function ($data) {
                $object_id = $data->id;
                if (env('CRYPTOGRAPHY_MODE', false)) {
                    $object_id = Helper::generate_token($data->id);
                }

                $wording_edit = ucwords(lang('edit', $this->translations));
                $html = '<a href="' . route('admin.module_rule.edit', $object_id) . '" class="btn btn-xs btn-primary" title="' . $wording_edit . '"><i class="fa fa-pencil"></i>&nbsp; ' . $wording_edit . '</a>';

                $wording_delete = ucwords(lang('delete', $this->translations));
                $html .= '<form action="' . route('admin.module_rule.delete') . '" method="POST" onsubmit="return confirm(\'' . lang('Are you sure to delete this #item?', $this->translations, ['#item' => $this->item]) . '\');" style="display: inline"> ' . csrf_field() . ' <input type="hidden" name="id" value="' . $object_id . '">
                <button type="submit" class="btn btn-xs btn-danger" title="' . $wording_delete . '"><i class="fa fa-trash"></i>&nbsp; ' . $wording_delete . '</button></form>';

                return $html;
            })
            ->editColumn('updated_at', function ($data) {
                return Helper::time_ago(strtotime($data->updated_at), lang('ago', $this->translations), Helper::get_periods($this->translations));
            })
            ->editColumn('created_at', function ($data) {
                return Helper::locale_timestamp($data->created_at);
            })
            ->rawColumns(['action'])
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

        // get modules data
        $modules = module::where('status', 1)->orderBy('name')->get();

        return view('admin.core.module_rule.form', compact('modules'));
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
            'module_id' => 'required|integer'
        ];
        $message = [
            'required' => ':attribute ' . lang('should not be empty', $this->translations),
            'integer' => ':attribute ' . lang('must be an integer', $this->translations)
        ];
        $names = [
            'module_id' => ucwords(lang('module', $this->translations)),
            'name' => ucwords(lang('rule name', $this->translations))
        ];
        $this->validate($request, $validation, $message, $names);

        $module_id = (int) $request->module_id;
        if (!$module_id) {
            return back()
                ->withInput()
                ->with('error', lang('You have to select a #item', $this->translations, ['#item' => $names['module_id']]));
        }

        // check is module exist
        $module = module::find($module_id);
        if (!$module) {
            return back()
                ->withInput()
                ->with('error', lang('#item not found, please check your link again', $this->translations, ['#item' => $names['module_id']]));
        }

        DB::beginTransaction();
        try {
            // DB PROCESS BELOW

            if ($request->package) {
                // if insert package rules (View List, View Details, Add New, Edit, Delete, Restore)
                $packages = ['View List', 'View Details', 'Add New', 'Edit', 'Delete', 'Restore'];
                $package_details = ['User can view a list of data', 'User can view detail data', 'User can add new item', 'User can edit the item', 'User can delete the item', 'You can restore the item'];
                foreach ($packages as $key => $item) {
                    // check is rule name for the module exist
                    $exist = module_rule::where('module_id', $module_id)->where('name', $item)->first();
                    if ($exist) {
                        return back()
                            ->withInput()
                            ->with('error', lang('#item has already been taken, please input another data', $this->translations, ['#item' => '"' . $module->name . ' > ' . $item . '"']));
                    }

                    $data = new module_rule();
                    $data->module_id = $module_id;
                    $data->name = $item;
                    $data->description = $package_details[$key];
                    $data->save();

                    $saved_data[] = $data;
                }

                // set data for logging
                $data = json_encode($saved_data);

                // set note for logging
                $note = '"' . $module->name . ' > package rules (View List, View Details, Add New, Edit, Delete, Restore)"';

                // set target_id for logging
                // get first id of saved data
                $target_id = $saved_data[0]->id;
            } else {
                // insert manual per item
                // HELPER VALIDATION FOR PREVENT SQL INJECTION & XSS ATTACK
                $name = Helper::validate_input_text($request->name);
                if (!$name) {
                    return back()
                        ->withInput()
                        ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => $names['name']]));
                }

                // check is rule name for the module exist
                $exist = module_rule::where('module_id', $module_id)->where('name', $name)->first();
                if ($exist) {
                    return back()
                        ->withInput()
                        ->with('error', lang('#item has already been taken, please input another data', $this->translations, ['#item' => '"' . $module->name . ' > ' . $name . '"']));
                }

                // SAVE THE DATA
                $data = new module_rule();
                $data->module_id = $module_id;
                $data->name = $name;
                $data->description = Helper::validate_input_text($request->description);
                $data->save();

                // set note for logging
                $note = '"' . $module->name . ' > ' . $name . '"';

                // set target_id for logging
                $target_id = $data->id;
            }

            // logging
            $log_detail_id = 5; // add new
            $module_id = $this->module_id;
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
        $redirect_url = 'admin.module_rule';
        if ($request->stay_on_page) {
            $redirect_url = 'admin.module_rule.create';
        }

        # SUCCESS
        return redirect()
            ->route($redirect_url)
            ->with('success', lang('Successfully added a new #item : #name', $this->translations, ['#item' => $this->item, '#name' => $note]));
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
                ->route('admin.module_rule')
                ->with('error', lang('Invalid #item ID, please check your link again', $this->translations, ['#item' => $this->item]));
        }

        // GET DATA BY ID
        $data = module_rule::find($id);

        // CHECK IS DATA FOUND
        if (!$data) {
            # FAILED - DATA NOT FOUND
            return redirect()
                ->route('admin.module_rule')
                ->with('error', lang('#item not found, please check your link again', $this->translations, ['#item' => $this->item]));
        }

        // get modules data
        $modules = module::where('status', 1)->orderBy('name')->get();

        return view('admin.core.module_rule.form', compact('data', 'raw_id', 'modules'));
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
        $data = module_rule::find($id);

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
            'module_id' => 'required|integer',
            'name' => 'required'
        ];
        $message = [
            'required' => ':attribute ' . lang('should not be empty', $this->translations),
            'integer' => ':attribute ' . lang('must be an integer', $this->translations)
        ];
        $names = [
            'module_id' => ucwords(lang('module', $this->translations)),
            'name' => ucwords(lang('rule name', $this->translations))
        ];
        $this->validate($request, $validation, $message, $names);

        $module_id = (int) $request->module_id;
        if (!$module_id) {
            return back()
                ->withInput()
                ->with('error', lang('You have to select a #item', $this->translations, ['#item' => $names['module_id']]));
        }

        // check is module exist
        $module = module::find($module_id);
        if (!$module) {
            return back()
                ->withInput()
                ->with('error', lang('#item not found, please check your link again', $this->translations, ['#item' => $names['module_id']]));
        }

        DB::beginTransaction();
        try {
            // DB PROCESS BELOW

            // HELPER VALIDATION FOR PREVENT SQL INJECTION & XSS ATTACK
            $name = Helper::validate_input_text($request->name);
            if (!$name) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => $names['name']]));
            }
            $data->name = $name;

            $data->description = Helper::validate_input_text($request->description);

            $data->save();

            // logging
            $value_after = $data->toJson();
            if ($value_before != $value_after) {
                $log_detail_id = 7; // update
                $module_id = $this->module_id;
                $target_id = $data->id;
                $note = '"' . $module->name . ' > ' . $name . '"';
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
        $success_message = lang('Successfully updated #item : #name', $this->translations, ['#item' => $this->item, '#name' => $note]);
        if ($request->stay_on_page) {
            return redirect()
                ->route('admin.module_rule.edit', $raw_id)
                ->with('success', $success_message);
        } else {
            return redirect()
                ->route('admin.module_rule')
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
        $data = module_rule::find($id);

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
            // get module data
            $module = module::find($data->module_id);

            // logging
            $log_detail_id = 8; // delete
            $module_id = $this->module_id;
            $target_id = $data->id;
            $note = '"' . $module->name . ' > ' . $data->name . '"';
            $value_after = $data;
            $ip_address = $request->ip();
            Helper::logging($log_detail_id, $module_id, $target_id, $note, $value_before, $value_after, $ip_address);

            # SUCCESS
            return redirect()
                ->route('admin.module_rule')
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

        return view('admin.core.module_rule.list', compact('deleted_data'));
    }

    /**
     * Get a listing of the deleted resource using DataTables.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_deleted_data(Datatables $datatables, Request $request)
    {
        // get table name
        $module_rule_table = (new module_rule())->getTable();
        $module_table = (new module())->getTable();

        $query = module_rule::onlyTrashed()
            ->select(
                $module_rule_table . '.*',
                'modules.name AS module_name'
            )
            ->leftJoin($module_table . ' AS modules', $module_rule_table . '.module_id', 'modules.id');

        return $datatables->eloquent($query)
            ->addColumn('action', function ($data) {
                $object_id = $data->id;
                if (env('CRYPTOGRAPHY_MODE', false)) {
                    $object_id = Helper::generate_token($data->id);
                }

                $wording_restore = ucwords(lang('restore', $this->translations));
                return '<form action="' . route('admin.module_rule.restore') . '" method="POST" onsubmit="return confirm(\'' . lang('Are you sure to restore this #item?', $this->translations, ['#item' => $this->item]) . '\');" style="display: inline"> ' . csrf_field() . ' <input type="hidden" name="id" value="' . $object_id . '">
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
        $data = module_rule::onlyTrashed()->find($id);

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
            // get module data
            $module = module::find($data->module_id);

            // logging
            $log_detail_id = 9; // restore
            $module_id = $this->module_id;
            $target_id = $data->id;
            $note = '"' . $module->name . ' > ' . $data->name . '"';
            $value_after = $data->toJson();
            $ip_address = $request->ip();
            Helper::logging($log_detail_id, $module_id, $target_id, $note, $value_before, $value_after, $ip_address);

            # SUCCESS
            return redirect()
                ->route('admin.module_rule.deleted_data')
                ->with('success', lang('Successfully restored #item', $this->translations, ['#item' => $this->item]));
        }

        # FAILED
        return back()
            ->with('error', lang('Oops, failed to restore #item. Please try again.', $this->translations, ['#item' => $this->item]));
    }
}
