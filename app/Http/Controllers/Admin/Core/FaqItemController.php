<?php

namespace App\Http\Controllers\Admin\Core;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

// Libraries
use App\Libraries\Helper;

// Models
use App\Models\faq;

class FaqItemController extends Controller
{
    // SET THIS MODULE
    private $module = 'FAQ';
    private $module_id = 17;

    // SET THIS OBJECT/ITEM NAME
    private $item = 'FAQ sub menu';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($parent)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'View List');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        $raw_parent = $parent;
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $parent = Helper::validate_token($parent);
        }

        // get table system name
        $table_faq = (new faq())->getTable();
        
        $parent_data = faq::select(
            $table_faq . '.*',
            'parent.id AS parent_id'
        )
            ->leftJoin($table_faq . ' AS parent', $table_faq . '.parent_id', 'parent.id')
            ->where($table_faq . '.id', $parent)
            ->first();
        if (!$parent_data) {
            return back()
                ->with('error', lang('Invalid #item ID, please check your link again', $this->translations, ['#item' => 'Parent FAQ']));
        }

        return view('admin.core.faq_item.list', compact('raw_parent', 'parent_data'));
    }

    /**
     * Get a listing of the resource using DataTables.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_data($parent, Request $request)
    {
        // SET THIS OBJECT/ITEM NAME BASED ON TRANSLATION
        $this->item = ucwords(lang($this->item, $this->translations));

        $raw_parent = $parent;
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $parent = Helper::validate_token($parent);
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

         // get table system name
         $table_faq = (new faq())->getTable();

        // GET THE DATA
        $data = faq::select(
            $table_faq . '.*',
            DB::raw('COUNT(sub.id) AS total_subs')
        )
            ->leftJoin($table_faq . ' AS sub', $table_faq . '.id', 'sub.parent_id')
            ->where($table_faq . '.parent_id', $parent)
            ->groupBy(
                $table_faq . '.id',
                $table_faq . '.text_1',
                $table_faq . '.text_2',
                $table_faq . '.level',
                $table_faq . '.parent_id',
                $table_faq . '.ordinal',
                $table_faq . '.status',
                $table_faq . '.created_at',
                $table_faq . '.updated_at',
                $table_faq . '.deleted_at'
            )
            ->orderBy($table_faq . '.ordinal')
            ->get();

        // MANIPULATE THE DATA
        if (isset($data[0])) {
            $wording_edit = ucwords(lang("edit", $this->translations));
            $wording_delete = ucwords(lang("delete", $this->translations));
            $wording_set = ucwords(lang("set #item", $this->translations, ['#item' => lang('sub menu', $this->translations)]));

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

                $edit_button = '<a href="' . route('admin.faq_item.edit', [$raw_parent, $object_id]) . '" class="btn btn-xs btn-primary" title="' . $wording_edit . '"><i class="fa fa-pencil"></i>&nbsp; ' . $wording_edit . '</a>';

                $delete_button = '<form action="' . route('admin.faq.delete') . '" method="POST" onsubmit="return confirm(\'' . lang('Are you sure to delete this #item?', $this->translations, ['#item' => $this->item]) . '\');" style="display: inline"> ' . csrf_field() . ' <input type="hidden" name="id" value="' . $object_id . '">
                <button type="submit" class="btn btn-xs btn-danger" title="' . $wording_delete . '"><i class="fa fa-trash"></i>&nbsp; ' . $wording_delete . '</button></form>';

                $total_subs = '';
                if ($item->total_subs > 0) {
                    $total_subs = ' (' . $item->total_subs . ')';
                }
                $set_button = '<a href="' . route('admin.faq_item', [$object_id]) . '" class="btn btn-xs btn-info" title="' . $wording_set . '"><i class="fa fa-sitemap"></i>&nbsp; ' . $wording_set . $total_subs . '</a>';

                $item->action = $edit_button . '<br>' . $set_button . '<br><br>' . $delete_button;
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
    public function create($parent)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'Add New');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        $raw_parent = $parent;
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $parent = Helper::validate_token($parent);
        }

        $parent_data = faq::find($parent);
        if (!$parent_data) {
            return back()
                ->with('error', lang('Invalid #item ID, please check your link again', $this->translations, ['#item' => 'Parent FAQ']));
        }

        return view('admin.core.faq_item.form', compact('raw_parent', 'parent_data'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($parent, Request $request)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'Add New');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        $raw_parent = $parent;
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $parent = Helper::validate_token($parent);
        }

        $parent_data = faq::find($parent);
        if (!$parent_data) {
            return back()
                ->withInput()
                ->with('error', lang('Invalid #item ID, please check your link again', $this->translations, ['#item' => 'Parent FAQ']));
        }

        // SET THIS OBJECT/ITEM NAME BASED ON TRANSLATION
        $this->item = ucwords(lang($this->item, $this->translations));

        // LARAVEL VALIDATION
        $validation = [
            'text_1' => 'required',
            'text_2' => 'required',
        ];
        $message = [
            'required' => ':attribute ' . lang('should not be empty', $this->translations),
        ];
        $names = [
            'text_1' => ucwords(lang('question', $this->translations)),
            'text_2' => ucwords(lang('answer', $this->translations)),
        ];
        $this->validate($request, $validation, $message, $names);

        DB::beginTransaction();
        try {
            // DB PROCESS BELOW

            // SAVE THE DATA
            $data = new faq();

            // HELPER VALIDATION FOR PREVENT SQL INJECTION & XSS ATTACK
            $text_1 = Helper::validate_input_text($request->text_1);
            if (!$text_1) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => $names['text_1']]));
            }
            $data->text_1 = $text_1;

            $text_2 = Helper::validate_input_text($request->text_2);
            if (!$text_2) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => $names['text_2']]));
            }
            $data->text_2 = $text_2;

            $data->level = $parent_data->level + 1;
            $data->parent_id = $parent_data->id;

            $data->status = (int) $request->status;

            // SET ORDER / ORDINAL
            $last = faq::select('ordinal')->orderBy('ordinal', 'desc')->first();
            $ordinal = 1;
            if ($last) {
                $ordinal = $last->ordinal + 1;
            }
            $data->ordinal = $ordinal;

            $data->save();

            $item_name = $data->text_1;

            // logging
            $log_detail_id = 5; // add new
            $module_id = $this->module_id;
            $target_id = $data->id;
            $note = '"' . $item_name . '"';
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
        $redirect_url = 'admin.faq_item';
        $param_url = [$raw_parent];
        if ($request->stay_on_page) {
            $redirect_url = 'admin.faq_item.create';
        }

        # SUCCESS
        return redirect()
            ->route($redirect_url, $param_url)
            ->with('success', lang('Successfully added a new #item : #name', $this->translations, ['#item' => $this->item, '#name' => $item_name]));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  id   $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function edit($parent, $id, Request $request)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'View Details');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        $raw_parent = $parent;
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $parent = Helper::validate_token($parent);
        }

        $parent_data = faq::find($parent);
        if (!$parent_data) {
            return back()
                ->with('error', lang('Invalid #item ID, please check your link again', $this->translations, ['#item' => 'Parent FAQ']));
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
                ->route('admin.faq')
                ->with('error', lang('Invalid #item ID, please check your link again', $this->translations, ['#item' => $this->item]));
        }

        // GET DATA BY ID
        $data = faq::find($id);

        // CHECK IS DATA FOUND
        if (!$data) {
            # FAILED - DATA NOT FOUND
            return redirect()
                ->route('admin.nav_menu')
                ->with('error', lang('#item not found, please check your link again', $this->translations, ['#item' => $this->item]));
        }

        return view('admin.core.faq_item.form', compact('data', 'raw_id', 'raw_parent', 'parent_data'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  id   $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update($parent, $id, Request $request)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'Edit');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        $raw_parent = $parent;
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $parent = Helper::validate_token($parent);
        }

        // SET THIS OBJECT/ITEM NAME BASED ON TRANSLATION
        $this->item = ucwords(lang($this->item, $this->translations));

        $raw_id = $id;

        if (env('CRYPTOGRAPHY_MODE', false)) {
            $id = Helper::validate_token($id);
        }

        // GET DATA BY ID
        $data = faq::find($id);

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
            'text_1' => 'required',
            'text_2' => 'required',
        ];
        $message = [
            'required' => ':attribute ' . lang('should not be empty', $this->translations),
        ];
        $names = [
            'text_1' => ucwords(lang('question', $this->translations)),
            'text_2' => ucwords(lang('answer', $this->translations)),
        ];
        $this->validate($request, $validation, $message, $names);

        DB::beginTransaction();
        try {
            // DB PROCESS BELOW

            // HELPER VALIDATION FOR PREVENT SQL INJECTION & XSS ATTACK
            $text_1 = Helper::validate_input_text($request->text_1);
            if (!$text_1) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => $names['text_1']]));
            }
            $data->text_1 = $text_1;

            $text_2 = Helper::validate_input_text($request->text_2);
            if (!$text_2) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => $names['text_2']]));
            }
            $data->text_2 = $text_2;

            $data->status = (int) $request->status;

            $data->save();

            $item_name = $data->text_1;

            // logging
            $value_after = $data->toJson();
            if ($value_before != $value_after) {
                $log_detail_id = 7; // update
                $module_id = $this->module_id;
                $target_id = $data->id;
                $note = '"' . $item_name . '"';
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
        $success_message = lang('Successfully updated #item : #name', $this->translations, ['#item' => $this->item, '#name' => $item_name]);
        if ($request->stay_on_page) {
            return redirect()
                ->route('admin.faq_item.edit', [$raw_parent, $raw_id])
                ->with('success', $success_message);
        } else {
            return redirect()
                ->route('admin.faq_item', [$raw_parent])
                ->with('success', $success_message);
        }
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

                $object = faq::find($tmp[1]);
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
