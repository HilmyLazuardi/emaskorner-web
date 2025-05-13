<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

// Libraries
use App\Libraries\Helper;

// Models
use App\Models\global_config;

class GlobalConfigController extends Controller
{
    // SET THIS MODULE
    private $module = 'Global Config';
    private $module_id = 33;
    private $item = 'Global Config';

    /**
     * Display & update application configuration.
     *
     * @return view()
     */
    public function index(Request $request)
    {
        if ($request->isMethod('post')) {
            // AUTHORIZING...
            $authorize = Helper::authorizing($this->module, 'Update');
            if ($authorize['status'] != 'true') {
                return back()->with('error', $authorize['message']);
            }

            // LARAVEL VALIDATION
            $validation = [
                'percentage_fee' => 'required|integer'
            ];

            $message = [
                'required'  => ':attribute ' . lang('should not be empty', $this->translations),
                'integer'   => ':attribute ' . lang('must be numeric', $this->translations)
            ];

            $names = [
                'percentage_fee' => ucwords(lang('Percentage Fee', $this->translations))
            ];
            $this->validate($request, $validation, $message, $names);

            DB::beginTransaction();
            try {
                // DB PROCESS BELOW

                // get existing data
                $data = global_config::where('name', 'percentage_fee')->first();

                if (empty($data)) {
                    return back()
                        ->withInput()
                        ->with('error', lang('Data not found for #item', $this->translations, ['#item' => ucwords(lang('percentage fee', $this->translations))]));
                }

                // store data before updated
                $value_before = $data->toJson();

                // HELPER VALIDATION FOR PREVENT SQL INJECTION & XSS ATTACK
                $data->value = (int) $request->percentage_fee;
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
                Helper::error_logging($error_msg, $this->module_id);

                if (env('APP_DEBUG') == false) {
                    $error_msg = lang('Oops, something went wrong please try again later.', $this->translations);
                }

                # ERROR
                return back()
                    ->withInput()
                    ->with('error', $error_msg);
            }

            # SUCCESS
            return redirect()
                ->route('admin.global.config')
                ->with('success', lang('#item has been successfully updated', $this->translations, ['#item' => ucwords(lang('global configuration', $this->translations))]));
        } else {
            // AUTHORIZING...
            $authorize = Helper::authorizing($this->module, 'View');
            if ($authorize['status'] != 'true') {
                return back()->with('error', $authorize['message']);
            }

            $data = global_config::select('global_config.value as percentage_fee')->where('name', 'percentage_fee')->first();

            return view('admin.global_config.index', compact('data'));
        }
    }
}