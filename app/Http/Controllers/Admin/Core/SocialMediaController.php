<?php

namespace App\Http\Controllers\Admin\Core;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

// Libraries
use App\Libraries\Helper;

// Models
use App\Models\social_media;

class SocialMediaController extends Controller
{
    // SET THIS MODULE
    private $module = 'Social Media';
    private $module_id = 16;

    // SET THIS OBJECT/ITEM NAME
    private $item = 'social media';

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

        return view('admin.core.social_media.list');
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
                'status' => 'false',
                'message' => 'Validation Error: ' . implode(', ', $validator->errors()->all()),
                'data' => $validator->errors()->messages()
            ]);
        }

        // GET THE DATA
        $data = social_media::orderBy('ordinal')->get();

        // MANIPULATE THE DATA
        if (isset($data[0])) {
            $wording_edit = ucwords(lang("edit", $this->translations));
            $wording_delete = ucwords(lang("delete", $this->translations));
            $wording_set = ucwords(lang("set #item", $this->translations, ['#item' => lang('branch', $this->translations)]));

            foreach ($data as $item) {
                $item->status_label = '<span class="label label-success">' . ucwords(lang('active', $this->translations)) . '</span>';
                if ($item->status != 1) {
                    $item->status_label = '<span class="label label-danger"><i>' . ucwords(lang('inactive', $this->translations)) . '</i></span>';
                }

                $item->created_at_edited = Helper::locale_timestamp($item->created_at);
                $item->updated_at_edited = Helper::time_ago(strtotime($item->updated_at), lang('ago', $this->translations), Helper::get_periods($this->translations));

                $item->image_item = asset('images/no-image.png');
                if (!empty($item->logo)) {
                    $item->image_item = asset($item->logo);
                }

                $object_id = $item->id;
                if (env('CRYPTOGRAPHY_MODE', false)) {
                    $object_id = Helper::generate_token($item->id);
                }

                $edit_button = '<a href="' . route('admin.social_media.edit', $object_id) . '" class="btn btn-xs btn-primary" title="' . $wording_edit . '"><i class="fa fa-pencil"></i>&nbsp; ' . $wording_edit . '</a>';

                $delete_button = '<form action="' . route('admin.social_media.delete') . '" method="POST" onsubmit="return confirm(\'' . lang('Are you sure to delete this #item?', $this->translations, ['#item' => $this->item]) . '\');" style="display: inline"> ' . csrf_field() . ' <input type="hidden" name="id" value="' . $object_id . '">
                <button type="submit" class="btn btn-xs btn-danger" title="' . $wording_delete . '"><i class="fa fa-trash"></i>&nbsp; ' . $wording_delete . '</button></form>';

                $item->action = $edit_button . '<br>' . $delete_button;
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
    public function create()
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'Add New');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        return view('admin.core.social_media.form');
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
        // if upload image
        if ($request->file('logo')) {
            $validation['logo'] = 'required|image|max:2048';
        }
        $message = [
            'required' => ':attribute ' . lang('should not be empty', $this->translations),
            'image' => ':attribute ' . lang('must be an image', $this->translations),
            'max' => ':attribute ' . lang('may not be greater than #item', $this->translations, ['#item' => '2MB']),
        ];
        $names = [
            'name' => ucwords(lang('name', $this->translations)),
            'link' => ucwords(lang('link', $this->translations)),
            'logo' => ucwords(lang('logo', $this->translations)),
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
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => $names['name']]));
            }

            // SAVE THE DATA
            $data = new social_media();
            $data->name = $name;

            if ($request->file('logo')) {
                // UPLOAD IMAGE
                $dir_path = 'uploads/social_media/';
                $image_file = $request->file('logo');
                $format_image_name = Helper::generate_slug($data->name) . '-' . time() . '-logo';
                $allowed_extensions = ['jpeg', 'jpg', 'png'];
                $image = Helper::upload_image($dir_path, $image_file, true, $format_image_name, $allowed_extensions);
                if ($image['status'] != 'true') {
                    # FAILED TO UPLOAD IMAGE
                    return back()
                        ->withInput()
                        ->with('error', lang($image['message'], $this->translations, $image['dynamic_objects']));
                }

                $data->logo = $dir_path . $image['data'];
            }

            $link = Helper::validate_input_url($request->link);
            if (!$link) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => $names['link']]));
            }
            $data->link = $link;
            
            $data->status = (int) $request->status;

            // SET ORDER / ORDINAL
            $last = social_media::select('ordinal')->orderBy('ordinal', 'desc')->first();
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
        $redirect_url = 'admin.social_media';
        if ($request->stay_on_page) {
            $redirect_url = 'admin.social_media.create';
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
                ->route('admin.social_media')
                ->with('error', lang('Invalid #item ID, please check your link again', $this->translations, ['#item' => $this->item]));
        }

        // GET DATA BY ID
        $data = social_media::find($id);

        // CHECK IS DATA FOUND
        if (!$data) {
            # FAILED - DATA NOT FOUND
            return redirect()
                ->route('admin.social_media')
                ->with('error', lang('#item not found, please check your link again', $this->translations, ['#item' => $this->item]));
        }

        return view('admin.core.social_media.form', compact('data', 'raw_id'));
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
        $data = social_media::find($id);

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
        // if upload image
        if ($request->file('logo')) {
            $validation['logo'] = 'required|image|max:2048';
        }
        $message = [
            'required' => ':attribute ' . lang('should not be empty', $this->translations),
            'image' => ':attribute ' . lang('must be an image', $this->translations),
            'max' => ':attribute ' . lang('may not be greater than #item', $this->translations, ['#item' => '2MB']),
        ];
        $names = [
            'name' => ucwords(lang('name', $this->translations)),
            'link' => ucwords(lang('link', $this->translations)),
            'logo' => ucwords(lang('logo', $this->translations)),
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
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => $names['name']]));
            }
            $data->name = $name;

            if ($request->file('logo')) {
                // UPLOAD IMAGE
                $dir_path = 'uploads/social_media/';
                $image_file = $request->file('logo');
                $format_image_name = Helper::generate_slug($data->name) . '-' . time() . '-logo';
                $allowed_extensions = ['jpeg', 'jpg', 'png'];
                $image = Helper::upload_image($dir_path, $image_file, true, $format_image_name, $allowed_extensions);
                if ($image['status'] != 'true') {
                    # FAILED TO UPLOAD IMAGE
                    return back()
                        ->withInput()
                        ->with('error', lang($image['message'], $this->translations, $image['dynamic_objects']));
                }

                $data->logo = $dir_path . $image['data'];
            }

            $link = Helper::validate_input_url($request->link);
            if (!$link) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => $names['link']]));
            }
            $data->link = $link;

            $data->status = (int) $request->status;

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
        $success_message = lang('Successfully updated #item : #name', $this->translations, ['#item' => $this->item, '#name' => $name]);
        if ($request->stay_on_page) {
            return redirect()
                ->route('admin.social_media.edit', $raw_id)
                ->with('success', $success_message);
        } else {
            return redirect()
                ->route('admin.social_media')
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

        $id = $request->id;
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $id = Helper::validate_token($id);
        }

        // GET DATA BY ID
        $data = social_media::find($id);

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
                ->route('admin.social_media')
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

        return view('admin.core.social_media.list', compact('deleted_data'));
    }

    /**
     * Get a listing of the deleted resource using DataTables.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_deleted_data(Datatables $datatables, Request $request)
    {
        $query = social_media::onlyTrashed();

        return $datatables->eloquent($query)
            ->addColumn('action', function ($data) {
                $object_id = $data->id;
                if (env('CRYPTOGRAPHY_MODE', false)) {
                    $object_id = Helper::generate_token($data->id);
                }

                $wording_restore = ucwords(lang('restore', $this->translations));
                return '<form action="' . route('admin.social_media.restore') . '" method="POST" onsubmit="return confirm(\'' . lang('Are you sure to restore this #item?', $this->translations, ['#item' => $this->item]) . '\');" style="display: inline"> ' . csrf_field() . ' <input type="hidden" name="id" value="' . $object_id . '">
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

        $id = $request->id;
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $id = Helper::validate_token($id);
        }

        // GET DATA BY ID
        $data = social_media::onlyTrashed()->find($id);

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
                ->route('admin.social_media.deleted_data')
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

                $object = social_media::find($tmp[1]);
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
