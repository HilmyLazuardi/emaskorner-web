<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Support\Facades\Session;

// LIBRARY
use App\Libraries\Helper;

// MODELS
use App\Models\invoice;

class InvoiceExportView implements FromView
{
    use Exportable;
    protected $status;
    protected $start_date;
    protected $end_date;

    public function __construct($status, $start_date, $end_date)
    {
        $this->status = $status;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->data = $this->get_data();
    }

    private function get_data()
    {
        $query = invoice::select(
            'invoices.*',
            'buyer.fullname AS buyer_name'
        )
            ->leftJoin('buyer', 'invoices.buyer_id', 'buyer.id')
            ->orderBy('invoices.updated_at', 'desc');

        if (!empty($this->status)) {
            $status = Helper::validate_input_text($this->status);
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

        if (!empty($this->start_date) && !empty($this->end_date)) {
            $query->whereRaw("DATE(invoices.created_at) BETWEEN ? AND ?", [$this->start_date, $this->end_date]);
        }

        $data = $query->get();
        return $data;
    }

    public function view(): View
    {
        return view('admin.invoice.export_excel', [
            'data' => $this->data
        ]);
    }
}
