@extends('_template_web.master')

@section('css-plugins')
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/slick.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/slick-theme.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/jquery.fancybox.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/content_element.css') }}">
@endsection

@section('script-plugins')
    <script type="text/javascript" src="{{ asset('web/js/select2.full.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('web/js/slick.js') }}"></script>
    <script type="text/javascript" src="{{ asset('web/js/jquery.fancybox.min.js') }}"></script>
@endsection

@section('content')
    @include('_template_web.header')

    <section class="white_bg no_categories">
        <div class="section_profile">
            <div class="profile_box">
                <form action="{{ route('web.order.create') }}" method="POST">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $confirm->variant_id }}">
                    <input type="hidden" name="address" value="{{ $confirm->address_id }}">
                    <input type="hidden" name="shipper" value="{{ $confirm->shipper_id }}">
                    <input type="hidden" name="delivery_method" value="{{ $confirm->delivery_method }}">
                    <input type="hidden" name="price_shipping" value="{{ $confirm->price_shipping }}">
                    <input type="hidden" name="insurance_shipping_fee" value="{{ $confirm->insurance_shipping_fee }}">
                    <input type="hidden" name="total_price" value="{{ $confirm->total_price }}">
                    <input type="hidden" name="shipper_service_type" value="{{ $confirm->shipper_service_type }}">
                    <input type="hidden" name="origin_code" value="{{ $confirm->origin_code }}">
                    <input type="hidden" name="destination_code" value="{{ $confirm->destination_code }}">
                    <input type="hidden" name="estimate" value="{{ $confirm->estimate }}">

                    <div class="container">
                        <img class="logo" src="{{ asset('images/icon_square.png') }}">
                        <h3>Pengiriman</h3>
                        <div class="address_box custom_one">
                            <span>Label Alamat</span>
                            {{ $pengiriman->name }}
                        </div>
                        <div class="address_box custom_one">
                            <span>Penerima</span>
                            {{ $pengiriman->fullname }} | {{ $pengiriman->phone_number }}
                        </div>
                        <div class="address_box custom_one">
                            <span>Alamat Pengiriman</span>
                            {{ $pengiriman->address_details }}<br>
                            {{ $pengiriman->village_name . ', ' . $pengiriman->sub_district_name . ', ' . $pengiriman->city_name }}<br>
                            {{ $pengiriman->province_name }}
                            @if (isset($pengiriman->postal_code))
                            - {{ $pengiriman->postal_code }}
                            @endif
                            @if ($pengiriman->remarks)
                                <br> {{ $pengiriman->remarks }}
                            @endif
                        </div>
                        <div class="address_box custom_one">
                            <span>Kurir</span>
                            {{ $confirm->shipper_name }}
                        </div>
                        <div class="address_box custom_one">
                            <span>Pengiriman</span>
                            {{ $confirm->shipper_service_type }}
                        </div>
                        <div class="preview_product">
                            <div class="row_clear">
                                <table>
                                    <tr>
                                        <td>Produk</td>
                                        <td>
                                            {{ $confirm->product_name }}
                                            <span>Jumlah : {{ $confirm->qty }} Pcs</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Subtotal</td>
                                        <td>Rp{{ number_format($confirm->sub_total_price, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <td>Ongkos Kirim</td>
                                        <td>Rp{{ number_format($confirm->price_shipping, 0, ',', '.') }}</td>
                                    </tr>
                                    @if($confirm->insurance_shipping_fee != 0)
                                        <tr>
                                            <td>Asuransi</td>
                                            <td>Rp{{ number_format($confirm->insurance_shipping_fee, 0, ',', '.') }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td><strong>Harga Total</strong></td>
                                        <td><strong>Rp{{ number_format($confirm->total_price, 0, ',', '.') }}</strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="button_wrapper">
                            <button type="submit" class="red_btn">Bayar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection