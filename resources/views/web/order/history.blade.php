@extends('_template_web.master')

@section('title', 'Riwayat Pesanan')

@section('css-plugins')
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/slick.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/slick-theme.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/jquery.fancybox.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/content_element.css') }}">

    <style>
        .history_img img {
            max-height: 120px !important;
            max-width: 120px !important;
        }
    </style>
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
            <div class="profile_menu">
                <img class="logo" src="{{ asset('web/images/icon_square.png') }}">
                <div class="container">
                    <ul>
                        <li><a href="{{ route('web.buyer.profile') }}">Profil</a></li>
                        <li><a href="{{ route('web.buyer.list_address') }}">Daftar Alamat</a></li>
                        <li><a href="{{ route('web.order.history') }}" class="active">Riwayat Pesanan</a></li>
                    </ul>
                </div>
            </div>
            <div class="section_history">
                <div class="container">
                    <div class="row_clear" id="drop_data">
                        <div class="search_bar">
                            <input type="text" id="keyword" placeholder="Cari Transaksi">
                            <button class="search_btn" onclick="refresh_data()"></button>
                        </div>
                        <div class="filter_btn">
                            <select class="select3" data-placeholder="Filter" id="filter" onchange="refresh_data()">
                                <option></option>
                                <option value="all">All</option>
                                <option value="1">Menunggu pembayaran</option>
                                <option value="2">Sudah dibayar</option>
                                <option value="3">Sudah dikirim</option>
                                <option value="4">Pesanan dibatalkan</option>
                                <option value="5">Refunded</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="button_wrapper">
                        <a href="{{ route('web.home') }}" class="green_btn">Kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('footer-script')
    <script>
        $(document).ready(function() {
            refresh_data();
        });

        function refresh_data() {
            var CSRF_TOKEN  = $('meta[name="csrf-token"]').attr('content');
            var keyword     = null;
            var filter      = null;

            if ($('#keyword').val() != null) {
                keyword = $('#keyword').val();
            }

            if ($('#filter').val() != null) {
                filter = $('#filter').val();
            }

            $.ajax({
                type: "POST",
                url: "{{ route('web.order.history_data') }}?keyword=" + keyword + '&filter=' + filter,
                data: {
                    _token : '{{ csrf_token() }}',
                    buyer_id : '{{ $buyer_id }}'
                },
                dataType: "json"
            })
            .done(function(response) {
                console.log(response)
                if (typeof response != 'undefined') {
                    if(response.status == 'success'){
                        $(".history_box").remove();
                        $("#drop_data").after(response.html);
                    } else{
                        alert('ERROR: ' + response.message);
                    }
                } else {
                    alert('Maaf, respon server tidak dikenali. Mohon coba lagi atau refresh halaman ini.');
                }
            })
            .fail(function() {
                alert('Maaf, gagal menghubungi server. Mohon coba lagi atau refresh halaman ini.');
            })
            .always(function() {});
        }
    </script>
@endsection