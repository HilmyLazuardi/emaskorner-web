<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

// LIBRARIES
use App\Libraries\Helper;

// MODELS
use App\Models\blog_category;

class BlogCategoryController extends Controller
{
    // SET THIS MODULE
    private $module     = 'Blog Category';
    private $module_id  = 41;
    private $item       = 'blog category'; // SET THIS OBJECT/ITEM NAME

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

        return view('admin.blog_category.list');
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
        $data = blog_category::orderBy('ordinal')->get();

        // MANIPULATE THE DATA
        if (isset($data[0])) {
            $wording_edit   = ucwords(lang("edit", $this->translations));
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

                $edit_button    = '<a href="' . route('admin.blog_category.edit', $object_id) . '" class="btn btn-xs btn-primary" title="' . $wording_edit . '"><i class="fa fa-pencil"></i>&nbsp; ' . $wording_edit . '</a>';
                $delete_button  = '<form action="' . route('admin.blog_category.delete') . '" method="POST" onsubmit="return confirm(\'' . lang('Are you sure to delete this #item?', $this->translations, ['#item' => $this->item]) . '\');" style="display: inline"> ' . csrf_field() . ' <input type="hidden" name="id" value="' . $object_id . '">
                <button type="submit" class="btn btn-xs btn-danger" title="' . $wording_delete . '"><i class="fa fa-trash"></i>&nbsp; ' . $wording_delete . '</button></form>';

                $item->action   = $edit_button . '<br><br>' . $delete_button;
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

        return view('admin.blog_category.form');
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
            'name' => 'required'
        ];

