<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Support\Facades\Session;

// MODELS
use App\Models\order;

class OrderExportView implements FromView
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
        $query = order::select(
            'order.*',
            'order_details.qty',
            'order_details.price_per_item',
            'buyer.fullname AS buyer_name',
            'buyer.phone_number',
            'product_item.name AS product_name',
            'product_item_variant.sku_id',
            'seller.store_name',
            'seller.fullname AS seller_name',
            'seller.phone_number AS seller_phone'
        )
            ->leftJoin('buyer', 'order.buyer_id', 'buyer.id')
            ->leftJoin('order_details', 'order.id', 'order_details.order_id')
            ->leftJoin('product_item_variant', 'order_details.product_id', 'product_item_variant.id')
            ->leftJoin('product_item', 'product_item_variant.product_item_id', 'product_item.id')
            ->leftJoin('seller', 'order.seller_id', 'seller.id')
            ->orderBy('order.created_at', 'desc');

        if (!empty($this->status)) {
            $query->where('order.progress_status', $this->status);
        }

        if (!empty($this->start_date) && !empty($this->end_date)) {
            $query->whereRaw("DATE(order.created_at) BETWEEN ? AND ?", [$this->start_date, $this->end_date]);
        }

        $data = $query->get();
        return $data;
    }

    public function view(): View
    {
        return view('admin.order.export_excel', [
            'data' => $this->data
        ]);
    }
}
