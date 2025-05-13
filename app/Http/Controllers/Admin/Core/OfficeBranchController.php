<?php

namespace App\Http\Controllers\Admin\Core;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;

// Libraries
use App\Libraries\Helper;

// Models
use App\Models\office_branch;
use App\Models\office;

class OfficeBranchController extends Controller
{
    // SET THIS MODULE
    private $module = 'Branch Office';
    private $module_id = 3;

    // SET THIS OBJECT/ITEM NAME
    private $item = 'branch office';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($parent_id)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'View List');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        $raw_id = $parent_id;
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $parent_id = Helper::validate_token($parent_id);
        }

        // CHECK IS OFFICE EXIST
        $data = office::find($parent_id);
        if (!$data) {
            # FAILED - DATA NOT FOUND
            return redirect()
                ->route('admin.office')
                ->with('error', lang('#item not found, please check your link again', $this->translations, ['#item' => ucwords(lang('office', $this->translations))]));
        }

        return view('admin.core.office_branch.list', compact('raw_id', 'data'));
    }

    /**
     * Get a listing of the resource using DataTables.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_data($parent_id, Request $request)
    {
        // SET THIS OBJECT/ITEM NAME BASED ON TRANSLATION
        $this->item = ucwords(lang($this->item, $this->translations));

        $parent_raw_id = $parent_id;
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $parent_id = Helper::validate_token($parent_id);
        }

        // AJAX OR API VALIDATOR
        $validation_rules = [
            // 'keyword' => 'required'
        ];
        $validator = Validator::make($request->all(), $validation_rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'false',
                'message' => 'Validation Error: ' . implode(', ', $validator->errors()->all()),
                'data' => $validator->errors()->messages()
            ]);
        }

        // GET THE DATA
        $data = office_branch::where('office_id', $parent_id)->orderBy('ordinal')->get();

        // MANIPULATE THE DATA
        if (isset($data[0])) {
            $wording_edit = ucwords(lang("edit", $this->translations));
            $wording_delete = ucwords(lang("delete", $this->translations));

            foreach ($data as $item) {
                $item->status_label = '<span class="label label-success">' . ucwords(lang('active', $this->translations)) . '</span>';
                if ($item->status != 1) {
                    $item->status_label = '<span class="label label-danger"><i>' . ucwords(lang('inactive', $this->translations)) . '</i></span>';
                }

                $item->created_at_edited = Helper::locale_timestamp($item->created_at);
                $item->updated_at_edited = Helper::time_ago(strtotime($item->updated_at), lang('ago', $this->translations), Helper::get_periods($this->translations));

                $object_id = $item->id;
                if (env('CRYPTOGRAPHY_MODE', false)) {
                    $object_id = Helper::generate_token($item->id);
                }

                $edit_button = '<a href="' . route('admin.office_branch.edit', [$parent_raw_id, $object_id]) . '" class="btn btn-xs btn-primary" title="' . $wording_edit . '"><i class="fa fa-pencil"></i>&nbsp; ' . $wording_edit . '</a>';
                $delete_button = '<form action="' . route('admin.office_branch.delete', $parent_raw_id) . '" method="POST" onsubmit="return confirm(\'' . lang('Are you sure to delete this #item?', $this->translations, ['#item' => $this->item]) . '\');" style="display: inline"> ' . csrf_field() . ' <input type="hidden" name="id" value="' . $object_id . '">
                <button type="submit" class="btn btn-xs btn-danger" title="' . $wording_delete . '"><i class="fa fa-trash"></i>&nbsp; ' . $wording_delete . '</button></form>';

                $item->action = $edit_button . $delete_button;

                if ($item->phone) {
                    $item->phone = env('COUNTRY_CODE') . $item->phone;
                } else {
                    $item->phone = '-';
                }
            }
        }

        # SUCCESS
        $response = [
            'status' => 'true',
            'message' => 'Successfully get ordered data list',
            'data' => $data
        ];
        return response()->json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($parent_id)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'Add New');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        $raw_id = $parent_id;
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $parent_id = Helper::validate_token($parent_id);
        }

        // CHECK IS OFFICE EXIST
        $parent_data = office::find($parent_id);
        if (!$parent_data) {
            # FAILED - DATA NOT FOUND
            return redirect()
                ->route('admin.office')
                ->with('error', lang('#item not found, please check your link again', $this->translations, ['#item' => ucwords(lang('office', $this->translations))]));
        }

        $parent_id = $raw_id;
        return view('admin.core.office_branch.form', compact('parent_id', 'parent_data'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($parent_id, Request $request)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'Add New');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        // SET THIS OBJECT/ITEM NAME BASED ON TRANSLATION
        $this->item = ucwords(lang($this->item, $this->translations));

        $raw_id = $parent_id;
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $parent_id = Helper::validate_token($parent_id);
        }

        // CHECK IS OFFICE EXIST
        $parent_data = office::find($parent_id);
        if (!$parent_data) {
            # FAILED - DATA NOT FOUND
            return redirect()
                ->route('admin.office')
                ->with('error', lang('#item not found, please check your link again', $this->translations, ['#item' => ucwords(lang('office', $this->translations))]));
        }

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

            // SAVE THE DATA
            $data = new office_branch();
            $data->office_id = $parent_id;
            $data->name = $name;
            $data->address = Helper::validate_input_text($request->address);
            $data->phone = $request->phone;
            $data->fax = $request->fax;
            $data->status = (int) $request->status;

            // SET ORDER / ORDINAL
            $last = office_branch::select('ordinal')->where('office_id', $parent_id)->orderBy('ordinal', 'desc')->first();
            $ordinal = 1;
            if ($last) {
                $ordinal = $last->ordinal + 1;
            }
            $data->ordinal = $ordinal;

            $data->save();

            // logging
            $log_detail_id = 5; // add new
            $module_id = $this->module_id;
            $target_id = $data->id;
            $note = '"' . $parent_data->name . ' > ' . $name . '"';
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
        $redirect_url = 'admin.office_branch';
        if ($request->stay_on_page) {
            $redirect_url = 'admin.office_branch.create';
        }

        # SUCCESS
        return redirect()
            ->route($redirect_url, $raw_id)
            ->with('success', lang('Successfully added a new #item : #name', $this->translations, ['#item' => $this->item, '#name' => $name]));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  id   $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function edit($parent_id, $id, Request $request)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'View Details');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        // SET THIS OBJECT/ITEM NAME BASED ON TRANSLATION
        $this->item = ucwords(lang($this->item, $this->translations));

        $parent_raw_id = $parent_id;
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $parent_id = Helper::validate_token($parent_id);
        }

        // CHECK IS OFFICE EXIST
        $parent_data = office::find($parent_id);
        if (!$parent_data) {
            # FAILED - DATA NOT FOUND
            return redirect()
                ->route('admin.office')
                ->with('error', lang('#item not found, please check your link again', $this->translations, ['#item' => ucwords(lang('office', $this->translations))]));
        }

        $raw_id = $id;
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $id = Helper::validate_token($id);
        }

        // CHECK OBJECT ID
        if ((int) $id < 1) {
            // INVALID OBJECT ID
            return redirect()
                ->route('admin.office_branch')
                ->with('error', lang('Invalid #item ID, please check your link again', $this->translations, ['#item' => $this->item]));
        }

        // GET DATA BY ID
        $data = office_branch::find($id);

        // CHECK IS DATA FOUND
        if (!$data) {
            # FAILED - DATA NOT FOUND
            return redirect()
                ->route('admin.office_branch', $parent_raw_id)
                ->with('error', lang('#item not found, please check your link again', $this->translations, ['#item' => $this->item]));
        }

        $parent_id = $parent_raw_id;
        return view('admin.core.office_branch.form', compact('data', 'raw_id', 'parent_data', 'parent_id'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  id   $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update($parent_id, $id, Request $request)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'Edit');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        // SET THIS OBJECT/ITEM NAME BASED ON TRANSLATION
        $this->item = ucwords(lang($this->item, $this->translations));

        $parent_raw_id = $parent_id;
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $parent_id = Helper::validate_token($parent_id);
        }

        // CHECK IS OFFICE EXIST
        $parent_data = office::find($parent_id);
        if (!$parent_data) {
            # FAILED - DATA NOT FOUND
            return redirect()
                ->route('admin.office')
                ->with('error', lang('#item not found, please check your link again', $this->translations, ['#item' => ucwords(lang('office', $this->translations))]));
        }

        $raw_id = $id;
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $id = Helper::validate_token($id);
        }

        // GET DATA BY ID
        $data = office_branch::find($id);

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

            // SAVE THE DATA
            $data->name = $name;
            $data->address = Helper::validate_input_text($request->address);
            $data->phone = $request->phone;
            $data->fax = $request->fax;
            $data->status = (int) $request->status;

            $data->save();

            // logging
            $value_after = $data->toJson();
            if ($value_before != $value_after) {
                $log_detail_id = 7; // update
                $module_id = $this->module_id;
                $target_id = $data->id;
                $note = '"' . $parent_data->name . ' > ' . $name . '"';
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
                ->route('admin.office_branch.edit', [$parent_raw_id, $raw_id])
                ->with('success', $success_message);
        } else {
            return redirect()
                ->route('admin.office_branch', $parent_raw_id)
                ->with('success', $success_message);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function delete($parent_id, Request $request)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'Delete');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        // SET THIS OBJECT/ITEM NAME BASED ON TRANSLATION
        $this->item = ucwords(lang($this->item, $this->translations));

        $parent_raw_id = $parent_id;
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $parent_id = Helper::validate_token($parent_id);
        }

        // CHECK IS OFFICE EXIST
        $parent_data = office::find($parent_id);
        if (!$parent_data) {
            # FAILED - DATA NOT FOUND
            return redirect()
                ->route('admin.office')
                ->with('error', lang('#item not found, please check your link again', $this->translations, ['#item' => ucwords(lang('office', $this->translations))]));
        }

        $id = $request->id;
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $id = Helper::validate_token($id);
        }

        // GET DATA BY ID
        $data = office_branch::find($id);

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
            $note = '"' . $parent_data->name . ' > ' . $data->name . '"';
            $value_after = $data;
            $ip_address = $request->ip();
            Helper::logging($log_detail_id, $module_id, $target_id, $note, $value_before, $value_after, $ip_address);

            # SUCCESS
            return redirect()
                ->route('admin.office_branch', $parent_raw_id)
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
    public function deleted_data($parent_id)
    {
        $raw_id = $parent_id;
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $parent_id = Helper::validate_token($parent_id);
        }

        // CHECK IS OFFICE EXIST
        $data = office::find($parent_id);
        if (!$data) {
            # FAILED - DATA NOT FOUND
            return redirect()
                ->route('admin.office')
                ->with('error', lang('#item not found, please check your link again', $this->translations, ['#item' => ucwords(lang('office', $this->translations))]));
        }

        $deleted_data = true;

        return view('admin.core.office_branch.list', compact('deleted_data', 'raw_id', 'data'));
    }

    /**
     * Get a listing of the deleted resource using DataTables.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_deleted_data($parent_id, Datatables $datatables, Request $request)
    {
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $parent_id = Helper::validate_token($parent_id);
        }

        $query = office_branch::onlyTrashed()->where('office_id', $parent_id);

        return $datatables->eloquent($query)
            ->addColumn('action', function ($data) {
                $object_id = $data->id;
                $parent_id = $data->office_id;
                if (env('CRYPTOGRAPHY_MODE', false)) {
                    $object_id = Helper::generate_token($data->id);
                    $parent_id = Helper::generate_token($data->office_id);
                }

                $wording_restore = ucwords(lang('restore', $this->translations));
                return '<form action="' . route('admin.office_branch.restore', $parent_id) . '" method="POST" onsubmit="return confirm(\'' . lang('Are you sure to restore this #item?', $this->translations, ['#item' => $this->item]) . '\');" style="display: inline"> ' . csrf_field() . ' <input type="hidden" name="id" value="' . $object_id . '">
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
    public function restore($parent_id, Request $request)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'Restore');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        // SET THIS OBJECT/ITEM NAME BASED ON TRANSLATION
        $this->item = ucwords(lang($this->item, $this->translations));

        $parent_raw_id = $parent_id;
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $parent_id = Helper::validate_token($parent_id);
        }

        // CHECK IS OFFICE EXIST
        $parent_data = office::find($parent_id);
        if (!$parent_data) {
            # FAILED - DATA NOT FOUND
            return redirect()
                ->route('admin.office')
                ->with('error', lang('#item not found, please check your link again', $this->translations, ['#item' => ucwords(lang('office', $this->translations))]));
        }

        $id = $request->id;
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $id = Helper::validate_token($id);
        }

        // GET DATA BY ID
        $data = office_branch::onlyTrashed()->find($id);

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
            $note = '"' . $parent_data->name . ' > ' . $data->name . '"';
            $value_after = $data->toJson();
            $ip_address = $request->ip();
            Helper::logging($log_detail_id, $module_id, $target_id, $note, $value_before, $value_after, $ip_address);

            # SUCCESS
            return redirect()
                ->route('admin.office_branch.deleted_data', $parent_raw_id)
                ->with('success', lang('Successfully restored #item', $this->translations, ['#item' => $this->item]));
        }

        # FAILED
        return back()
            ->with('error', lang('Oops, failed to restore #item. Please try again.', $this->translations, ['#item' => $this->item]));
    }

    /**
     * Sorting a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sorting(Request $request)
    {
        // AJAX OR API VALIDATOR
        $validation_rules = [
            'rows' => 'required'
        ];
        $validator = Validator::make($request->all(), $validation_rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'false',
                'message' => 'Validation Error: ' . implode(', ', $validator->errors()->all()),
                'data' => $validator->errors()->messages()
            ]);
        }

        // JSON Array - sample: row[]=2&row[]=1&row[]=3
        $rows = $request->input('rows');

        // convert to array
        $data = explode('&', $rows);

        DB::beginTransaction();
        try {
            // DB PROCESS BELOW

            $ordinal = 1;
            foreach ($data as $item) {
                // split the data
                $tmp = explode('[]=', $item);

                $object = office_branch::find($tmp[1]);
                $object->ordinal = $ordinal;
                $object->save();

                $ordinal++;
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
            return response()->json([
                'status' => 'false',
                'message' => $error_msg,
                'data' => ''
            ]);
        }

        # SUCCESS
        $response = [
            'status' => 'true',
            'message' => 'Successfully sort data',
            'data' => $data
        ];
        return response()->json($response, 200);
    }
}
