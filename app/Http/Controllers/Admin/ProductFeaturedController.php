<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;

// Libraries
use App\Libraries\Helper;

// Models
use App\Models\product_item;
use App\Models\product_featured;

class ProductFeaturedController extends Controller
{
    // SET THIS MODULE
    private $module     = 'Product Featured';
    private $module_id  = 32;

    // SET THIS OBJECT/ITEM NAME
    private $item       = 'product featured';

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

        return view('admin.product_featured.list');
    }

    /**
     * Get a listing of the resource using DataTables.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_data(Request $request)
    {
        // SET THIS OBJECT/ITEM NAME BASED ON TRANSLATION
        $this->item = ucwords(lang($this->item, $this->translations));

        // AJAX OR API VALIDATOR
        $validation_rules = [
            // 'keyword' => 'required'
        ];

        $validator = Validator::make($request->all(), $validation_rules);
        if ($validator->fails()) {
            return response()->json([
                'status'    => 'false',
                'message'   => 'Validation Error: ' . implode(', ', $validator->errors()->all()),
                'data'      => $validator->errors()->messages()
            ]);
        }

        // GET THE DATA
        $data = product_featured::select(
                'product_featured.*',
                'product_item.name'
            )
            ->leftJoin('product_item', 'product_featured.product_id', 'product_item.id')
            ->orderBy('product_featured.ordinal')
            ->get();

        // MANIPULATE THE DATA
        if (isset($data[0])) {
            foreach ($data as $item) {
                $item->created_at_edited = Helper::locale_timestamp($item->created_at);
                $item->updated_at_edited = Helper::time_ago(strtotime($item->updated_at), lang('ago', $this->translations), Helper::get_periods($this->translations));

                $object_id = $item->id;
                if (env('CRYPTOGRAPHY_MODE', false)) {
                    $object_id = Helper::generate_token($item->product_id);
                }
                
                $status_button = '<form action="' . route('admin.product_featured.update_status') . '" method="POST" onsubmit="return confirm(\'' . lang('Are you sure to unfeatured this #item?', $this->translations, ['#item' => $this->item]) . '\');" style="display: inline"> ' . csrf_field() . ' <input type="hidden" name="id" value="' . $object_id . '">
                <button type="submit" class="btn btn-xs btn-dark" title="unfeatured"><i class="fa fa-star-o"></i>&nbsp; unfeatured</button></form>';

                $item->action = $status_button;
            }
        }

        # SUCCESS
        $response = [
            'status'    => 'true',
            'message'   => 'Successfully get ordered data list',
            'data'      => $data
        ];

        return response()->json($response, 200);
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
                'status'    => 'false',
                'message'   => 'Validation Error: ' . implode(', ', $validator->errors()->all()),
                'data'      => $validator->errors()->messages()
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
                $tmp                = explode('[]=', $item);
                $object             = product_featured::where('product_id', $tmp[1])->update(['ordinal' => $ordinal]);

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
                'status'    => 'false',
                'message'   => $error_msg,
                'data'      => ''
            ]);
        }

        # SUCCESS
        $response = [
            'status'    => 'true',
            'message'   => 'Successfully sort data',
            'data'      => $data
        ];

        return response()->json($response, 200);
    }

    /**
     * Set flag to the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update_status(Request $request)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'Edit');
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
        $data = product_item::find($id);

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
            $data->featured = 0;

            // DELETE FROM TABLE PRODUCT FEATURED
            $exist_data = product_featured::where('product_id', $id)->delete();

            // UPDATE THE DATA
            $data->save();

            $name = $data->name;

            // logging
            $value_after = $data->toJson();
            if ($value_before != $value_after) {
                $log_detail_id  = 7; // update
                $module_id      = $this->module_id;
                $target_id      = $data->id;
                $note           = '"' . $name . '"';
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
        $success_message = lang('Successfully updated #item : #name', $this->translations, ['#item' => $this->item, '#name' => $name]);
        return redirect()->route('admin.product_featured')->with('success', $success_message);
    }
}