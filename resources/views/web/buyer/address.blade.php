@extends('_template_web.master')

@section('title', 'Daftar Alamat')

@section('css-plugins')
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/jquery-ui.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/jquery-ui.theme.css') }}">
@endsection

@section('script-plugins')
    <script type="text/javascript" src="{{ asset('web/js/jquery-ui.min.js') }}"></script>
@endsection

@section('content')
    @include('_template_web.header')
    @include('_template_web.alert_popup')

    <section class="white_bg no_categories">
        <div class="section_profile">
            <div class="profile_menu">
                <img class="logo" src="{{ asset('web/images/icon_square.png') }}">
                <div class="container">
                    <ul>
                    <li><a href="{{ route('web.buyer.profile') }}">Profil</a></li>
                        <li><a href="{{ route('web.buyer.list_address') }}" class="active">Daftar Alamat</a></li>
                        <li><a href="{{ route('web.order.history') }}">Riwayat Pesanan</a></li>
                    </ul>
                </div>
            </div>
            <div class="profile_box">
                <div class="container">
                    <h3>Daftar Alamat</h3>

                    @if (isset($data[0]))
                        @foreach ($data as $item)
                            <div class="address_box">
                                <span>{{ $item->name }}</span>@if ($item->is_default) <span class="label_utama">Utama</span> @endif
                                @if (empty($item->fullname) || empty($item->phone_number))
                                    <h4 style="color: red">Harus isi Nama & Telepon penerima</h4>
                                    <b style="color: red">untuk menjadikan alamat utama</b><br>
                                @else
                                    <h4>{{ $item->fullname }}</h4>
                                    {{ $item->phone_number }}<br>
                                @endif
                               
                                {{ $item->address_details }}<br>
                                {{ $item->village_name . ', ' . $item->sub_district_name . ', ' . $item->city_name }}
                                @if (isset($item->postal_code))
                                    - {{ $item->postal_code }}
                                @endif
                                <br>
                                {{ $item->province_name }}
                                <div class="row_clear">
                                    <a href="{{ route('web.buyer.edit_address', $item->raw_id) }}" class="green_btn">Ubah</a>
                                    @if (!$item->is_default && !empty($item->fullname) && !empty($item->phone_number))
                                        <button class="set_btn" onclick="set_ad_default_address('{{ $item->raw_id }}')">Jadikan Alamat Utama</button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="address_box">
                            <div class="text_center_bold">Buat pemesanan Kamu lebih mudah dengan tambahkan alamat.</div>
                        </div>
                    @endif

                    <a href="{{ route('web.buyer.add_address') }}" class="green_btn">Tambah Alamat</a>
                </div>
            </div>
        </div>
    </section>
@endsection
@section('footer-script')
    <script>
        @if (count($errors) > 0)
            show_popup_failed();
        @endif

        @if (session('error'))
            show_popup_failed();
        @endif

        @if (session('success'))
            show_popup_success();
        @endif
        
        @if (session('success_address'))
            show_popup_success();
        @endif

        function set_ad_default_address(id) {
            var result = confirm("Apakah Anda yakin ingin menjadikan alamat ini sebagai alamat utama Anda?");
            if (result != true) {
                event.preventDefault();
                return false;
            }

            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

            $.ajax({
                type: "POST",
                url: "{{ route('web.buyer.set_default_address') }}",
                data: {
                    _token : CSRF_TOKEN,
                    target : id,
                },
                dataType: "json",
                beforeSend: function() {
                    // LOADING HERE
                }
            })
            .done(function(response) {
                if (typeof response != 'undefined') {
                    if (response.status == 'success') {
                        show_popup_alert("success", "Berhasil", response.message);
                        
                        setTimeout(() => {
                            location.reload();
                            return false;
                        }, 2300);
                    } else {
                        show_popup_alert("error", "Error!", response.message);
                        // alert('ERROR: ' + response.message);
                    }
                } else {
                    show_popup_alert("error", "Error!", 'Maaf, respon server tidak dikenali. Mohon coba lagi atau refresh halaman ini.');
                    // alert('Maaf, respon server tidak dikenali. Mohon coba lagi atau refresh halaman ini.');
                }
            })
            .fail(function() {
                show_popup_alert("error", "Error!", 'Maaf, gagal menghubungi server. Mohon coba lagi atau refresh halaman ini.');
                // alert('Maaf, gagal menghubungi server. Mohon coba lagi atau refresh halaman ini.');
            })
            .always(function() {});
        }
    </script>
@endsection