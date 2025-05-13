@extends('_template_web.master')

@section('css-plugins')
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/jquery-ui.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/jquery-ui.theme.css') }}">
@endsection

@section('script-plugins')
    <script type="text/javascript" src="{{ asset('web/js/jquery-ui.min.js') }}"></script>
@endsection

@section('content')
    @include('_template_web.header')

    <section class="white_bg no_categories">
        <div class="section_profile">
            <div class="profile_box">
                <div class="container">
                    <form action="{{ route('web.order.process') }}" method="POST">
                        @csrf
                        <img class="logo" src="{{ asset('images/icon_square.png') }}">
                        <input type="hidden" name="product_id" value="{{ $data->product_id }}">
                        <input type="hidden" name="product_slug" value="{{ $data->product_slug }}">
                        <input type="hidden" name="variant_id" value="{{ $data->variant_id }}">
                        <input type="hidden" name="variant_sku" value="{{ $data->variant_sku }}">
                        <input type="hidden" name="qty" value="{{ $data->qty }}">
                        <h3>Detail Pesanan</h3>
                        <div class="order_wrapper">
                            <div class="order_top">
                                <div class="half_box">
                                    Tanggal Pesan
                                    <span>{{ date('d-m-Y', strtotime($data->date)) }}</span>
                                </div>
                                <div class="half_box">
                                    Pesan Dari
                                    <span>{{ $data->seller }}</span>
                                </div>
                            </div>
                            <div class="order_mid">
                                <div class="row_clear">
                                    <div class="order_img">
                                        <img src="{{ asset($data->product_image) }}">
                                    </div>
                                    <div class="order_info">
                                        <h4>{{ $data->product_name }}</h4>
                                        <span>Rp{{ number_format($data->product_price, 0, ',', '.') }}</span>
                                        Jumlah: {{ $data->qty }} <br>
                                        Berat: {{ $data->total_weight }}gram <br>
                                        Varian: {{ $data->variant_name }}
                                    </div>
                                </div>
                            </div>
                            <div class="order_bottom">
                                <div class="form_box">
                                    <span class="title">Catatan</span>
                                    <div class="row_clear">
                                        <input type="text" name="catatan" placeholder="Tulis catatan untuk penjual">
                                        <button class="edit_btn"></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="warning_box">
                            <div class="text_center">
                                <img src="{{ asset('web/images/warning_grey.png') }}"><br>
                                <p><strong>Penting</strong></p>
                            </div>
                            <ul>
                                <li>Dengan klik “Lanjut”, Anda setuju dengan syarat & ketentuan yang berlaku dan bahwa pembayaran tidak bisa dibatalkan.</li>
                                <li>Produk masih berbentuk prototype. Dana Anda akan dikembalikan jika produk tidak selesai diproduksi dalam batas waktu yang ditentukan.</li>
                                <li>Cek <a href="{{ route('web.faq') }}" target="_blank">FAQ</a> untuk syarat & ketentuan, serta info lainnya.</li>
                            </ul>
                        </div>
                        <div class="button_wrapper">
                            <button type="submit" class="red_btn">Lanjut</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection