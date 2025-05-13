@extends('_template_web.master')

@php
    use App\Libraries\Helper;

    // SET BULAN
    $bulan = array(1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
    $bulan3char = array(1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des');
    $indonesian_day = array('Monday'  => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu',
            'Sunday'    => 'Minggu');
@endphp

@section('title', 'Histori Pesanan')

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
            <div class="profile_box custom_one">
                <div class="container">
                    <img class="logo" src="{{ asset('images/icon_square.png') }}">
                    <h3>Detail Pesanan</h3>
                </div>
                <div class="container">
                    <div class="oh_img"><img src="{{ env('APP_URL') . $data->product_image }}"></div>
                    <div class="address_box custom_two">
                        <span>Order ID</span>
                        {{ $data->transaction_id }}
                    </div>
                    <div class="address_box custom_two">
                        <span>Tanggal Pembelian</span>
                        @php
                            $create = date('Y-m-d', strtotime($data->created_at));
                            $transaction_date = explode('-', $create);
                            $tgl_indo = $transaction_date[2] . ' ' . $bulan[ (int)$transaction_date[1] ] . ' ' . $transaction_date[0];
                        @endphp
                        {{ $tgl_indo }}
                    </div>
                    <div class="address_box custom_two">
                        <span>Nama Produk</span>
                        {{ $data->product_name }}
                    </div>
                    <div class="address_box custom_two">
                        <span>Penjual</span>
                        {{ $data->seller_name }}
                    </div>
                    <div class="address_box custom_two">
                        <span>Jumlah</span>
                        {{ $data->qty }} pcs
                    </div>
                    <div class="address_box custom_two">
                        <span>Varian</span>
                        {{ $data->variant_name }}
                    </div>
                    @if (!is_null($data->estimate_arrived_at))
                        @php
                            $day            = date('l', strtotime($data->estimate_arrived_at));
                            $tanggal = $indonesian_day[$day];
                            $estimate = date('Y-m-d', strtotime($data->estimate_arrived_at));
                            $estimate_date = explode('-', $estimate);
                            $tgl_indo_estimate = $tanggal.', '. $estimate_date[2] . ' ' . $bulan3char[ (int)$estimate_date[1] ] . ' ' . $estimate_date[0];
                        @endphp
                        <div class="address_box custom_two">
                            <span>Estimasi Tiba</span>
                            {{-- Helper::convert_date_to_indonesian($data->estimate_arrived_at) --}}
                            {{ $tgl_indo_estimate }}
                        </div>
                    @endif
                    <div class="address_box custom_two">
                        <span>Catatan</span>
                        {{ $data->remarks }}
                    </div>
                    <div class="preview_product custom_one">
                        <div class="row_clear">
                            <table>
                                <tr>
                                    <td>Subtotal</td>
                                    <td>Rp {{ number_format($data->price_subtotal, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td>Ongkos kirim</td>
                                    <td>Rp {{ number_format($data->price_shipping, 0, ',', '.') }}</td>
                                </tr>
                                @if ($data->use_insurance_shipping)
                                    <tr>
                                        <td>Asuransi</td>
                                        <td>Rp {{ number_format($data->insurance_shipping_fee, 0, ',', '.') }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td><strong>Harga total</strong></td>
                                    <td><strong>Rp {{ number_format($data->price_total, 0, ',', '.') }}</strong></td>
                                </tr>
                                <tr>
                                    <td>Status</td>
                                    <td>
                                        @if ($data->progress_status == 1)
                                            <span class="yellow_c">{{ $data->status }}</span>
                                        @elseif ($data->progress_status == 2)
                                            <span class="green_c">{{ $data->status }}</span>
                                        @elseif ($data->progress_status == 3)
                                            <span class="blue_c">{{ $data->status }}</span>
                                        @else
                                            <span class="red_c">{{ $data->status }}</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="button_wrapper">
                        @if ($data->progress_status == 1)
                            <a class="red_btn" href="{{ $data->payment_url }}">Bayar</a>
                        @else
                            <a href="{{ route('web.product.detail', $data->slug) }}" class="red_btn" >Beli Lagi</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('footer-script') @endsection