        $message = [
            'required' => ':attribute ' . lang('should not be empty', $this->translations),
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
            $data               = new blog_category();
            $data->name         = $name;
            $data->description  = Helper::validate_input_text($request->description);
            $data->status       = (int) $request->status;

            // SLUG
            $slug = Helper::generate_slug($data->name);
            if ($request->slug) {
                $slug = Helper::generate_slug($request->slug);
            }

            // MAKE SURE SLUG IS UNIQUE
            $slug       = Helper::check_unique('blog_category', $slug);
            $data->slug = $slug;

            // SET ORDER / ORDINAL
            $last       = blog_category::select('ordinal')->orderBy('ordinal', 'desc')->first();
            $ordinal    = 1;

            if ($last) {
                $ordinal = $last->ordinal + 1;
            }

            $data->ordinal = $ordinal;
            $data->save();

            // LOGGING
            $log_detail_id  = 5; // ADD NEW
            $module_id      = $this->module_id;
            $target_id      = $data->id;
            $note           = '"' . $name . '"';
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
        $redirect_url       = 'admin.blog_category';
        if ($request->stay_on_page) {
            $redirect_url   = 'admin.blog_category.create';
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
        $raw_id     = $id;

        if (env('CRYPTOGRAPHY_MODE', false)) {
            $id     = Helper::validate_token($id);
        }

        // CHECK OBJECT ID
        if ((int) $id < 1) {
            // INVALID OBJECT ID
            return redirect()
                ->route('admin.blog_category')
                ->with('error', lang('Invalid #item ID, please check your link again', $this->translations, ['#item' => $this->item]));
        }

        // GET DATA BY ID
        $data = blog_category::find($id);

        // CHECK IS DATA FOUND
        if (!$data) {
            # FAILED - DATA NOT FOUND
            return redirect()
                ->route('admin.blog_category')
                ->with('error', lang('#item not found, please check your link again', $this->translations, ['#item' => $this->item]));
        }

        return view('admin.blog_category.form', compact('data', 'raw_id'));
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
        $raw_id     = $id;

        if (env('CRYPTOGRAPHY_MODE', false)) {
            $id = Helper::validate_token($id);
        }

        // GET DATA BY ID
        $data = blog_category::find($id);

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
            $data->name         = $name;
            $data->description  = Helper::validate_input_text($request->description);
            $data->status       = (int) $request->status;

            // MAKE SURE SLUG IS UNIQUE (FOR UPDATE DATA)
            $slug = Helper::generate_slug($data->name);
            if ($request->slug) {
                $slug = Helper::generate_slug($request->slug);
            }

            // MAKE SURE SLUG IS UNIQUE
            if ($data->slug != $slug) {
                $slug = Helper::check_unique('blog_category', $slug);
            }
            $data->slug = $slug;
            $data->save();

            // LOGGING
            $value_after = $data->toJson();
            if ($value_before != $value_after) {
                $log_detail_id  = 7; // UPDATE
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
        if ($request->stay_on_page) {
            return redirect()->route('admin.blog_category.edit', $raw_id)->with('success', $success_message);
        } else {
            return redirect()->route('admin.blog_category')->with('success', $success_message);
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
        $id         = $request->id;

        if (env('CRYPTOGRAPHY_MODE', false)) {
            $id     = Helper::validate_token($id);
        }

        // GET DATA BY ID
        $data       = blog_category::find($id);

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
            // LOGGING
            $log_detail_id  = 8; // DELETE
            $module_id      = $this->module_id;
            $target_id      = $data->id;
            $note           = '"' . $data->name . '"';
            $value_after    = null;
            $ip_address     = $request->ip();
            Helper::logging($log_detail_id, $module_id, $target_id, $note, $value_before, $value_after, $ip_address);

            # SUCCESS
            return redirect()
                ->route('admin.blog_category')
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
    public function deleted_data()
    {
        $deleted_data = true;
        return view('admin.blog_category.list', compact('deleted_data'));
    }

    /**
     * Get a listing of the deleted resource using DataTables.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_deleted_data(Datatables $datatables, Request $request)
    {
        $query = blog_category::onlyTrashed();
        return $datatables->eloquent($query)
            ->addColumn('action', function ($data) {
                $object_id = $data->id;
                if (env('CRYPTOGRAPHY_MODE', false)) {
                    $object_id = Helper::generate_token($data->id);
                }

                $wording_restore = ucwords(lang('restore', $this->translations));
                return '<form action="' . route('admin.blog_category.restore') . '" method="POST" onsubmit="return confirm(\'' . lang('Are you sure to restore this #item?', $this->translations, ['#item' => $this->item]) . '\');" style="display: inline"> ' . csrf_field() . ' <input type="hidden" name="id" value="' . $object_id . '">
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
        $id         = $request->id;
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $id     = Helper::validate_token($id);
        }

        // GET DATA BY ID
        $data = blog_category::onlyTrashed()->find($id);
        // CHECK IS DATA FOUND
        if (!$data) {
            # FAILED - DATA NOT FOUND
            return back()->withInput()->with('error', lang('#item not found, please reload your page before resubmit', $this->translations, ['#item' => $this->item]));
        }

        // RESTORE THE DATA
        if ($data->restore()) {
            // LOGGING
            $log_detail_id  = 9; // RESTORE
            $module_id      = $this->module_id;
            $target_id      = $data->id;
            $note           = '"' . $data->name . '"';
            $value_before   = null;
            $value_after    = $data->toJson();
            $ip_address     = $request->ip();
            Helper::logging($log_detail_id, $module_id, $target_id, $note, $value_before, $value_after, $ip_address);

            # SUCCESS
            return redirect()
                ->route('admin.blog_category.deleted_data')
                ->with('success', lang('Successfully restored #item', $this->translations, ['#item' => $this->item]));
        }

        # FAILED
        return back()->with('error', lang('Oops, failed to restore #item. Please try again.', $this->translations, ['#item' => $this->item]));
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

        // CONVERT TO ARRAY
        $data = explode('&', $rows);

        DB::beginTransaction();
        try {
            // DB PROCESS BELOW

            $ordinal = 1;
            foreach ($data as $item) {
                // SPLIT THE DATA
                $tmp                = explode('[]=', $item);
                $object             = blog_category::find($tmp[1]);
                $object->ordinal    = $ordinal;
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
}