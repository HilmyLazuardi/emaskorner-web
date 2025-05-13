<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;

// MODELS
use App\Models\buyer;

class BuyerExportView implements FromView
{
    use Exportable;

    public function __construct()
    {
        $this->data = $this->get_data();
    }

    private function get_data()
    {
        // GET THE DATA
        $data = buyer::whereNull('deleted_at')
            ->get();

        return $data;
    }

    public function view(): View
    {
        return view('admin.buyer.export_excel', [
            'data' => $this->data
        ]);
    }
}
