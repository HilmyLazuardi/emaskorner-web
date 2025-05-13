@extends('_template_web.master')

@php
    use App\Libraries\Helper;
@endphp

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

@section('title', 'Transaksi Kamu Belum Berhasil')

@section('content')
    @include('_template_web.header')

    <section class="no_categories white_bg">
        <div class="section_profile border_bottom">
            <div class="profile_box custom_two">
                <div class="container">
                    <img class="logo_sukses" src="{{ asset('web/images/warning_big.png') }}">
                    <h3>Mohon Maaf<br>Transaksi Kamu Belum Berhasil</h3>
                    @php
                        // $persentase = ($product->qty_sold / ($product->qty_sold + $product->qty_booked + $product->qty)) * 100;
                    @endphp

                    {{-- @if (isset($invoice[0]))
                        @foreach ($invoice as $item)
                            <div class="order_mid custom_one">
                                <div class="row_clear">
                                    <div class="order_img">
                                        @php
                                            $product_image = $item->product_image;
                                            if (!empty($item->product_variant_image)) {
                                                $product_image = $item->product_variant_image;
                                            }
                                        @endphp
                                        <img src="{{ asset($product_image) }}">
                                    </div>
                                    <div class="order_info">
                                        <h4>{{ $item->product_name }}</h4>
                                        Jumlah: {{ $item->qty }}<br>
                                        Varian: {{ $item->product_variant_name }}<br>
                                        oleh: {{ $item->store_name }}
                                    </div>
                                </div>
                                {{-- <div class="pl_stock_view">
                                    <div class="sv_track"><span class="sv_bar" style="width: {{ ceil($persentase) }}%;"></span></div>
                                    <div class="sv_info">{{ ceil($persentase) }}% Dipesan</div>
                                </div> --}}
                            {{-- </div>
                        @endforeach
                    @endif --}}

                    <div class="button_wrapper custom_one">
                        <a href="{{ route('web.home') }}" class="green_btn">Belanja lagi</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('_template_web.footer')
@endsection