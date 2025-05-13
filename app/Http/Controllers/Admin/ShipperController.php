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
use App\Models\shippers;

class ShipperController extends Controller
{
    // SET THIS MODULE
    private $module     = 'Shipper';
    private $module_id  = 34;

    // SET THIS OBJECT/ITEM NAME
    private $item = 'shipper';

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

        return view('admin.shipper.list');
    }

    /**
     * Get a listing of the resource using DataTables.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_data(Datatables $datatables, Request $request)
    {
        $query = shippers::whereNotNull('id');

        return $datatables->eloquent($query)
            ->addColumn('action', function ($data) {
                $object_id = $data->id;
                if (env('CRYPTOGRAPHY_MODE', false)) {
                    $object_id = Helper::generate_token($data->id);
                }

                if ($data->status) {
                    $wording_status   = 'inactive';
                    $button_status    = 'dark';
                    $icon_status      = 'fa-star-o';
                } else {
                    $wording_status   = 'active';
                    $button_status    = 'success';
                    $icon_status      = 'fa-star';
                }

                $word_status = ucwords(lang($wording_status, $this->translations));
                $html = '<form action="' . route('admin.shipper.change_status') . '" method="POST" onsubmit="return confirm(\'' . lang('Are you sure want to set this #item to ' . $word_status . '?', $this->translations, ['#item' => $this->item]) . '\');" style="display: inline"> ' . csrf_field() . ' <input type="hidden" name="id" value="' . $object_id . '">
                <button type="submit" class="btn btn-xs btn-' . $button_status . '" title="' . $word_status . '"><i class="fa ' . $icon_status . '"></i>&nbsp; ' . $word_status . '</button></form>';

                return $html;
            })
            ->addColumn('status', function ($data) {
                if ($data->status) {
                    $html = '<span class="label label-success">' . ucwords(lang('active', $this->translations)) . '</span>';
                } else {
                    $html = '<span class="label label-danger">' . ucwords(lang('inactive', $this->translations)) . '</span>';
                }

                return $html;
            })
            ->rawColumns(['action', 'status'])
            ->toJson();
    }

    /**
     * Set flag to the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function change_status(Request $request)
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
        $data = shippers::find($id);

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
            if ($data->status == 0) {
                $data->status = 1;
            } else {
                $data->status = 0;
            }

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
        return redirect()->route('admin.shipper')->with('success', $success_message);
    }
}