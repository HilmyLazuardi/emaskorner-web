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
    <style>
        .tooltip {
          position: relative;
          display: inline-block;
        }

        .tooltip .tooltiptext {
          visibility: hidden;
          width: 140px;
          background-color: #555;
          color: #fff;
          text-align: center;
          border-radius: 6px;
          padding: 5px;
          position: absolute;
          z-index: 1;
          bottom: 150%;
          left: 50%;
          margin-left: -75px;
          opacity: 0;
          transition: opacity 0.3s;
        }

        .tooltip .tooltiptext::after {
          content: "";
          position: absolute;
          top: 100%;
          left: 50%;
          margin-left: -5px;
          border-width: 5px;
          border-style: solid;
          border-color: #555 transparent transparent transparent;
        }

        .tooltip:hover .tooltiptext {
          visibility: visible;
          opacity: 1;
        }
    </style>
@endsection

@section('script-plugins')
    <script type="text/javascript" src="{{ asset('web/js/select2.full.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('web/js/slick.js') }}"></script>
    <script type="text/javascript" src="{{ asset('web/js/jquery.fancybox.min.js') }}"></script>
    <script>
    function myFunction() {
      var copyText = document.getElementById("myInput");
      copyText.select();
      copyText.setSelectionRange(0, 99999);
      navigator.clipboard.writeText(copyText.value);
      
      var tooltip = document.getElementById("myTooltip");
      tooltip.innerHTML = "Berhasil";
    }

    function outFunc() {
      var tooltip = document.getElementById("myTooltip");
      tooltip.innerHTML = "Copy Link";
    }
    </script>
@endsection

@section('title', 'Transaksi Kamu Berhasil')

@section('content')
    @include('_template_web.header')

    <section class="no_categories white_bg">
        <div class="section_profile border_bottom">
            <div class="profile_box custom_two">
                <div class="container">
                    <img class="logo_sukses" src="{{ asset('web/images/checklist_big.png') }}">
                    <h3>Pembayaran Sukses!</h3>
                    @php
                        // $persentase = (($product->qty_sold + $product->qty_booked) / ($product->qty_sold + $product->qty_booked + $product->qty)) * 100;
                    @endphp

                    @if (isset($invoice[0]))
                    <div class="order_mid custom_one">
                        {{-- @if (!is_null($order->estimate_arrived_at))
                            <p style="margin-bottom: 20px;">Perkiraan waktu produk tiba di tujuan: maks. <strong>{{ Helper::convert_date_to_indonesian($order->estimate_arrived_at) }}</strong></p>
                        @endif --}}

                        {{-- @foreach ($invoice as $item)
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
                            </div>
                        @endforeach --}}

                        <div class="order_mid custom_one">
                            <div class="row_clear">
                                <div class="order_img">
                                    @php
                                        $product_image = $invoice[0]->product_image;
                                        if (!empty($invoice[0]->product_variant_image)) {
                                            $product_image = $invoice[0]->product_variant_image;
                                        }
                                    @endphp
                                    <img src="{{ asset($product_image) }}">
                                </div>
                                <div class="order_info">
                                    <h4>{{ $invoice[0]->product_name }}</h4>
                                    Jumlah: {{ $invoice[0]->qty }}<br>
                                    Varian: {{ $invoice[0]->product_variant_name }}<br>
                                    oleh: {{ $invoice[0]->store_name }}
                                </div>
                            </div>
                            @php
                                $count_item = count($invoice);
                                $count_other = $count_item - 1;
                            @endphp
                            @if ($count_other > 0)
                                <span class="other_product">+{{ $count_other }} produk lainnya</span>
                            @endif
                        </div>
                        

                        {{-- <div class="pl_stock_view">
                            <div class="sv_track"><span class="sv_bar" style="width: {{ ceil($persentase) }}%;"></span></div>
                            <div class="sv_info">{{ ceil($persentase) }}% Dipesan</div>
                        </div> --}}
                    </div>
                    @endif

                    <div class="button_wrapper custom_one">
                        <a href="{{ route('web.home') }}" class="green_btn">Belanja lagi</a>
                        <a href="{{ route('web.order.history') }}" class="red_btn">Riwayat Pesanan</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- <div class="share_wrapper">
            <div class="container">
                <h3>Bantu produk ini lebih dikenal:</h3>
                <div class="dummy"><a href="https://facebook.com/sharer.php?u={{ route('web.product.detail', $product->product_slug) }}" target="_blank"><img style="width: 25px;display: block;" src="{{ asset('images/ic_facebook.png') }}"></a></div>
                <div class="dummy"><a href="https://twitter.com/share?text={{ $product->product_name }}&url={{ route('web.product.detail', $product->product_slug) }}" target="_blank"><img style="width: 40px;display: block;" src="{{ asset('images/ic_twitter.png') }}"></a></div>
                <div class="dummy"><a data-action="share/whatsapp/share" href="https://wa.me/?text={{ route('web.product.detail', $product->product_slug) }}" target="_blank"><img style="width: 30px;display: block;" src="{{ asset('images/ic_wa.png') }}"></a></div>
                <div class="dummy">
                    <div class="tooltip">
                        <button onclick="myFunction()" onmouseout="outFunc()" style="border:none;background: none;"><img style="width: 50px;display: block;" src="{{ asset('images/ic_copy.png') }}"></a>
                        <span class="tooltiptext" id="myTooltip">Copy Link</span>
                      </button>
                    </div>
                    <input type="text" value="{{ route('web.product.detail', $product->product_slug) }}" style="position: absolute;opacity: 0;z-index: -1;height: 20px;width: 20px;" id="myInput">
                </div>
            </div>
        </div> --}}

        <div class="section_product_list green_bg custom_one">
            <div class="row_clear container">
                <p style="text-align: center">Terima kasih sudah mendukung kemajuan UMKM Indonesia.<br>Cek juga produk berkualitas lainnya di LokalKorner, ya!</p>
                <h2>Yang Mungkin Kamu Suka</h2>
            </div>
            @if (isset($products[0]))
                <div class="row_clear container slider_rp">
                    @foreach ($products as $item)
                        @php
                            // $persentase_product = 0;
                            // $persentase_product = (($item->qty_sold + $item->qty_booked) / ($item->qty_sold + $item->qty_booked + $item->qty)) * 100;
                        @endphp
                        <div class="product_list_box">
                            <div class="product_list">
                                <div class="pl_img">
                                    <a href="{{ route('web.product.detail', $item->slug) }}">
                                        @php
                                            $product_image = $item->product_image;
                                            if (!empty($item->product_variant_image)) {
                                                $product_image = $item->product_variant_image;
                                            }
                                        @endphp
                                        <img src="{{ asset($product_image) }}">
                                    </a>
                                </div>
                                <div class="pl_info">
                                    <div class="pl_name"><h4><a href="{{ route('web.product.detail', $item->slug) }}">{{ $item->product_name }}</a></h4></div>
                                    <div class="pl_owner">by: <a href="{{ route('web.product.detail', $item->slug) }}">{{ $item->store_name }}</a></div>
                                    <div class="pl_price">Rp{{ number_format($item->price, 0, ',', '.') }}</div>
                                    {{-- <div class="pl_stock_view">
                                        <div class="sv_track"><span class="sv_bar" style="width: {{ ceil($persentase_product) }}%;"></span></div>
                                        <div class="sv_info">{{ ceil($persentase_product) }}% Terjual</div>
                                    </div> --}}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    @include('_template_web.footer')
@endsection