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

    <script>
        function formatNumber(num) {
            return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.')
        }
    </script>
@endsection

@section('content')
    @include('_template_web.header')

    @if (isset($shipper_warning[0]))
        @php
            if (count($shipper_warning) == 2) {
                $wording = 'Wah, Jasa Pengiriman JNE & Anteraja Sedang Gangguan. <br>Coba jasa pengiriman lainnya atau tunggu beberapa saat lagi, ya.';
            } else {
                foreach ($shipper_warning as $warning) {
                    if ($warning->name == 'JNE') {
                        $wording = 'Wah, Jasa Pengiriman JNE Sedang Gangguan. <br>Coba jasa pengiriman lainnya atau tunggu beberapa saat lagi, ya.';
                    } else {
                        $wording = 'Wah, Jasa Pengiriman Anteraja Sedang Gangguan. <br>Coba jasa pengiriman lainnya atau tunggu beberapa saat lagi, ya.';
                    }
                }
            }
        @endphp

        <div class="popup_failed" style="display:block;">
            <div class="overlay"></div>
            <div class="popup_box">
                <div class="icon_box"><img src="{{ asset('web/images/icon_close_white.png') }}"></div>
                <h3>Error!</h3>
                <p>{!! $wording !!}</p>
                <a href="#" class="close_btn" onclick="hide_popup();">Close</a>
            </div>
        </div>
    @endif

    <section class="white_bg no_categories">
        <div class="section_profile">
            <div class="profile_box">
                <div class="container">
                    <img class="logo" src="{{ asset('images/icon_square.png') }}">
                    <h3>Kirim ke Mana?</h3>
                    <div class="form_wrapper form_bg">
                        <form action="{{ route('web.order.confirm') }}" method="POST">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $data_detail->product_id }}">
                            <input type="hidden" name="product_slug" value="{{ $data_detail->product_slug }}">
                            <input type="hidden" name="variant_id" value="{{ $data_detail->variant_id }}">
                            <input type="hidden" name="variant_sku" value="{{ $data_detail->variant_sku }}">
                            <input type="hidden" name="qty" value="{{ $data_detail->qty }}">

                            <div class="form_box">
                                <span class="title">Alamat</span>
                                <select class="select3" data-placeholder="Pilih dari Daftar Alamat Anda" name="address" id="address" required>
                                    @if (isset($buyer_address[0]))
                                        <option selected disable>Pilih dari Daftar Alamat Anda</option>
                                        @foreach ($buyer_address as $address)
                                            <option value="{{ $address->id }}" data-zipcode="{{ $address->postal_code }}">
                                                {{ 
                                                    $address->name . ' - ' .
                                                    $address->fullname . ' (' .
                                                    $address->phone_number . ') - ' .
                                                    $address->village_name . ',  ' . 
                                                    $address->sub_district_name . ', ' . 
                                                    $address->city_name . ', ' . 
                                                    $address->province_name . ', ' . 
                                                    $address->postal_code . ' - ' . 
                                                    $address->address_details
                                                }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                <a href="{{ route('web.buyer.add_address', ['from' => 'process-order']) }}" class="add_address">+ Tambah Alamat</a>
                            </div>
                            {{-- KALAU DIPILIH SELECT BOX BARU MUNCUL --}}
                            {{-- <div class="address_box no_border">
                                Jl.Jatijajar No.29, RT.02/RW.05<br>
                                PERUM III, Way Kanan<br>
                                Provinsi Lampung 
                            </div> --}}
                            <div class="form_box">
                                <span class="title">Kurir</span>
                                <select class="select3" data-placeholder="Pilih kurir di sini" name="shipper" id="shipper" required>
                                    @if (count($shippers) > 0)
                                        <option selected disabled>Pilih kurir di sini</option>
                                        @foreach ($shippers as $shipper)
                                            <option value="{{ $shipper->id }}">{{ $shipper->name}}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            
                            <div class="form_box" id="loading-pengiriman" style="display: none">
                                <span class="title">Mohon menunggu, sedang memproses...</span>
                            </div>
                            <div class="form_box" id="pilih-pengiriman">
                                <span class="title">Pengiriman</span>
                                <select class="select3" data-placeholder="Pilih pengiriman yang Anda inginkan" name="delivery_method" id="delivery_method" required>
                                    <option selected disabled>Pilih pengiriman yang Anda inginkan</option>
                                </select>
                                <div class="label_box">
                                    <label><input type="checkbox" name="use_insurance_shipping" id="use_insurance_shipping"><span>Pakai Asuransi</span></label>
                                </div>
                            </div>
                            <div class="preview_product">
                                <div class="order_mid">
                                    <div class="row_clear">
                                        <div class="order_img">
                                            <img src="{{ asset($data_detail->product_image) }}">
                                        </div>
                                        <div class="order_info">
                                            <h4>{{ $data_detail->product_name }}</h4>
                                            <span>Rp{{ number_format($data_detail->product_price, 0, ',', '.') }}</span>
                                            Jumlah: {{ $data_detail->qty }} <br>
                                            Berat: {{ $data_detail->total_weight }}gram <br>
                                            Varian: {{ $data_detail->variant_name }}
                                        </div>
                                    </div>
                                </div>
                                <div class="row_clear">
                                    <table>
                                        <tr>
                                            <td>Subtotal</td>
                                            <td>Rp{{ number_format($data_detail->total_price, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td>Ongkos kirim
                                                <input type="hidden" style="width: 160px;" name="price_shipping" id="price_shipping"> <!-- update jadi hidden lagi nanti -->
                                            </td>
                                            <td id="price_shipping_view">Rp0
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <input type="hidden" style="width: 160px;" name="service_type" id="service_type"> <!-- update jadi hidden lagi nanti -->
                                                <input type="hidden" style="width: 160px;" name="origin_code" id="origin_code"> <!-- update jadi hidden lagi nanti -->
                                                <input type="hidden" style="width: 160px;" name="destination_code" id="destination_code"> <!-- update jadi hidden lagi nanti -->
                                                <input type="hidden" style="width: 160px;" name="estimate" id="estimate"> <!-- update jadi hidden lagi nanti -->
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Asuransi
                                                <input type="hidden" style="width: 160px;" name="insurance_shipping_fee" id="insurance_shipping_fee"> <!-- update jadi hidden lagi nanti -->
                                            </td>
                                            <td id="insurance_shipping_fee_view">Rp0
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Harga total</strong>
                                                <input type="hidden" style="width: 160px;" name="total_price" id="total_price"> <!-- update jadi hidden lagi nanti -->
                                            </td>
                                            <td><strong id="total_price_view">Rp0</strong>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="button_wrapper">
                                <button type="submit" class="red_btn">Lanjut</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('footer-script')
    <script>
        function get_shipment() {
            // CHECK ADDRESS
            var check_address = $('#address').val();
            if (isNaN(check_address)) {
                alert('Harap isi alamat terlebih dahulu');
                return;
            }

            var shipper = $('#shipper').val();
            switch (shipper) {
                case "1":
                    // JNE
                    var link = "{{ route('web.order.ajax_check_shipment_jne') }}";
                    break;
                case "2":
                    // ANTERAJA
                    var link = "{{ route('web.order.ajax_check_shipment_anteraja') }}";
                    break;
            
                default:
                    alert("Kurir yang dipilih tidak valid, mohon coba lagi.");
                    return;
                    break;
            }

            var destination = $('#address').find(':selected').data('zipcode');
            var seller_id   = '{{ $data_detail->seller_id }}';
            var weight      = '{{ $data_detail->formatted_total_weight }}';

            $.ajax({
                type: "POST",
                url: link,
                data: {
                    _token: "{{ csrf_token() }}",
                    destination: destination,
                    seller_id: seller_id,
                    weight: weight
                },
                beforeSend: function () {
                    // do something before send the data
                    $('#loading-pengiriman').show();
                    $('#pilih-pengiriman').hide();
                },
            })
            .done(function (response) {
                // Callback handler that will be called on success
                if (typeof response != 'undefined') {
                    if (response.status == true) {
                        // SUCCESS RESPONSE
                        var select2_elm = $('#delivery_method');

                        // remove all existing options in select2 element
                        select2_elm.empty();

                        if (response.data != null) {
                            // set default option - just will be selected in "view"
                            var newOption = new Option("- {{ ucwords('pilih salah satu') }} -", "", false, true);
                            select2_elm.append(newOption);
                            
                            // looping the response data to set new options
                            if ($('#shipper').val() == 1) { // JNE
                                $.each(response.data.data, function(key, value) {
                                    if (value.etd_thru == null) {
                                        var estimate = 0;
                                        var text = value.service_display + ' - Rp.' + value.price
                                    } else {
                                        var estimate = value.etd_thru;
                                        var text = value.service_display + ' - Rp.' + value.price + ' (' + value.etd_thru + ' Hari)'
                                    }
                                    newOption = '<option value="' + value.service_code + '" data-service-type="' + value.service_display + '" data-price="' + value.price + '" data-estimate="' + estimate + '">' + text + '</option>';
                                    select2_elm.append(newOption);
                                });
                            } else { // ANTERAJA
                                $.each(response.data.data, function(key, value) {
                                    var estimate = value.etd_last;
                                    var text = value.product_name + ' - Rp.' + value.rates + ' (' + value.etd_thru + ' Hari)'
                                    newOption = '<option value="' + value.product_code + '" data-service-type="' + value.product_name + '" data-price="' + value.rates + '" data-estimate="' + estimate + '">' + text + '</option>';
                                    select2_elm.append(newOption);
                                });
                            }

                            // enable the select2 element
                            select2_elm.prop('disabled', false);

                            $('#origin_code').val(response.origin_code);
                            $('#destination_code').val(response.destination_code);
                        } else {
                            // set default option - just will be selected in "view"
                            var newOption = new Option("*{{ strtoupper('tidak ada data') }}", "", false, true);
                            select2_elm.append(newOption);
                            // disable the select2 element
                            select2_elm.prop('disabled', true);
                        }
                    } else {
                        // FAILED RESPONSE
                        alert(response.message);

                        // set default option - just will be selected in "view"
                        var select2_elm = $('#delivery_method');

                        // remove all existing options in select2 element
                        select2_elm.empty();

                        var newOption = new Option("*{{ strtoupper('tidak ada data') }}", "", false, true);
                        select2_elm.append(newOption);

                        // disable the select2 element
                        // select2_elm.prop('disabled', true);
                    }
                } else {
                    alert('Server not respond, please try again.');
                }
            })
            .fail(function (jqXHR, textStatus, errorThrown) {
                // Callback handler that will be called on failure

                // Log the error to the console
                console.error("The following error occurred: " + textStatus, errorThrown);

                alert("Gagal mendapatkan daftar layanan pengiriman yang dipilih, mohon coba lagi.");
            })
            .always(function () {
                // Callback handler that will be called regardless
                // if the request failed or succeeded
                $('#loading-pengiriman').hide();
                $('#pilih-pengiriman').show();
            });
        }

        $(document).ready(function() {
            $('#address').change(function() {
                $("#shipper").val('').trigger('change');
                $("#delivery_method").empty().trigger('change');
            });

            $('#shipper').change(function() {
                var selected_shipper = $("#shipper").val();
                if (selected_shipper != null) {
                    // CHECK ADDRESS
                    var check_address = $('#address').val();
                    if (isNaN(check_address)) {
                        alert('Harap pilih alamat terlebih dahulu');
                        // reset pilihan kurir
                        $("#shipper").val('').trigger('change');
                        return;
                    }

                    get_shipment();
                }
            });

            $('#delivery_method').change(function() {
                var selected_shipper = $("#shipper").val();
                if (selected_shipper != null) {
                    var service_type    = $(this).find(':selected').data('service-type');
                    var price           = $(this).find(':selected').data('price');
                    var estimate        = $(this).find(':selected').data('estimate');
                    
                    var formatter = new Intl.NumberFormat('en-US', {
                        style: 'currency',
                        currency: 'USD',
                    });

                    var price_subtotal  = parseInt('{{ $data_detail->total_price }}');
                    var price_shipping  = price;
                    var price_insurance = 0;
                    var price_total     = price_subtotal + price_insurance + price_shipping;

                    if ($('#insurance_shipping_fee').val() != undefined) {
                        price_insurance = parseInt($('#insurance_shipping_fee').val());
                    }
                    
                    $('#price_shipping_view').empty();
                    $('#total_price_view').empty();

                    $('#estimate').val(estimate);
                    $('#service_type').val(service_type);
                    $('#price_shipping').val(price_shipping);
                    $('#price_shipping_view').append('Rp'+formatNumber(price_shipping));
                    $('#total_price').val(price_total);
                    $('#total_price_view').append('Rp'+formatNumber(price_total));
                }
            });

            $("#use_insurance_shipping").change(function() {
                var price_subtotal  = parseInt('{{ $data_detail->total_price }}');
                var price_shipping  = 0;
                var price_insurance = 0;

                if($('#price_shipping').val() != undefined) {
                    price_shipping = parseInt($('#price_shipping').val());
                }

                if (this.checked) {
                    // ASURANSI = 0,2% x harga barang + 5000 (b. adm)
                    var price_insurance = (0.2 / 100) * price_subtotal + 5000;
                } else {
                    var price_insurance = 0;
                }

                var price_total = price_subtotal + price_insurance + price_shipping;
                
                $('#insurance_shipping_fee_view').empty();
                $('#total_price_view').empty();

                $('#insurance_shipping_fee').val(price_insurance);
                $('#insurance_shipping_fee_view').append('Rp'+formatNumber(price_insurance));
                $('#total_price').val(price_total);
                $('#total_price_view').append('Rp'+formatNumber(price_total));
            });
        });
    </script>
@endsection