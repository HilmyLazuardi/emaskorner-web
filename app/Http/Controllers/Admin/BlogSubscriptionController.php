<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BlogSubscriptionExportView;

// LIBRARIES
use App\Libraries\Helper;

// MODELS
use App\Models\subscription;

class BlogSubscriptionController extends Controller
{
    // SET THIS MODULE
    private $module     = 'Blog Subscription';
    private $module_id  = 42;

    // SET THIS OBJECT/ITEM NAME
    private $item       = 'blog subscription';

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

        return view('admin.blog_subscription.list');
    }

    /**
     * Get a listing of the resource using DataTables.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_data(Datatables $datatables, Request $request)
    {
        $query = subscription::select('*');

        return $datatables->eloquent($query)       
            ->editColumn('updated_at', function ($data) {
                return Helper::time_ago(strtotime($data->updated_at), lang('ago', $this->translations), Helper::get_periods($this->translations));
            })
            ->editColumn('created_at', function ($data) {
                return Helper::locale_timestamp($data->created_at);
            })
            ->toJson();
    }

    public function export(Request $request)
    {
        // LOGGING
        $log_detail_id  = 11; // EXPORT DATA
        $module_id      = $this->module_id;
        $target_id      = null;
        $note           = null;
        $value_before   = null;
        $value_after    = null;
        $ip_address     = $request->ip();
        Helper::logging($log_detail_id, $module_id, $target_id, $note, $value_before, $value_after, $ip_address);

        // SET FILE NAME
        $filename = date('YmdHis') . '_' . $this->module;

        return Excel::download(new BlogSubscriptionExportView, $filename . '.xlsx');
    }
}