@extends('_template_web.master')

@section('title', 'Detail Invoice')

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
                    <span class="flags {{ $data_invoice['status_label'] }}">{{ $data_invoice['status'] }}</span>
                    <div class="row_clear custom_one">
                        <span class="label">No. Tagihan</span>
                        {{ $data_invoice['invoice_no'] }}
                    </div>
                    <div class="row_clear custom_one">
                        <span class="label">Alamat Pengiriman</span>
                        @if (!empty($data_invoice['receiver_name']))
                            {{ $data_invoice['receiver_name'] }}<br>
                        @endif
                        @if (!empty($data_invoice['receiver_phone']))
                            {{ $data_invoice['receiver_phone'] }}<br>
                        @endif
                        {{ $data_invoice['buyer_address'] }}
                    </div>
                </div>

                @foreach ($data_item as $key => $item_seller)
                    <div class="row_clear">
                        <div class="co_wrapper">
                            <span class="title">{{ $key }}</span>
                            @foreach ($item_seller as $item)
                                <div class="co_box">
                                    <div class="row_clear">
                                        <div class="co_img"><a href="#"><img src="{{ asset($item->product_image) }}"></a></div>
                                        <div class="co_desc">
                                            <h4><a href="#">{{ $item->product_name }}</a></h4>
                                            <span class="co_price">Rp{{ number_format($item->price_total, 0, ',', '.') }}</span>
                                            <span class="co_pcs">{{ $item->qty }} pcs</span>
                                        </div>
                                    </div>
                                    @if (!empty($item->remarks))
                                        <div class="row_clear notes">
                                            <div class="notes_field">
                                                <span>{{ $item->remarks }}</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach

                            <div class="co_subtotal">
                                <div class="row_clear">
                                    <div class="text_left">Subtotal</div>
                                    <div class="text_right">Rp{{ number_format($data_price[$key]['price_subtotal'], 0, ',', '.') }}</div>
                                </div>
                                <div class="row_clear">
                                    <div class="text_left">
                                        <span>Asuransi Pengiriman</span>
                                    </div>
                                    @php
                                        $insurance_fee = '-';
                                        if (!empty($data_price[$key]['insurance_shipping_fee'])) {
                                            $insurance_fee = 'Rp' . number_format($data_price[$key]['insurance_shipping_fee'], 0, ',', '.');
                                        }
                                    @endphp
                                    <div class="text_right">{{ $insurance_fee }}</div>
                                </div>
                                <div class="row_clear">
                                    <div class="text_left">Kurir</div>
                                    <div class="text_right bold">{{ $data_price[$key]['shipper_service_type'] }}</div>
                                </div>
                                <div class="row_clear">
                                    <div class="text_left">Ongkos Kirim</div>
                                    <div class="text_right">Rp{{ number_format($data_price[$key]['price_shipping'], 0, ',', '.') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="order_summary">
                    <h3>Rincian Pesanan</h3>
                    <div class="row_clear">
                        <div class="text_left">Subtotal</div>
                        <div class="text_right">Rp{{ number_format($data_invoice['price_subtotal'], 0, ',', '.') }}</div>
                    </div>
                    <div class="row_clear">
                        <div class="text_left">
                            <span>Asuransi Pengiriman</span>
                        </div>
                        @php
                            $invoice_insurance_fee = '-';
                            if (!empty($data_invoice['price_insurance'])) {
                                $invoice_insurance_fee = 'Rp' . number_format($data_invoice['price_insurance'], 0, ',', '.');
                            }
                        @endphp
                        <div class="text_right">{{ $invoice_insurance_fee }}</div>
                    </div>
                    <div class="row_clear">
                        <div class="text_left">Total Ongkos Kirim</div>
                        <div class="text_right bold">Rp{{ number_format($data_invoice['price_shipping'], 0, ',', '.') }}</div>
                    </div>

                    @if (!empty($data_invoice['voucher_code']) || !empty($data_invoice['discount_amount']))
                        <div class="row_clear">
                            <div class="row_clear">Kode Voucher</div>
                            <div class="text_left bold">{{ $data_invoice['voucher_code'] }}</div>
                            <div class="text_right">-Rp{{ number_format($data_invoice['discount_amount'], 0, ',', '.') }}</div>
                        </div>
                    @endif

                    <div class="row_clear">
                        <div class="text_left bold">Harga Total</div>
                        <div class="text_right bold">Rp{{ number_format($data_invoice['total_amount'], 0, ',', '.') }}</div>
                    </div>
                </div>

                @if ($data_invoice['status_payment_buttons'])
                    <div class="button_wrapper">
                        <a href="{{ $data_invoice['payment_url'] }}" class="green_btn" target="_blank">Bayar</a>
                        {{-- <a href="{{ route('web.order.history') }}" class="red_btn">Batalkan</a> --}}
                    </div>
                @endif

            </div>
        </div>
    </section>
@endsection