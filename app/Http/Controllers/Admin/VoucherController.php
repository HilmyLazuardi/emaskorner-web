<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;

// Libraries
use App\Libraries\Helper;

// Models
use App\Models\voucher;

class VoucherController extends Controller
{
    // SET THIS MODULE
    private $module = 'Voucher';
    private $module_id = 39;

    // SET THIS OBJECT/ITEM NAME
    private $item = 'voucher';

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

        return view('admin.voucher.list');
    }

    /**
     * Get a listing of the resource using DataTables.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_data(Datatables $datatables, Request $request)
    {
        $query = voucher::whereNotNull('id');

        return $datatables->eloquent($query)
            ->addColumn('action', function ($data) {
                $object_id = $data->id;
                if (env('CRYPTOGRAPHY_MODE', false)) {
                    $object_id = Helper::generate_token($data->id);
                }

                $wording_edit = ucwords(lang('edit', $this->translations));
                $html = '<a href="' . route('admin.voucher.edit', $object_id) . '" class="btn btn-xs btn-primary" title="' . $wording_edit . '"><i class="fa fa-pencil"></i>&nbsp; ' . $wording_edit . '</a>';

                $wording_delete = ucwords(lang('delete', $this->translations));
                $html .= '<form action="' . route('admin.voucher.delete') . '" method="POST" onsubmit="return confirm(\'' . lang('Are you sure to delete this #item?', $this->translations, ['#item' => $this->item]) . '\');" style="display: inline"> ' . csrf_field() . ' <input type="hidden" name="id" value="' . $object_id . '">
                <button type="submit" class="btn btn-xs btn-danger" title="' . $wording_delete . '"><i class="fa fa-trash"></i>&nbsp; ' . $wording_delete . '</button></form>';

                return $html;
            })
            ->editColumn('voucher_type', function ($data) {
                return ucwords($data->voucher_type);
            })
            ->editColumn('discount_type', function ($data) {
                return ucwords($data->discount_type);
            })
            ->addColumn('is_active_label', function ($data) {
                if ($data->is_active != 1) {
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
            ->rawColumns(['action', 'is_active_label'])
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

        return view('admin.voucher.form');
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
            'unique_code' => 'required',
            'name' => 'required',
            'voucher_type' => 'required',
            'discount_type' => 'required',
            'discount_value' => 'required',
            'min_transaction' => 'required',
            'description' => 'required',
            'qty' => 'required',
            'qty_per_user' => 'required',
            'period_begin' => 'required',
            'period_end' => 'required',
        ];
        $message = [
            'required' => ':attribute ' . lang('should not be empty', $this->translations)
        ];
        $names = [
            'unique_code' => ucwords(lang('unique code', $this->translations)),
            'name' => ucwords(lang('name', $this->translations)),
            'voucher_type' => ucwords(lang('voucher type', $this->translations)),
            'discount_type' => ucwords(lang('discount type', $this->translations)),
            'discount_value' => ucwords(lang('discount value', $this->translations)),
            'min_transaction' => ucwords(lang('min transaction', $this->translations)),
            'description' => ucwords(lang('description', $this->translations)),
            'qty' => ucwords(lang('qty', $this->translations)),
            'qty_per_user' => ucwords(lang('qty per user', $this->translations)),
            'period_begin' => ucwords(lang('period begin', $this->translations)),
            'period_end' => ucwords(lang('period end', $this->translations)),
        ];
        $this->validate($request, $validation, $message, $names);

        DB::beginTransaction();
        try {
            // DB PROCESS BELOW

            // HELPER VALIDATION FOR PREVENT SQL INJECTION & XSS ATTACK
            $unique_code = Helper::validate_input_text($request->unique_code, TRUE);
            if (!$unique_code) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => $names['unique_code']]));
            }

            // UNIQUE CODE DB VALIDATION
            $unique_code_db = voucher::select('id')
                ->where('unique_code', $unique_code)
                ->first();
            
            if (!empty($unique_code_db)) {
                return back()
                    ->withInput()
                    ->with('error', lang('Unique code ' . $unique_code . ' is already exist. Please use another unique code', $this->translations));
            }

            // HELPER VALIDATION FOR PREVENT SQL INJECTION & XSS ATTACK
            $name = Helper::validate_input_text($request->name, TRUE);
            if (!$name) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => $names['name']]));
            }

            // PERIOD START
            $period_begin = Helper::validate_input_text($request->period_begin);
            $period_begin = str_replace('/', '-', $period_begin);
            if (!$period_begin) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('period begin', $this->translations))]));
            }

            $period_end = Helper::validate_input_text($request->period_end);
            $period_end = str_replace('/', '-', $period_end);
            if (!$period_end) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('period end', $this->translations))]));
            }

            // CUSTOM VALIDATION FOR PERIOD PERIOD
            $date_period_begin = date('Y-m-d H:i:s', strtotime($period_begin));
            $date_period_end = date('Y-m-d H:i:s', strtotime($period_end));
            if ($date_period_end <= $date_period_begin) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('period end', $this->translations))]));
            }

            // CREATE NEW DATA
            $data                      = new voucher();
            $data->unique_code         = strtoupper($unique_code);
            $data->name                = $name;
            $data->voucher_type        = Helper::validate_input_text($request->voucher_type, TRUE);
            $data->discount_type       = Helper::validate_input_text($request->discount_type, TRUE);
            $data->discount_value      = str_replace(',', '', $request->discount_value);

            if ($data->discount_type == 'percentage' && $request->discount_max_amount !== NULL) {
                $data->discount_max_amount = (int) str_replace(',', '', $request->discount_max_amount);
            } else {
                $data->discount_max_amount = NULL;
            }

            $data->min_transaction     = (int) str_replace(',', '', $request->min_transaction);
            $data->description         = Helper::validate_input_text($request->description, TRUE);
            $data->qty                 = (int) $request->qty;
            $data->qty_per_user        = (int) $request->qty_per_user;
            $data->period_begin        = $date_period_begin;
            $data->period_end          = $date_period_end;
            $data->is_active           = (int) $request->is_active;
            $data->save();

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
        $redirect_url = 'admin.voucher';
        if ($request->stay_on_page) {
            $redirect_url = 'admin.voucher.create';
        }

        # SUCCESS
        return redirect()
            ->route($redirect_url)
            ->with('success', lang(
                'Successfully added a new #item : #name',
                $this->translations,
                [
                    '#item' => $this->item,
                    '#name' => $name
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
                ->route('admin.voucher')
                ->with('error', lang('Invalid #item ID, please check your link again', $this->translations, ['#item' => $this->item]));
        }

        // GET DATA BY ID
        $data = voucher::find($id);

        // CHECK IS DATA FOUND
        if (!$data) {
            # FAILED - DATA NOT FOUND
            return redirect()
                ->route('admin.voucher')
                ->with('error', lang('#item not found, please check your link again', $this->translations, ['#item' => $this->item]));
        }

        if ($data->discount_max_amount === null) {
            unset($data->discount_max_amount);
        }

        return view('admin.voucher.form', compact('data', 'raw_id'));
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
        $data = voucher::find($id);

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
            'unique_code' => 'required',
            'name' => 'required',
            'voucher_type' => 'required',
            'discount_type' => 'required',
            'discount_value' => 'required',
            'min_transaction' => 'required',
            'description' => 'required',
            'qty' => 'required',
            'qty_per_user' => 'required',
            'period_begin' => 'required',
            'period_end' => 'required',
        ];
        $message = [
            'required' => ':attribute ' . lang('should not be empty', $this->translations)
        ];
        $names = [
            'unique_code' => ucwords(lang('unique code', $this->translations)),
            'name' => ucwords(lang('name', $this->translations)),
            'voucher_type' => ucwords(lang('voucher type', $this->translations)),
            'discount_type' => ucwords(lang('discount type', $this->translations)),
            'discount_value' => ucwords(lang('discount value', $this->translations)),
            'min_transaction' => ucwords(lang('min transaction', $this->translations)),
            'description' => ucwords(lang('description', $this->translations)),
            'qty' => ucwords(lang('qty', $this->translations)),
            'qty_per_user' => ucwords(lang('qty per user', $this->translations)),
            'period_begin' => ucwords(lang('period begin', $this->translations)),
            'period_end' => ucwords(lang('period end', $this->translations)),
        ];
        $this->validate($request, $validation, $message, $names);

        DB::beginTransaction();
        try {
            // DB PROCESS BELOW

            // HELPER VALIDATION FOR PREVENT SQL INJECTION & XSS ATTACK
            $unique_code = Helper::validate_input_text($request->unique_code, TRUE);
            if (!$unique_code) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => $names['unique_code']]));
            }

            // UNIQUE CODE DB VALIDATION
            if ($data->unique_code != $unique_code) {
                $unique_code_db = voucher::select('id')
                    ->where('unique_code', $unique_code)
                    ->first();
            }
            
            if (!empty($unique_code_db)) {
                return back()
                    ->withInput()
                    ->with('error', lang('Unique code ' . $unique_code . ' is already exist. Please use another unique code', $this->translations));
            }

            // HELPER VALIDATION FOR PREVENT SQL INJECTION & XSS ATTACK
            $name = Helper::validate_input_text($request->name, TRUE);
            if (!$name) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => $names['name']]));
            }

            // PERIOD START
            $period_begin = Helper::validate_input_text($request->period_begin);
            $period_begin = str_replace('/', '-', $period_begin);
            if (!$period_begin) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('period begin', $this->translations))]));
            }

            $period_end = Helper::validate_input_text($request->period_end);
            $period_end = str_replace('/', '-', $period_end);
            if (!$period_end) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('period end', $this->translations))]));
            }

            // CUSTOM VALIDATION FOR PERIOD PERIOD
            $date_period_begin = date('Y-m-d H:i:s', strtotime($period_begin));
            $date_period_end = date('Y-m-d H:i:s', strtotime($period_end));
            if ($date_period_end <= $date_period_begin) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('period end', $this->translations))]));
            }

            // CREATE NEW DATA
            $data->unique_code         = strtoupper($unique_code);
            $data->name                = $name;
            $data->voucher_type        = Helper::validate_input_text($request->voucher_type, TRUE);
            $data->discount_type       = Helper::validate_input_text($request->discount_type, TRUE);
            $data->discount_value      = str_replace(',', '', $request->discount_value);
            
            if ($data->discount_type == 'percentage' && $request->discount_max_amount !== NULL) {
                $data->discount_max_amount = (int) str_replace(',', '', $request->discount_max_amount);
            } else {
                $data->discount_max_amount = NULL;
            }

            $data->min_transaction     = (int) str_replace(',', '', $request->min_transaction);
            $data->description         = Helper::validate_input_text($request->description, TRUE);
            $data->qty                 = (int) $request->qty;
            $data->qty_per_user        = (int) $request->qty_per_user;
            $data->period_begin        = $date_period_begin;
            $data->period_end          = $date_period_end;
            $data->is_active           = (int) $request->is_active;
            $data->save();

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
        $success_message = lang(
            'Successfully updated #item : #name',
            $this->translations,
            [
                '#item' => $this->item,
                '#name' => $name
            ]
        );
        if ($request->stay_on_page) {
            return redirect()
                ->route('admin.voucher.edit', $raw_id)
                ->with('success', $success_message);
        } else {
            return redirect()
                ->route('admin.voucher')
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
        $data = voucher::find($id);

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
                ->route('admin.voucher')
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

        return view('admin.voucher.list', compact('deleted_data'));
    }

    /**
     * Get a listing of the deleted resource using DataTables.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_deleted_data(Datatables $datatables, Request $request)
    {
        $query = voucher::onlyTrashed();

        return $datatables->eloquent($query)
            ->addColumn('action', function ($data) {
                $object_id = $data->id;
                if (env('CRYPTOGRAPHY_MODE', false)) {
                    $object_id = Helper::generate_token($data->id);
                }

                $wording_restore = ucwords(lang('restore', $this->translations));
                return '<form action="' . route('admin.voucher.restore') . '" method="POST" onsubmit="return confirm(\'' . lang('Are you sure to restore this #item?', $this->translations, ['#item' => $this->item]) . '\');" style="display: inline"> ' . csrf_field() . ' <input type="hidden" name="id" value="' . $object_id . '">
                <button type="submit" class="btn btn-xs btn-success" title="' . $wording_restore . '"><i class="fa fa-check"></i>&nbsp; ' . $wording_restore . '</button></form>';
            })
            ->editColumn('voucher_type', function ($data) {
                return ucwords($data->voucher_type);
            })
            ->editColumn('discount_type', function ($data) {
                return ucwords($data->discount_type);
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
        $data = voucher::onlyTrashed()->find($id);

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
                ->route('admin.voucher.deleted_data')
                ->with('success', lang('Successfully restored #item', $this->translations, ['#item' => $this->item]));
        }

        # FAILED
        return back()
            ->with('error', lang('Oops, failed to restore #item. Please try again.', $this->translations, ['#item' => $this->item]));
    }
}
