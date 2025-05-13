<?php

namespace App\Http\Controllers\Admin\Core;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Yajra\Datatables\Datatables;

// Libraries
use App\Libraries\Helper;

// Models
use App\Models\admin_notification;

class NotificationController extends Controller
{
    public function get_notif()
    {
        $admin_id = Session::get(env('SESSION_ADMIN_NAME', 'sysadmin'))->id;

        $query = admin_notification::where('admin_id', $admin_id)
            ->where('read_status', 0)
            ->orderBy('created_at', 'desc');

        // get total data
        $total = $query->count();

        // get data
        $data = $query->limit(4)->get();

        if ($total > 0) {
            foreach ($data as $item) {
                $item->time_ago = Helper::time_ago(strtotime($item->created_at), lang('ago', $this->translations), Helper::get_periods($this->translations));
                $item->summary = Helper::read_more($item->content, 150);
                $url = 'javascript:;';
                if ($item->clickable) {
                    $url = route('admin.notif.open', Helper::generate_token($item->id));
                }
                $item->url = $url;
            }
        }

        $response = [
            'status' => 'true',
            'message' => 'Successfully get notifications',
            'total' => $total,
            'data' => $data
        ];
        return response()->json($response, 200);
    }

    public function open($id)
    {
        $raw_id = (int) Helper::validate_token($id);
        $admin_id = Session::get(env('SESSION_ADMIN_NAME', 'sysadmin'))->id;

        DB::beginTransaction();
        try {
            $data = admin_notification::where('id', $raw_id)->where('admin_id', $admin_id)->first();

            if ($data) {
                // mark notif as read
                $data->read_status = 1;
                $data->save();

                DB::commit();

                switch ($data->module_id) {
                    case 13:
                        // CREDIT CARD BILL
                        $url = route('admin.credit_card_bill.edit', Helper::generate_token($data->target_id)) . '?src=notif_list';
                        break;

                    default:
                        $url = route('admin.home');
                        break;
                }

                return redirect($url);
            } else {
                dd($raw_id, $data);
            }
        } catch (\Exception $ex) {
            DB::rollback();

            $error_msg = $ex->getMessage() . ' in ' . $ex->getFile() . ' at line ' . $ex->getLine();
            Helper::error_logging($error_msg, null, null, 'Open Notification');

            if (env('APP_DEBUG') == false) {
                $error_msg = lang('Oops, something went wrong please try again later.', $this->translations);
            }

            # ERROR
            return redirect()
                ->route('admin.home')
                ->with('error', $error_msg);
        }
    }

    public function index()
    {
        return view('admin.core.notif.list');
    }

    public function get_data(Datatables $datatables, Request $request)
    {
        $admin_id = Session::get(env('SESSION_ADMIN_NAME', 'sysadmin'))->id;
        $query = admin_notification::where('admin_id', $admin_id);

        return $datatables->eloquent($query)
            ->addColumn('action', function ($data) {
                $html = '';
                if ($data->clickable) {
                    $wording_edit = ucwords(lang('view', $this->translations));
                    $html = '<a href="' . route('admin.notif.open', Helper::generate_token($data->id)) . '" class="btn btn-xs btn-primary" title="' . $wording_edit . '"><i class="fa fa-eye"></i>&nbsp; ' . $wording_edit . '</a>';
                }
                return $html;
            })
            ->editColumn('created_at', function ($data) {
                $ago = Helper::time_ago(strtotime($data->created_at), lang('ago', $this->translations), Helper::get_periods($this->translations));
                return Helper::locale_timestamp($data->created_at) . ' - ' . $ago;
            })
            ->rawColumns(['action'])
            ->toJson();
    }
}
