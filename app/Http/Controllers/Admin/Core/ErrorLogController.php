<?php

namespace App\Http\Controllers\Admin\Core;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;

// Libraries
use App\Libraries\Helper;

// Models
use App\Models\error_log;
use App\Models\module;
use App\Models\admin;

class ErrorLogController extends Controller
{
    // SET THIS MODULE
    private $module = 'Error Logs';
    private $module_id = '12';

    // SET THIS OBJECT/ITEM NAME
    private $item = 'error log';

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

        return view('admin.core.error_logs.list');
    }

    /**
     * Get a listing of the resource using DataTables.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_data(Datatables $datatables, Request $request)
    {
        // get table system name
        $table_error_log = (new error_log())->getTable();
        $table_module = (new module())->getTable();
        $table_admin = (new admin())->getTable();

        $query = error_log::select(
            $table_error_log . '.*',
            $table_module . '.name AS module_name',
            $table_admin . '.username'
        )
            ->leftJoin($table_module, $table_error_log . '.module_id', '=', $table_module . '.id')
            ->leftJoin($table_admin, $table_error_log . '.admin_id', '=', $table_admin . '.id');

        return $datatables->eloquent($query)
            ->addColumn('action', function ($data) {
                $object_id = $data->id;
                if (env('CRYPTOGRAPHY_MODE', false)) {
                    $object_id = Helper::generate_token($data->id);
                }

                $wording_edit = ucwords(lang('view', $this->translations));
                $html = '<a href="' . route('admin.error_logs.view', $object_id) . '" class="btn btn-xs btn-primary" title="' . $wording_edit . '"><i class="fa fa-eye"></i>&nbsp; ' . $wording_edit . '</a>';

                return $html;
            })
            ->addColumn('status_label', function ($data) {
                if ($data->status != 1) {
                    return '<span class="label label-danger"><i>' . ucwords(lang('unsolved', $this->translations)) . '</i></span>';
                }
                return '<span class="label label-success">' . ucwords(lang('solved', $this->translations)) . '</span>';
            })
            ->editColumn('err_message', function ($data) {
                return Helper::read_more($data->err_message, 100);
            })
            ->editColumn('updated_at', function ($data) {
                return Helper::time_ago(strtotime($data->updated_at), lang('ago', $this->translations), Helper::get_periods($this->translations));
            })
            ->editColumn('created_at', function ($data) {
                return Helper::locale_timestamp($data->created_at);
            })
            ->rawColumns(['action', 'status_label'])
            ->toJson();
    }

    /**
     * View details of the specified resource.
     *
     * @param  id   $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function view($id, Request $request)
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
                ->route('admin.error_logs')
                ->with('error', lang('Invalid #item ID, please check your link again', $this->translations, ['#item' => $this->item]));
        }

        $table_error_log = (new error_log())->getTable();
        $table_module = (new module())->getTable();
        $table_admin = (new admin())->getTable();

        $data = error_log::select(
            $table_error_log . '.*',
            $table_module . '.name AS module_name',
            $table_admin . '.username'
        )
            ->leftJoin($table_module, $table_error_log . '.module_id', '=', $table_module . '.id')
            ->leftJoin($table_admin, $table_error_log . '.admin_id', '=', $table_admin . '.id')
            ->where($table_error_log . '.id', $id)
            ->first();

        // CHECK IS DATA FOUND
        if (!$data) {
            # FAILED - DATA NOT FOUND
            return redirect()
                ->route('admin.error_logs')
                ->with('error', lang('#item not found, please check your link again', $this->translations, ['#item' => $this->item]));
        }

        return view('admin.core.error_logs.details', compact('data', 'raw_id'));
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
        $authorize = Helper::authorizing($this->module, 'Update');
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
        $data = error_log::find($id);

        // CHECK IS DATA FOUND
        if (!$data) {
            # FAILED - DATA NOT FOUND
            return back()
                ->withInput()
                ->with('error', lang('#item not found, please reload your page before resubmit', $this->translations, ['#item' => $this->item]));
        }

        // store data before updated
        $value_before = $data->toJson();

        DB::beginTransaction();
        try {
            // DB PROCESS BELOW

            $data->status = (int) $request->status;
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
        $success_message = lang('Successfully updated #item', $this->translations, ['#item' => $this->item]);
        if ($request->stay_on_page) {
            return redirect()
                ->route('admin.error_logs.view', $raw_id)
                ->with('success', $success_message);
        } else {
            return redirect()
                ->route('admin.error_logs')
                ->with('success', $success_message);
        }
    }
}
