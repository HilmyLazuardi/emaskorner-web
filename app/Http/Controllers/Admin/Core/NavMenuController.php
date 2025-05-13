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
use App\Models\nav_menu;

class NavMenuController extends Controller
{
    // SET THIS MODULE
    private $module = 'Navigation Menu';
    private $module_id = 14;

    // SET THIS OBJECT/ITEM NAME
    private $item = 'navigation menu';

    private $positions = ['top', 'bottom', 'sidebar', 'footer'];

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

        return view('admin.core.nav_menu.list', compact('position'));
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

        // get table system name
        $table_nav_menu = (new nav_menu())->getTable();

        // GET THE DATA
        $data = nav_menu::select(
            $table_nav_menu . '.*',
            DB::raw('COUNT(sub.id) AS total_subs')
        )
            ->leftJoin($table_nav_menu . ' AS sub', $table_nav_menu . '.id', 'sub.parent_id')
            ->where($table_nav_menu . '.level', 1)
            ->where($table_nav_menu . '.position', $position)
            ->groupBy(
                $table_nav_menu . '.id',
                $table_nav_menu . '.name',
                $table_nav_menu . '.link_type',
                $table_nav_menu . '.link_external',
                $table_nav_menu . '.link_internal',
                $table_nav_menu . '.link_target',
                $table_nav_menu . '.level',
                $table_nav_menu . '.parent_id',
                $table_nav_menu . '.ordinal',
                $table_nav_menu . '.status',
                $table_nav_menu . '.created_at',
                $table_nav_menu . '.updated_at',
                $table_nav_menu . '.deleted_at'
            )
            ->orderBy($table_nav_menu . '.ordinal')
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

                $edit_button = '<a href="' . route('admin.nav_menu.edit', [$position, $object_id]) . '" class="btn btn-xs btn-primary" title="' . $wording_edit . '"><i class="fa fa-pencil"></i>&nbsp; ' . $wording_edit . '</a>';

                $delete_button = '<form action="' . route('admin.nav_menu.delete', $position) . '" method="POST" onsubmit="return confirm(\'' . lang('Are you sure to delete this #item?', $this->translations, ['#item' => $this->item]) . '\');" style="display: inline"> ' . csrf_field() . ' <input type="hidden" name="id" value="' . $object_id . '">
                <button type="submit" class="btn btn-xs btn-danger" title="' . $wording_delete . '"><i class="fa fa-trash"></i>&nbsp; ' . $wording_delete . '</button></form>';

                $total_subs = '';
                if ($item->total_subs > 0) {
                    $total_subs = ' (' . $item->total_subs . ')';
                }
                $set_button = '<a href="' . route('admin.nav_menu_child', [$position, $object_id]) . '" class="btn btn-xs btn-info" title="' . $wording_set . '"><i class="fa fa-sitemap"></i>&nbsp; ' . $wording_set . $total_subs . '</a>';

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

        return view('admin.core.nav_menu.form', compact('position'));
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
        ];
        $message = [
            'required' => ':attribute ' . lang('should not be empty', $this->translations),
        ];
        $names = [
            'name' => ucwords(lang('name', $this->translations)),
            'link_type' => ucwords(lang('link type', $this->translations)),
        ];
        $this->validate($request, $validation, $message, $names);

        DB::beginTransaction();
        try {
            // DB PROCESS BELOW

            // SAVE THE DATA
            $data = new nav_menu();
            $data->position = $position;

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
            $last = nav_menu::select('ordinal')->orderBy('ordinal', 'desc')->first();
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
        $redirect_url = 'admin.nav_menu';
        if ($request->stay_on_page) {
            $redirect_url = 'admin.nav_menu.create';
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
                ->route('admin.nav_menu')
                ->with('error', lang('Invalid #item ID, please check your link again', $this->translations, ['#item' => $this->item]));
        }

        // GET DATA BY ID
        $data = nav_menu::find($id);

        // CHECK IS DATA FOUND
        if (!$data) {
            # FAILED - DATA NOT FOUND
            return redirect()
                ->route('admin.nav_menu')
                ->with('error', lang('#item not found, please check your link again', $this->translations, ['#item' => $this->item]));
        }

        return view('admin.core.nav_menu.form', compact('data', 'raw_id', 'position'));
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
        $data = nav_menu::find($id);

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
        $message = [
            'required' => ':attribute ' . lang('should not be empty', $this->translations),
        ];
        $names = [
            'name' => ucwords(lang('name', $this->translations)),
            'link_type' => ucwords(lang('link type', $this->translations)),
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
                ->route('admin.nav_menu.edit', [$position, $raw_id])
                ->with('success', $success_message);
        } else {
            return redirect()
                ->route('admin.nav_menu', $position)
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
        $data = nav_menu::find($id);

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

            $redirect_url = 'admin.nav_menu';
            $param_url = [$position];
            if ($data->level > 1) {
                $redirect_url = 'admin.nav_menu_child';
                $parent_id = $data->parent_id;
                if (env('CRYPTOGRAPHY_MODE', false)) {
                    $parent_id = Helper::generate_token($parent_id);
                }
                $param_url[] = $parent_id;
            }

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

        return view('admin.core.nav_menu.list', compact('deleted_data', 'position'));
    }

    /**
     * Get a listing of the deleted resource using DataTables.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_deleted_data($position, Datatables $datatables, Request $request)
    {
        $query = nav_menu::onlyTrashed()->where('position', $position);

        return $datatables->eloquent($query)
            ->addColumn('action', function ($data) {
                $object_id = $data->id;
                if (env('CRYPTOGRAPHY_MODE', false)) {
                    $object_id = Helper::generate_token($data->id);
                }

                $wording_restore = ucwords(lang('restore', $this->translations));
                return '<form action="' . route('admin.nav_menu.restore', $data->position) . '" method="POST" onsubmit="return confirm(\'' . lang('Are you sure to restore this #item?', $this->translations, ['#item' => $this->item]) . '\');" style="display: inline"> ' . csrf_field() . ' <input type="hidden" name="id" value="' . $object_id . '">
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
            ->editColumn('deleted_at', function ($data) {
                return Helper::time_ago(strtotime($data->deleted_at), lang('ago', $this->translations), Helper::get_periods($this->translations));
            })
            ->editColumn('created_at', function ($data) {
                return Helper::locale_timestamp($data->created_at);
            })
            ->rawColumns(['action', 'link_url'])
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
        $data = nav_menu::onlyTrashed()->find($id);

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
                ->route('admin.nav_menu.deleted_data', $position)
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

                $object = nav_menu::find($tmp[1]);
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
