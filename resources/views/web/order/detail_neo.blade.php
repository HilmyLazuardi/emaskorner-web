@extends('_template_web.master')

@section('title', 'Detail Pesanan')

@section('css-plugins')
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/jquery-ui.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/jquery-ui.theme.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/select2.css') }}">
@endsection

@section('script-plugins')
    <script type="text/javascript" src="{{ asset('web/js/jquery-ui.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('web/js/select2.full.min.js') }}"></script>
@endsection

@section('content')
    @include('_template_web.header_with_categories')
    @include('_template_web.alert_popup')

    <section class="white_bg no_categories">
        <div class="section_order_list">
            <div class="container">
                <div class="sol_box">
                    <span class="flags {{ $data_order['status_label'] }}">{{ $data_order['status'] }}</span>
                    <div class="row_clear custom_one">
                        <span class="label">No. Tagihan</span>
                        {{ $data_order['transaction_id'] }}
                    </div>
                    
                    <div class="row_clear custom_one">
                        <span class="label">Alamat Pengiriman</span>
                        @if (!empty($data_order['receiver_name']))
                            {{ $data_order['receiver_name'] }}<br>
                        @endif
                        @if (!empty($data_order['receiver_phone']))
                            {{ $data_order['receiver_phone'] }}<br>
                        @endif
                        {{ $data_order['buyer_address'] }}
                    </div>
                </div>

                <div class="row_clear">
                    <div class="co_wrapper">
                        <span class="title">{{ $data_order['seller_name'] }}</span>
                        @foreach ($data as $item)
                            <div class="co_box">
                                <div class="row_clear">
                                    @php
                                        $product_image = $item->product_image;
                                        if (!empty($item->variant_image)) {
                                            $product_image = $item->variant_image;
                                        }
                                    @endphp
                                    <div class="co_img"><a href="#"><img src="{{ asset($product_image) }}"></a></div>
                                    <div class="co_desc">
                                        <h4><a href="#">{{ $item->product_name . ' - ' . $item->variant_name }}</a></h4>
                                        @php
                                            $subtotal_per_item = $item->price * $item->qty;
                                        @endphp
                                        <span class="co_price">Rp{{ number_format($subtotal_per_item, 0, ',', '.') }}</span>
                                        <span class="co_pcs">{{ $item->qty }} pcs</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="co_subtotal">
                            <div class="row_clear">
                                <div class="text_left">Subtotal</div>
                                <div class="text_right">Rp{{ number_format($data_order['price_subtotal'], 0, ',', '.') }}</div>
                            </div>

                            <div class="row_clear">
                                <div class="text_left">
                                    <span>Asuransi Pengiriman</span>
                                </div>
                                @php
                                    $asuransi = '-';
                                    if ($data_order['price_insurance'] > 0) {
                                        $asuransi = 'Rp' . number_format($data_order['price_insurance'], 0, ',', '.');
                                    }
                                @endphp
                                <div class="text_right">{{ $asuransi }}</div>
                            </div>

                            <div class="row_clear">
                                <div class="text_left">Kurir</div>
                                <div class="text_right bold">{{ $data_order['shipper_service_type'] }}</div>
                            </div>

                            <div class="row_clear">
                                <div class="text_left">Ongkos Kirim</div>
                                <div class="text_right">Rp{{ number_format($data_order['price_shipping'], 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="row_clear custom_one">
                        <div class="detail_payment">
                            <h3>Detail Pembayaran ({{ count($data_invoice) }} Invoice)</h3>
                            <ol>
                                @foreach ($data_invoice as $key => $item)
                                    <li>
                                        {{ $key }}
                                        <div class="row_clear">
                                            <div class="text_left">{{ $item['transaction_id'] }}</div>
                                            <div class="text_right">Rp{{ number_format($item['price_total'], 0, ',', '.') }}</div>
                                        </div>
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="order_summary">
                    <h3>Rincian Pesanan</h3>
                    <div class="row_clear">
                        <div class="text_left">Subtotal</div>
                        <div class="text_right">Rp{{ number_format($data_invoice_price['price_subtotal'], 0, ',', '.') }}</div>
                    </div>

                    <div class="row_clear">
                        <div class="text_left">
                            <span>Asuransi Pengiriman</span>
                        </div>
                        @php
                            $invoice_insurance_fee = '-';
                            if (!empty($data_invoice_price['price_insurance'])) {
                                $invoice_insurance_fee = 'Rp' . number_format($data_invoice_price['price_insurance'], 0, ',', '.');
                            }
                        @endphp
                        <div class="text_right">{{ $invoice_insurance_fee }}</div>
                    </div>

                    <div class="row_clear">
                        <div class="text_left">Total Ongkos Kirim</div>
                        <div class="text_right bold">Rp{{ number_format($data_invoice_price['price_shipping'], 0, ',', '.') }}</div>
                    </div>

                    @if (!empty($data_voucher))
                        <div class="row_clear">
                            <div class="row_clear">Kode Voucher</div>
                            <div class="text_left bold">{{ $data_voucher['voucher_code'] }}</div>
                            <div class="text_right">-Rp{{ number_format($data_voucher['discount_amount'], 0, ',', '.') }}</div>
                        </div>
                    @endif

                    <div class="row_clear">
                        <div class="text_left bold">Harga Total</div>
                        <div class="text_right bold">Rp{{ number_format($data_invoice_price['price_total'], 0, ',', '.') }}</div>
                    </div>
                </div>
                
                <div class="button_wrapper">
                    <a href="{{ route('web.home') }}" class="green_btn">Belanja Lagi</a>
                </div>
            </div>
        </div>
    </section>
@endsection