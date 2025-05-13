<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

// Libraries
use App\Libraries\Helper;

// Models
use App\Models\product_item;
use App\Models\product_faq;

class ProductFaqController extends Controller
{
    // SET THIS MODULE
    private $module     = 'Product Faq';
    private $module_id  = 24;

    // SET THIS OBJECT/ITEM NAME
    private $item = 'product faq';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($product_item_id)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'View List');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        $raw_product_item_id = $product_item_id;
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $product_item_id = Helper::validate_token($product_item_id);
        }

        $header = product_item::where('id', $product_item_id)->first();
        if (empty($header)) {
            return back()->withInput()->with('error', 'Invalid link', $this->translations);
        }
        
        return view('admin.product_faq.list', compact('raw_product_item_id', 'header'));
    }

    /**
     * Get a listing of the resource using DataTables.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_data($product_item_id, Datatables $datatables, Request $request)
    {
        $raw_product_item_id = $product_item_id;
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $product_item_id = Helper::validate_token($product_item_id);
        }

        $query = product_faq::where('product_item_id', $product_item_id);

        return $datatables->eloquent($query)
            ->addColumn('action', function ($data) use($raw_product_item_id) {
                $object_id = $data->id;
                if (env('CRYPTOGRAPHY_MODE', false)) {
                    $object_id = Helper::generate_token($data->id);
                }

                $wording_edit = ucwords(lang('edit', $this->translations));
                $html = '<a href="' . route('admin.product_faq.edit', ['product_item_id' => $raw_product_item_id, 'id' => $object_id]) . '" class="btn btn-xs btn-primary" title="' . $wording_edit . '"><i class="fa fa-pencil"></i>&nbsp; ' . $wording_edit . '</a>';

                $wording_delete = ucwords(lang('delete', $this->translations));
                $html .= '<form action="' . route('admin.product_faq.delete', $raw_product_item_id) . '" method="POST" onsubmit="return confirm(\'' . lang('Are you sure to delete this #item?', $this->translations, ['#item' => $this->item]) . '\');" style="display: inline"> ' . csrf_field() . ' <input type="hidden" name="id" value="' . $object_id . '">
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
    public function create($product_item_id)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'Add New');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        $raw_product_item_id = $product_item_id;
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $product_item_id = Helper::validate_token($product_item_id);
        }

        $header = product_item::where('id', $product_item_id)->first();
        if (empty($header)) {
            return back()->withInput()->with('error', 'Invalid link', $this->translations);
        }
        
        return view('admin.product_faq.form', compact('raw_product_item_id', 'header'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($product_item_id, Request $request)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'Add New');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        $raw_product_item_id = $product_item_id;
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $product_item_id = Helper::validate_token($product_item_id);
        }

        $header = product_item::where('id', $product_item_id)->first();
        if (empty($header)) {
            return back()->withInput()->with('error', 'Invalid link', $this->translations);
        }

        // SET THIS OBJECT/ITEM NAME BASED ON TRANSLATION
        $this->item = ucwords(lang($this->item, $this->translations));

        // get table name
        $module_table = (new product_faq())->getTable();

        // LARAVEL VALIDATION
        $validation = [
            'question'  => 'required',
            'answer'    => 'required'
        ];
        $message = [
            'required'  => ':attribute ' . lang('should not be empty', $this->translations)
        ];
        $names = [
            'question'  => ucwords(lang('question', $this->translations)),
            'answer'    => ucwords(lang('answer', $this->translations))
        ];
        $this->validate($request, $validation, $message, $names);

        DB::beginTransaction();
        try {
            // DB PROCESS BELOW

            // HELPER VALIDATION FOR PREVENT SQL INJECTION & XSS ATTACK
            $question = Helper::validate_input_text($request->question);
            if (!$question) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('question', $this->translations))]));
            }

            $answer = Helper::validate_input_text($request->answer);
            if (!$answer) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('answer', $this->translations))]));
            }

            // SAVE THE DATA
            $data                   = new product_faq();
            $data->product_item_id  = $header->id;
            $data->question         = $question;
            $data->answer           = $answer;

            // SET ORDER / ORDINAL
            $last                   = product_faq::select('ordinal')->orderBy('ordinal', 'desc')->first();
            $ordinal                = 1;
            if ($last) {
                $ordinal            = $last->ordinal + 1;
            }
            $data->ordinal          = $ordinal;

            $data->save();

            // logging
            $log_detail_id  = 5; // add new
            $module_id      = $this->module_id;
            $target_id      = $data->id;
            $note           = '"' . $data->question . '"';
            $value_before   = null;
            $value_after    = $data;
            $ip_address     = $request->ip();
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
            return back()->withInput()->with('error', $error_msg);
        }

        // SET REDIRECT URL
        $redirect_url = 'admin.product_faq';
        if ($request->stay_on_page) {
            $redirect_url = 'admin.product_faq.create';
        }

        # SUCCESS
        return redirect()
            ->route($redirect_url, $raw_product_item_id)
            ->with('success', lang('Successfully added a new #item', $this->translations, ['#item' => $this->item]));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  id   $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function edit($product_item_id, $id, Request $request)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'View Details');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        $raw_product_item_id = $product_item_id;
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $product_item_id = Helper::validate_token($product_item_id);
        }

        $header = product_item::where('id', $product_item_id)->first();
        if (empty($header)) {
            return back()->withInput()->with('error', 'Invalid link', $this->translations);
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
                ->route('admin.product_faq', $raw_product_item_id)
                ->with('error', lang('Invalid #item ID, please check your link again', $this->translations, ['#item' => $this->item]));
        }

        // GET DATA BY ID
        $data = product_faq::find($id);

        // CHECK IS DATA FOUND
        if (!$data) {
            # FAILED - DATA NOT FOUND
            return redirect()
                ->route('admin.product_faq', $raw_product_item_id)
                ->with('error', lang('#item not found, please check your link again', $this->translations, ['#item' => $this->item]));
        }

        return view('admin.product_faq.form', compact('data', 'raw_id', 'raw_product_item_id', 'header'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  id   $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update($product_item_id, $id, Request $request)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'Edit');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        // HEADER
        $raw_product_item_id = $product_item_id;
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $product_item_id = Helper::validate_token($product_item_id);
        }

        $header = product_item::where('id', $product_item_id)->first();
        if (empty($header)) {
            return back()->withInput()->with('error', 'Invalid link', $this->translations);
        }

        // SET THIS OBJECT/ITEM NAME BASED ON TRANSLATION
        $this->item = ucwords(lang($this->item, $this->translations));

        $raw_id = $id;

        if (env('CRYPTOGRAPHY_MODE', false)) {
            $id = Helper::validate_token($id);
        }

        // GET DATA BY ID
        $data = product_faq::where('id', $id)->where('product_item_id', $product_item_id)->first();

        // CHECK IS DATA FOUND
        if (empty($data)) {
            # FAILED - DATA NOT FOUND
            return back()
                ->withInput()
                ->with('error', lang('#item not found, please reload your page before resubmit', $this->translations, ['#item' => $this->item]));
        }

        // store data before updated
        $value_before = $data->toJson();

        // LARAVEL VALIDATION
        $validation = [
            'question'  => 'required',
            'answer'    => 'required'
        ];
        $message = [
            'required'  => ':attribute ' . lang('should not be empty', $this->translations)
        ];
        $names = [
            'question'  => ucwords(lang('question', $this->translations)),
            'answer'    => ucwords(lang('answer', $this->translations))
        ];
        $this->validate($request, $validation, $message, $names);

        DB::beginTransaction();
        try {
            // DB PROCESS BELOW

            // HELPER VALIDATION FOR PREVENT SQL INJECTION & XSS ATTACK
            // HELPER VALIDATION FOR PREVENT SQL INJECTION & XSS ATTACK
            $question = Helper::validate_input_text($request->question);
            if (!$question) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('question', $this->translations))]));
            }

            $answer = Helper::validate_input_text($request->answer);
            if (!$answer) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => ucwords(lang('answer', $this->translations))]));
            }

            // SAVE THE DATA
            $data->question = $question;
            $data->answer   = $answer;
            $data->save();

            // logging
            $value_after = $data->toJson();
            if ($value_before != $value_after) {
                $log_detail_id  = 7; // update
                $module_id      = $this->module_id;
                $target_id      = $data->id;
                $note           = '"' . $question . '"';
                $ip_address     = $request->ip();
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
            return back()->withInput()->with('error', $error_msg);
        }

        # SUCCESS
        $success_message = lang('Successfully updated #item', $this->translations, ['#item' => $this->item]);
        if ($request->stay_on_page) {
            return redirect()->route('admin.product_faq.edit', ['product_item_id' => $raw_product_item_id,'id' => $raw_id])->with('success', $success_message);
        } else {
            return redirect()->route('admin.product_faq', $raw_product_item_id)->with('success', $success_message);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function delete($product_item_id, Request $request)
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
        $data = product_faq::find($id);

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
            $note           = '"' . $data->question . '"';
            $value_after    = null;
            $ip_address     = $request->ip();
            Helper::logging($log_detail_id, $module_id, $target_id, $note, $value_before, $value_after, $ip_address);

            # SUCCESS
            return redirect()
                ->route('admin.product_faq', $product_item_id)
                ->with('success', lang('Successfully deleted #item', $this->translations, ['#item' => $this->item]));
        }

        # FAILED
        return back()->with('error', lang('Oops, failed to delete #item. Please try again.', $this->translations, ['#item' => $this->item]));
    }

    /**
     * Display a listing of the deleted resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function deleted_data($raw_product_item_id)
    {
        $deleted_data = true;

        if (env('CRYPTOGRAPHY_MODE', false)) {
            $product_item_id = Helper::validate_token($raw_product_item_id);
        }

        $header = product_item::where('id', $product_item_id)->first();
        if (empty($header)) {
            return back()->withInput()->with('error', 'Invalid link', $this->translations);
        }

        return view('admin.product_faq.list', compact('deleted_data', 'raw_product_item_id', 'header'));
    }

    /**
     * Get a listing of the deleted resource using DataTables.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_deleted_data($raw_product_item_id, Datatables $datatables, Request $request)
    {
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $product_item_id = Helper::validate_token($raw_product_item_id);
        }

        $query = product_faq::where('product_item_id', $product_item_id)->onlyTrashed();

        return $datatables->eloquent($query)
            ->addColumn('action', function ($data) use($raw_product_item_id) {
                $object_id = $data->id;
                if (env('CRYPTOGRAPHY_MODE', false)) {
                    $object_id = Helper::generate_token($data->id);
                }

                $wording_restore = ucwords(lang('restore', $this->translations));
                return '<form action="' . route('admin.product_faq.restore', $raw_product_item_id) . '" method="POST" onsubmit="return confirm(\'' . lang('Are you sure to restore this #item?', $this->translations, ['#item' => $this->item]) . '\');" style="display: inline"> ' . csrf_field() . ' <input type="hidden" name="id" value="' . $object_id . '">
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
    public function restore($raw_product_item_id, Request $request)
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
        $data = product_faq::onlyTrashed()->find($id);

        // CHECK IS DATA FOUND
        if (!$data) {
            # FAILED - DATA NOT FOUND
            return back()
                ->withInput()
                ->with('error', lang('#item not found, please reload your page before resubmit', $this->translations, ['#item' => $this->item]));
        }

        // RESTORE THE DATA
        if ($data->restore()) {
            // logging
            $log_detail_id  = 9; // restore
            $module_id      = $this->module_id;
            $target_id      = $data->id;
            $note           = '"' . $data->question . '"';
            $value_before   = null;
            $value_after    = $data->toJson();
            $ip_address     = $request->ip();
            Helper::logging($log_detail_id, $module_id, $target_id, $note, $value_before, $value_after, $ip_address);

            # SUCCESS
            return redirect()
                ->route('admin.product_faq.deleted_data', $raw_product_item_id)
                ->with('success', lang('Successfully restored #item', $this->translations, ['#item' => $this->item]));
        }

        # FAILED
        return back()->with('error', lang('Oops, failed to restore #item. Please try again.', $this->translations, ['#item' => $this->item]));
    }
}