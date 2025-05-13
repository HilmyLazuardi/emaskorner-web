@php
    // Libraries
    use App\Libraries\Helper;
@endphp

<table>
    <thead>
    <tr>
        <th>Tanggal Pemesanan</th>
        <th>Nama Pembeli</th>
        <th>No Invoice</th>
        <th>Subtotal</th>
        <th>Biaya Pengiriman</th>
        <th>Asuransi Pengiriman</th>
        <th>Diskon</th>
        <th>Total</th>
        <th>Status</th>
    </tr>
    </thead>
    <tbody>
        @foreach ($data as $key => $item)
            @php
                // HARGA ASURANSI
                $asuransi = '(Tidak Pakai Asuransi)';
                if ($item->shipping_insurance_fee) {
                    $asuransi = 'Rp' . number_format($item->shipping_insurance_fee, 0, ',', '.');
                }

                // TOTAL DISKON
                $discount_ammount = '(Tidak Pakai Voucher)';
                if ($item->discount_amount) {
                    $discount_ammount = '(' . $item->voucher_code . ') Rp' . number_format($item->discount_amount, 0, ',', '.');
                }

                // STATUS LABEL
                $status_label = 'UNKNOWN';

                if (!empty($item->paid_at) && $item->payment_status == 1 && $item->is_cancelled == 0) {
                    $status_label = 'Paid';
                }

                if (empty($item->paid_at) && $item->payment_status == 0 && $item->is_cancelled == 0) {
                    $status_label = 'Unpaid';
                }
                
                if ($item->is_cancelled == 1) {
                    $status_label = 'Cancelled';
                }
            @endphp
            <tr>
                <td>{!! Helper::locale_timestamp($item->created_at, 'd M Y H:i', false) !!}</td>
                <td>{!! $item->buyer_name !!}</td>
                <td>{!! $item->invoice_no !!}</td>
                <td>{!! 'Rp' . number_format($item->subtotal, 0, ',', '.') !!}</td>
                <td>{!! 'Rp' . number_format($item->shipping_fee, 0, ',', '.') !!}</td>
                <td>{!! $asuransi !!}</td>
                <td>{!! $discount_ammount !!}</td>
                <td>{!! 'Rp' . number_format($item->total_amount, 0, ',', '.') !!}</td>
                <td>{!! $status_label !!}</td>
            </tr>
        @endforeach
    </tbody>
</table>