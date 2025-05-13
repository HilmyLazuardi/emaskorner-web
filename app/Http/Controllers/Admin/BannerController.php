<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

// Libraries
use App\Libraries\Helper;

// Models
use App\Models\banner;

class BannerController extends Controller
{
    // SET THIS MODULE
    private $module = 'Banner';
    private $module_id = 20;

    // SET THIS OBJECT/ITEM NAME
    private $item = 'banner';

    private $positions = ['home', 'blog'];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($position)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'View List');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        if (!in_array($position, $this->positions)) {
            return redirect()
                ->route('admin.home')
                ->with('error', lang('Invalid position for #item', $this->translations, ['#item' => $this->item]));
        }

        return view('admin.banner.list', compact('position'));
    }

    /**
     * Get a listing of the resource using DataTables.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_data($position, Request $request)
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

        if (!in_array($position, $this->positions)) {
            $response = [
                'status' => 'false',
                'message' => lang('Invalid position for #item', $this->translations, ['#item' => $this->item]),
                'data' => ''
            ];
            return response()->json($response, 200);
        }

        // GET THE DATA
        $data = banner::where('position', $position)->orderBy('ordinal')->get();

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

                $edit_button = '<a href="' . route('admin.banner.edit', [$position, $object_id]) . '" class="btn btn-xs btn-primary" title="' . $wording_edit . '"><i class="fa fa-pencil"></i>&nbsp; ' . $wording_edit . '</a>';

                $delete_button = '<form action="' . route('admin.banner.delete', $position) . '" method="POST" onsubmit="return confirm(\'' . lang('Are you sure to delete this #item?', $this->translations, ['#item' => $this->item]) . '\');" style="display: inline"> ' . csrf_field() . ' <input type="hidden" name="id" value="' . $object_id . '">
                <button type="submit" class="btn btn-xs btn-danger" title="' . $wording_delete . '"><i class="fa fa-trash"></i>&nbsp; ' . $wording_delete . '</button></form>';

                $item->action = $edit_button . '<br><br>' . $delete_button;

                $item->image_show = asset($item->image_thumb);

                $item->image_mobile_show = asset($item->image_mobile_thumb);
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
    public function create($position)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'Add New');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        if (!in_array($position, $this->positions)) {
            return redirect()
                ->route('admin.home')
                ->with('error', lang('Invalid position for #item', $this->translations, ['#item' => $this->item]));
        }

        return view('admin.banner.form', compact('position'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($position, Request $request)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'Add New');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        if (!in_array($position, $this->positions)) {
            return back()
                ->withInput()
                ->with('error', lang('Invalid position for #item', $this->translations, ['#item' => $this->item]));
        }

        // SET THIS OBJECT/ITEM NAME BASED ON TRANSLATION
        $this->item = ucwords(lang($this->item, $this->translations));

        // LARAVEL VALIDATION
        $validation = [
            'name' => 'required',
            'link_type' => 'required',
            'image' => 'required|image|max:2048',
        ];
        $message = [
            'required' => ':attribute ' . lang('should not be empty', $this->translations),
            'image' => ':attribute ' . lang('must be an image', $this->translations),
            'max' => ':attribute ' . lang('may not be greater than #item', $this->translations, ['#item' => '2MB']),
        ];
        $names = [
            'name' => ucwords(lang('name', $this->translations)),
            'link_type' => ucwords(lang('link type', $this->translations)),
            'image' => ucwords(lang('image', $this->translations)),
        ];
        $this->validate($request, $validation, $message, $names);

        DB::beginTransaction();
        try {
            // DB PROCESS BELOW

            // SAVE THE DATA
            $data = new banner();
            $data->position = $position;

            if ($request->file('image')) {
                // UPLOAD IMAGE
                if ($position == 'home') {
                    $dir_path = 'uploads/banner/';
                } else {
                    $dir_path = 'uploads/banner/' . $position . '/';
                }
                $image_file = $request->file('image');
                $format_image_name = Helper::unique_string();
                $allowed_extensions = ['jpeg', 'jpg', 'png', 'gif'];
                $generate_thumbnail = true;
                $thumbnail_width = 200;
                $thumbnail_height = 200;
                $thumbnail_quality_percentage = 80;
                $image = Helper::upload_image($dir_path, $image_file, true, $format_image_name, $allowed_extensions, $generate_thumbnail, $thumbnail_width, $thumbnail_height, $thumbnail_quality_percentage);
                if ($image['status'] != 'true') {
                    # FAILED TO UPLOAD IMAGE
                    return back()
                        ->withInput()
                        ->with('error', lang($image['message'], $this->translations, $image['dynamic_objects']));
                }

                $data->image = $dir_path . $image['data'];
                $data->image_thumb = $dir_path . $image['thumbnail'];
            }

            if ($request->file('image_mobile')) {
                // UPLOAD IMAGE
                if ($position == 'home') {
                    $dir_path = 'uploads/banner/';
                } else {
                    $dir_path = 'uploads/banner/' . $position . '/';
                }
                $image_file = $request->file('image_mobile');
                $format_image_name = Helper::unique_string();
                $allowed_extensions = ['jpeg', 'jpg', 'png', 'gif'];
                $generate_thumbnail = true;
                $thumbnail_width = 200;
                $thumbnail_height = 200;
                $thumbnail_quality_percentage = 80;
                $image = Helper::upload_image($dir_path, $image_file, true, $format_image_name, $allowed_extensions, $generate_thumbnail, $thumbnail_width, $thumbnail_height, $thumbnail_quality_percentage);
                if ($image['status'] != 'true') {
                    # FAILED TO UPLOAD IMAGE
                    return back()
                        ->withInput()
                        ->with('error', lang($image['message'], $this->translations, $image['dynamic_objects']));
                }

                $data->image_mobile = $dir_path . $image['data'];
                $data->image_mobile_thumb = $dir_path . $image['thumbnail'];
            }

            // HELPER VALIDATION FOR PREVENT SQL INJECTION & XSS ATTACK
            $name = Helper::validate_input_text($request->name);
            if (!$name) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => $names['name']]));
            }
            $data->name = $name;

            $link_type = Helper::validate_input_text($request->link_type);
            if (!in_array($link_type, ['none', 'internal', 'external'])) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid value for #item', $this->translations, ['#item' => $names['link_type']]));
            }
            $data->link_type = $link_type;

            if ($link_type == 'external') {
                $link_url = Helper::validate_input_url($request->link_external);
                if (!$link_url) {
                    return back()
                        ->withInput()
                        ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => 'External Link']));
                }
                $data->link_external = $link_url;
            } elseif ($link_type == 'internal') {
                $link_url = Helper::validate_input_text($request->link_internal);
                if (!$link_url) {
                    return back()
                        ->withInput()
                        ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => 'Internal Link']));
                }
                $data->link_internal = $link_url;
            }

            if ($link_type != 'none') {
                $link_target = Helper::validate_input_text($request->link_target);
                if (!in_array($link_target, ['same window', 'new window'])) {
                    return back()
                        ->withInput()
                        ->with('error', lang('Invalid value for #item', $this->translations, ['#item' => 'Link Target']));
                }
                $data->link_target = $link_target;
            }

            $data->status = (int) $request->status;

            // SET ORDER / ORDINAL
            $last = banner::select('ordinal')->where('position', $position)->orderBy('ordinal', 'desc')->first();
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
        $param_url = [$position];
        $redirect_url = 'admin.banner';
        if ($request->stay_on_page) {
            $redirect_url = 'admin.banner.create';
        }

        # SUCCESS
        return redirect()
            ->route($redirect_url, $param_url)
            ->with('success', lang('Successfully added a new #item : #name', $this->translations, ['#item' => $this->item, '#name' => $name]));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  id   $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function edit($position, $id, Request $request)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'View Details');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        if (!in_array($position, $this->positions)) {
            return redirect()
                ->route('admin.home')
                ->with('error', lang('Invalid position for #item', $this->translations, ['#item' => $this->item]));
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
                ->route('admin.banner')
                ->with('error', lang('Invalid #item ID, please check your link again', $this->translations, ['#item' => $this->item]));
        }

        // GET DATA BY ID
        $data = banner::find($id);

        // CHECK IS DATA FOUND
        if (!$data) {
            # FAILED - DATA NOT FOUND
            return redirect()
                ->route('admin.banner')
                ->with('error', lang('#item not found, please check your link again', $this->translations, ['#item' => $this->item]));
        }

        return view('admin.banner.form', compact('data', 'raw_id', 'position'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  id   $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update($position, $id, Request $request)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'Edit');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        if (!in_array($position, $this->positions)) {
            return back()
                ->withInput()
                ->with('error', lang('Invalid position for #item', $this->translations, ['#item' => $this->item]));
        }

        // SET THIS OBJECT/ITEM NAME BASED ON TRANSLATION
        $this->item = ucwords(lang($this->item, $this->translations));

        $raw_id = $id;

        if (env('CRYPTOGRAPHY_MODE', false)) {
            $id = Helper::validate_token($id);
        }

        // GET DATA BY ID
        $data = banner::find($id);

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
            'name' => 'required',
            'link_type' => 'required',
        ];
        if ($request->image) {
            $validation['image'] = 'required|image|max:2048';
        }
        $message = [
            'required' => ':attribute ' . lang('should not be empty', $this->translations),
            'image' => ':attribute ' . lang('must be an image', $this->translations),
            'max' => ':attribute ' . lang('may not be greater than #item', $this->translations, ['#item' => '2MB']),
        ];
        $names = [
            'name' => ucwords(lang('name', $this->translations)),
            'link_type' => ucwords(lang('link type', $this->translations)),
            'image' => ucwords(lang('image', $this->translations)),
        ];
        $this->validate($request, $validation, $message, $names);

        DB::beginTransaction();
        try {
            // DB PROCESS BELOW

            if ($request->file('image')) {
                // UPLOAD IMAGE
                if ($position == 'home') {
                    $dir_path = 'uploads/banner/';
                } else {
                    $dir_path = 'uploads/banner/' . $position . '/';
                }
                $image_file = $request->file('image');
                $format_image_name = Helper::unique_string();
                $allowed_extensions = ['jpeg', 'jpg', 'png', 'gif'];
                $generate_thumbnail = true;
                $thumbnail_width = 200;
                $thumbnail_height = 200;
                $thumbnail_quality_percentage = 80;
                $image = Helper::upload_image($dir_path, $image_file, true, $format_image_name, $allowed_extensions, $generate_thumbnail, $thumbnail_width, $thumbnail_height, $thumbnail_quality_percentage);
                if ($image['status'] != 'true') {
                    # FAILED TO UPLOAD IMAGE
                    return back()
                        ->withInput()
                        ->with('error', lang($image['message'], $this->translations, $image['dynamic_objects']));
                }

                $data->image = $dir_path . $image['data'];
                $data->image_thumb = $dir_path . $image['thumbnail'];
            }

            if ($request->file('image_mobile')) {
                // UPLOAD IMAGE
                if ($position == 'home') {
                    $dir_path = 'uploads/banner/';
                } else {
                    $dir_path = 'uploads/banner/' . $position . '/';
                }
                $image_file = $request->file('image_mobile');
                $format_image_name = Helper::unique_string();
                $allowed_extensions = ['jpeg', 'jpg', 'png', 'gif'];
                $generate_thumbnail = true;
                $thumbnail_width = 200;
                $thumbnail_height = 200;
                $thumbnail_quality_percentage = 80;
                $image = Helper::upload_image($dir_path, $image_file, true, $format_image_name, $allowed_extensions, $generate_thumbnail, $thumbnail_width, $thumbnail_height, $thumbnail_quality_percentage);
                if ($image['status'] != 'true') {
                    # FAILED TO UPLOAD IMAGE
                    return back()
                        ->withInput()
                        ->with('error', lang($image['message'], $this->translations, $image['dynamic_objects']));
                }

                $data->image_mobile = $dir_path . $image['data'];
                $data->image_mobile_thumb = $dir_path . $image['thumbnail'];
            }

            // HELPER VALIDATION FOR PREVENT SQL INJECTION & XSS ATTACK
            $name = Helper::validate_input_text($request->name);
            if (!$name) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => $names['name']]));
            }
            $data->name = $name;

            $link_type = Helper::validate_input_text($request->link_type);
            if (!in_array($link_type, ['none', 'internal', 'external'])) {
                return back()
                    ->withInput()
                    ->with('error', lang('Invalid value for #item', $this->translations, ['#item' => $names['link_type']]));
            }
            $data->link_type = $link_type;

            if ($link_type == 'external') {
                $link_url = Helper::validate_input_url($request->link_external);
                if (!$link_url) {
                    return back()
                        ->withInput()
                        ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => 'External Link']));
                }
                $data->link_external = $link_url;
            } elseif ($link_type == 'internal') {
                $link_url = Helper::validate_input_text($request->link_internal);
                if (!$link_url) {
                    return back()
                        ->withInput()
                        ->with('error', lang('Invalid format for #item', $this->translations, ['#item' => 'Internal Link']));
                }
                $data->link_internal = $link_url;
            }

            if ($link_type != 'none') {
                $link_target = Helper::validate_input_text($request->link_target);
                if (!in_array($link_target, ['same window', 'new window'])) {
                    return back()
                        ->withInput()
                        ->with('error', lang('Invalid value for #item', $this->translations, ['#item' => 'Link Target']));
                }
                $data->link_target = $link_target;
            }

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
                ->route('admin.banner.edit', [$position, $raw_id])
                ->with('success', $success_message);
        } else {
            return redirect()
                ->route('admin.banner', $position)
                ->with('success', $success_message);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function delete($position, Request $request)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'Delete');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        if (!in_array($position, $this->positions)) {
            return back()
                ->with('error', lang('Invalid position for #item', $this->translations, ['#item' => $this->item]));
        }

        // SET THIS OBJECT/ITEM NAME BASED ON TRANSLATION
        $this->item = ucwords(lang($this->item, $this->translations));

        $id = $request->id;
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $id = Helper::validate_token($id);
        }

        // GET DATA BY ID
        $data = banner::find($id);

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

            $redirect_url = 'admin.banner';
            $param_url = [$position];

            # SUCCESS
            return redirect()
                ->route($redirect_url, $param_url)
                ->with('success', lang('Successfully deleted #item', $this->translations, ['#item' => $this->item]) . ': ' . $data->name);
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
    public function deleted_data($position)
    {
        if (!in_array($position, $this->positions)) {
            return redirect()
                ->route('admin.home')
                ->with('error', lang('Invalid position for #item', $this->translations, ['#item' => $this->item]));
        }

        $deleted_data = true;

        return view('admin.banner.list', compact('deleted_data', 'position'));
    }

    /**
     * Get a listing of the deleted resource using DataTables.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_deleted_data($position, Datatables $datatables, Request $request)
    {
        $query = banner::onlyTrashed()->where('position', $position);

        return $datatables->eloquent($query)
            ->addColumn('action', function ($data) {
                $object_id = $data->id;
                if (env('CRYPTOGRAPHY_MODE', false)) {
                    $object_id = Helper::generate_token($data->id);
                }

                $wording_restore = ucwords(lang('restore', $this->translations));
                return '<form action="' . route('admin.banner.restore', $data->position) . '" method="POST" onsubmit="return confirm(\'' . lang('Are you sure to restore this #item?', $this->translations, ['#item' => $this->item]) . '\');" style="display: inline"> ' . csrf_field() . ' <input type="hidden" name="id" value="' . $object_id . '">
                <button type="submit" class="btn btn-xs btn-success" title="' . $wording_restore . '"><i class="fa fa-check"></i>&nbsp; ' . $wording_restore . '</button></form>';
            })
            ->addColumn('link_url', function ($data) {
                switch ($data->link_type) {
                    case 'internal':
                        return $data->link_internal;
                        break;

                    case 'external':
                        return $data->link_external;
                        break;

                    default:
                        return '-';
                        break;
                }
            })
            ->addColumn('image_show', function ($data) {
                return '<a href="' . asset($data->image) . '" target="_blank"><img src="' . asset($data->image_thumb) . '"></a>';
            })
            ->addColumn('image_mobile_show', function ($data) {
                return '<a href="' . asset($data->image_mobile) . '" target="_blank"><img src="' . asset($data->image_mobile_thumb) . '"></a>';
            })
            ->editColumn('deleted_at', function ($data) {
                return Helper::time_ago(strtotime($data->deleted_at), lang('ago', $this->translations), Helper::get_periods($this->translations));
            })
            ->editColumn('created_at', function ($data) {
                return Helper::locale_timestamp($data->created_at);
            })
            ->rawColumns(['action', 'link_url', 'image_show'])
            ->toJson();
    }

    /**
     * Restore the specified deleted resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function restore($position, Request $request)
    {
        // AUTHORIZING...
        $authorize = Helper::authorizing($this->module, 'Restore');
        if ($authorize['status'] != 'true') {
            return back()->with('error', $authorize['message']);
        }

        if (!in_array($position, $this->positions)) {
            return back()
                ->with('error', lang('Invalid position for #item', $this->translations, ['#item' => $this->item]));
        }

        // SET THIS OBJECT/ITEM NAME BASED ON TRANSLATION
        $this->item = ucwords(lang($this->item, $this->translations));

        $id = $request->id;
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $id = Helper::validate_token($id);
        }

        // GET DATA BY ID
        $data = banner::onlyTrashed()->find($id);

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
                ->route('admin.banner.deleted_data', $position)
                ->with('success', lang('Successfully restored #item', $this->translations, ['#item' => $this->item]) . ': ' . $data->name);
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
    public function sorting($position, Request $request)
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

        if (!in_array($position, $this->positions)) {
            $response = [
                'status' => 'false',
                'message' => lang('Invalid position for #item', $this->translations, ['#item' => $this->item]),
                'data' => ''
            ];
            return response()->json($response, 200);
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

                $object = banner::find($tmp[1]);
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
