@php
    // Libraries
    use App\Libraries\Helper;
@endphp

<table>
    <thead>
    <tr>
        <th>Tanggal Pemesanan</th>
        <th>No Transaksi</th>
        <th>Nama Pemilik</th>
        <th>Nama Toko</th>
        <th>Telp Toko</th>
        <th>Nama Pembeli</th>
        <th>Telp Pembeli</th>
        <th>Nama Produk</th>
        <th>SKU</th>
        <th>Qty</th>
        <th>Harga Satuan</th>
        <th>Subtotal</th>
        <th>Biaya Pengiriman</th>
        <th>Asuransi Pengiriman</th>
        <th>Admin Fee</th>
        <th>Total</th>
        <th>Total Diterima Seller</th>
        <th>Status</th>
    </tr>
    </thead>
    <tbody>
        @foreach ($data as $key => $item)
            @php
                $asuransi = '(Tidak Pakai Asuransi)';
                if ($item->insurance_shipping_fee) {
                    $asuransi = 'Rp' . number_format($item->insurance_shipping_fee, 0, ',', '.');
                }

                $status_label = 'UNKNOWN';
                switch ($item->progress_status) {
                    case '1':
                        $status_label = 'Menunggu Pembayaran';
                        break;

                    case '2':
                        $status_label = 'Siap Dikirim (Terbayar)';
                        break;

                    case '3':
                        $status_label = 'Sudah Dikirim';
                        break;

                    case '4':
                        $status_label = 'BATAL';
                        break;
                }

                $seller_phone = '-';
                if (!empty($item->seller_phone)) {
                    $seller_phone = '0'.$item->seller_phone;
                }
            @endphp
            <tr>
                <td>{!! Helper::locale_timestamp($item->created_at, 'd M Y H:i', false) !!}</td>
                <td>{!! $item->transaction_id !!}</td>
                <td>{!! $item->seller_name !!}</td>
                <td>{!! $item->store_name !!}</td>
                <td>{!! $seller_phone !!}</td>
                <td>{!! $item->buyer_name !!}</td>
                <td>{!! $item->phone_number !!}</td>
                <td>{!! $item->product_name !!}</td>
                <td>{!! $item->sku_id !!}</td>
                <td>{!! $item->qty !!}</td>
                <td>{!! 'Rp' . number_format($item->price_per_item, 0, ',', '.') !!}</td>
                <td>{!! 'Rp' . number_format($item->price_subtotal, 0, ',', '.') !!}</td>
                <td>{!! 'Rp' . number_format($item->price_shipping, 0, ',', '.') !!}</td>
                <td>{!! $asuransi !!}</td>
                <td>{!! 'Rp' . number_format($item->amount_fee, 0, ',', '.') !!}</td>
                <td>{!! 'Rp' . number_format($item->price_total, 0, ',', '.') !!}</td>
                <td>{!! 'Rp' . number_format(($item->price_total - $item->amount_fee), 0, ',', '.') !!}</td>
                <td>{!! $status_label !!}</td>
            </tr>
        @endforeach
    </tbody>
</table>