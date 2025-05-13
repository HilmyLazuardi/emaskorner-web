<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InvoiceExportView;

// Libraries
use App\Libraries\Helper;

// Models
use App\Models\invoice;
use App\Models\seller;

class InvoiceController extends Controller
{
    // SET THIS MODULE
    private $module     = 'Invoice';
    private $module_id  = 40;

    // SET THIS OBJECT/ITEM NAME
    private $item = 'invoice';

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

        return view('admin.invoice.list');
    }

    /**
     * Get a listing of the resource using DataTables.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_data(Datatables $datatables, Request $request)
    {
        $query = invoice::select(
                'invoices.*',
                'buyer.fullname AS buyer_name'
            )
            ->leftJoin('buyer', 'invoices.buyer_id', 'buyer.id')
            ->orderBy('invoices.updated_at', 'desc');

        if (!empty($request->status)) {
            $status = Helper::validate_input_text($request->status);
            switch ($status) {
                case 'paid':
                    $query->whereNotNull('invoices.paid_at');
                    $query->where('invoices.payment_status', 1);
                    $query->where('invoices.is_cancelled', 0);
                    break;
                case 'unpaid':
                    $query->whereNull('invoices.paid_at');
                    $query->where('invoices.payment_status', 0);
                    $query->where('invoices.is_cancelled', 0);
                    break;
                case 'cancelled':
                    $query->where('invoices.is_cancelled', 1);
                    break;
            }
        }

        if (!empty($request->daterange)) {
            // DATERANGE FORMATING
            $daterange = explode(' - ', $request->daterange);
            $start_date_plain = Helper::convert_datepicker($daterange[0]); // GMT+7
            $start_date = Helper::server_timestamp($start_date_plain . ' 00:00:00', 'Y-m-d H:i:s'); // UTC
            $end_date_plain = Helper::convert_datepicker($daterange[1]); // GMT+7
            $end_date = Helper::server_timestamp($end_date_plain . ' 23:59:59', 'Y-m-d H:i:s'); // UTC

            $query->whereRaw("invoices.created_at BETWEEN ? AND ?", [$start_date, $end_date]);

            if (isset($_COOKIE['devon'])) {
                return [$start_date, $end_date];
            }
        }

        return $datatables->eloquent($query)
            ->addColumn('action', function ($data) {
                $object_id = $data->id;
                if (env('CRYPTOGRAPHY_MODE', false)) {
                    $object_id = Helper::generate_token($data->id);
                }

                $wording_detail = ucwords(lang('detail', $this->translations));
                $html = '<a href="' . route('admin.invoice.detail', $object_id) . '" class="btn btn-xs btn-primary" title="' . $wording_detail . '"><i class="fa fa-eye"></i>&nbsp; ' . $wording_detail . '</a>';

                return $html;
            })
            ->addColumn('status', function ($data) {
                $label = 'UNKNOWN';

                if (!empty($data->paid_at) && $data->payment_status == 1 && $data->is_cancelled == 0) {
                    $label = 'Paid';
                }

                if (empty($data->paid_at) && $data->payment_status == 0 && $data->is_cancelled == 0) {
                    $label = 'Unpaid';
                }
                
                if ($data->is_cancelled == 1) {
                    $label = 'Cancelled';
                }

                return $label;
            })
            ->editColumn('created_at', function ($data) {
                return Helper::locale_timestamp($data->created_at, 'd M Y H:i', false);
            })
            ->editColumn('subtotal', function ($data) {
                return 'Rp' . number_format($data->subtotal, 0, ',', '.');
            })
            ->editColumn('shipping_fee', function ($data) {
                return 'Rp' . number_format($data->shipping_fee, 0, ',', '.');
            })
            ->editColumn('shipping_insurance_fee', function ($data) {
                if ($data->shipping_insurance_fee) {
                    return 'Rp' . number_format($data->shipping_insurance_fee, 0, ',', '.');
                }
                return '(Tidak Pakai Asuransi)';
            })
            ->editColumn('total_amount', function ($data) {
                return 'Rp' . number_format($data->total_amount, 0, ',', '.');
            })
            ->editColumn('discount_amount', function ($data) {
                if ($data->discount_amount) {
                    return '(' . $data->voucher_code . ') Rp' . number_format($data->discount_amount, 0, ',', '.');
                }
                return '(Tidak Pakai Voucher)';
            })
            ->rawColumns(['action', 'status'])
            ->toJson();
    }

    /**
     * Display a detail of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function detail($id)
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
                ->route('admin.invoice')
                ->with('error', lang('Invalid #item ID, please check your link again', $this->translations, ['#item' => $this->item]));
        }

        // CHECK DATA
        $data = invoice::select(
            'invoices.invoice_no'
        )
            ->join('order', 'order.invoice_id', 'invoices.id')
            ->where('invoices.id', $id)
            ->get();

        if (empty($data[0])) {
            // INVALID OBJECT ID
            return redirect()
                ->route('admin.invoice')
                ->with('error', lang('#item ID not found, please check your link again', $this->translations, ['#item' => $this->item]));
        }

        return view('admin.invoice.detail', compact('raw_id', 'data'));
    }

    /**
     * Get a detail of the resource using DataTables.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_data_detail($id, Datatables $datatables, Request $request)
    {
        $raw_id = $id;
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $id = Helper::validate_token($id);
        }

        $query = invoice::select(
                'order.*',
                'buyer.fullname AS buyer_name'
            )
            ->leftJoin('buyer', 'invoices.buyer_id', 'buyer.id')
            ->join('order', 'order.invoice_id', 'invoices.id')
            ->where('invoices.id', $id)
            ->orderBy('order.updated_at', 'desc');

        return $datatables->eloquent($query)
            ->addColumn('action', function ($data) {
                $object_id = $data->id;
                if (env('CRYPTOGRAPHY_MODE', false)) {
                    $object_id = Helper::generate_token($data->id);
                }

                $wording_detail = ucwords(lang('detail', $this->translations));
                $html = '<a href="' . route('admin.order.detail', $object_id) . '" class="btn btn-xs btn-primary" title="' . $wording_detail . '"><i class="fa fa-eye"></i>&nbsp; ' . $wording_detail . '</a>';

                return $html;
            })
            ->addColumn('status', function ($data) {
                $label = 'UNKNOWN';

                switch ($data->progress_status) {
                    case '1':
                        $label = 'Menunggu Pembayaran';
                        break;
                    
                    case '2':
                        $label = 'Siap Dikirim (Terbayar)';
                        break;

                    case '3':
                        $label = 'Sudah Dikirim';
                        break;
                    
                    case '4':
                        $label = 'BATAL';
                        break;
                    
                    case '5':
                        $label = 'Refunded';
                        break;
                }

                return $label;
            })
            ->editColumn('created_at', function ($data) {
                return Helper::locale_timestamp($data->created_at, 'd M Y H:i', false);
            })
            ->editColumn('price_subtotal', function ($data) {
                return 'Rp' . number_format($data->price_subtotal, 0, ',', '.');
            })
            ->editColumn('price_shipping', function ($data) {
                return 'Rp' . number_format($data->price_shipping, 0, ',', '.');
            })
            ->editColumn('insurance_shipping_fee', function ($data) {
                if ($data->insurance_shipping_fee) {
                    return 'Rp' . number_format($data->insurance_shipping_fee, 0, ',', '.');
                }
                return '(Tidak Pakai Asuransi)';
            })
            ->editColumn('price_total', function ($data) {
                return 'Rp' . number_format($data->price_total, 0, ',', '.');
            })
            ->rawColumns(['action', 'status'])
            ->toJson();
    }

    public function export(Request $request)
    {
        // logging
        $log_detail_id = 11; // export data
        $module_id = $this->module_id;
        $target_id = null;
        $note = null;
        $value_before = null;
        $value_after = null;
        $ip_address = $request->ip();
        Helper::logging($log_detail_id, $module_id, $target_id, $note, $value_before, $value_after, $ip_address);

        $status = '';
        if (!empty($request->status)) {
            $status = $request->status;
        }
        $start_date = '';
        $end_date = '';
        if (!empty($request->daterange)) {
            // DATERANGE FORMATING
            $daterange = explode(' - ', $request->daterange);
            $start_date_plain = explode('/', $daterange[0]);
            $end_date_plain = explode('/', $daterange[1]);
            $start_date = $start_date_plain[2].'-'.$start_date_plain[1].'-'.$start_date_plain[0];
            $end_date = $end_date_plain[2].'-'.$end_date_plain[1].'-'.$end_date_plain[0];
        }

        // SET FILE NAME
        $filename = date('YmdHis') . '_' . $this->module;

        return Excel::download(new InvoiceExportView($status, $start_date, $end_date), $filename . '.xlsx');
    }
}