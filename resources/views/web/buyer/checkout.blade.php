@extends('_template_web.master')

@php
    use App\Libraries\Helper;
@endphp

@section('title', 'Pengiriman')

@section('css-plugins')
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/jquery-ui.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/jquery-ui.theme.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/select2.css') }}">

    <style>
        .alamat_name {
            display: inline-block;
            text-transform: uppercase;
            font-family: 'WorkSansBold';
        }
        .alamat_utama {
            background: #ADADAD;
            color: #4E4E4E;
            padding: 0 10px;
            text-transform: none;
            font-family: 'WorkSans';
            margin-left: 5px;
        }
    </style>
@endsection

@section('script-plugins')
    <script type="text/javascript" src="{{ asset('web/js/jquery-ui.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('web/js/select2.full.min.js') }}"></script>
    <script>
        function formatNumber(num) {
            return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.')
        }
    </script>
@endsection

@section('content')
    @include('_template_web.header_with_categories')
    @include('_template_web.alert_popup')

    <section class="white_bg no_categories">
        <div class="section_profile wishlist">
            <div class="profile_menu">
                <h2>Pengiriman</h2>
            </div>
        </div>

        <div class="section_checkout">
            <form action="{{ route('web.cart.create_order') }}" method="post" onsubmit="return validate_form()">
                @csrf
                <div class="container">
                    <div class="checkout_left">
                        <div class="co_address">
                            <span class="title">Alamat</span>
                            <select class="select2" id="buyer_address" name="address_id" required>
                                @if (isset($buyer_address[0]))
                                    @foreach ($buyer_address as $key => $address)
                                        <option value="{{ $address->object_id }}" data-zipcode="{{ $address->postal_code }}" @if ($address->is_default) selected @endif>
                                            {{ $address->name }} - {{ $address->address_details }}, {{ $address->village_name . ', ' . $address->sub_district_name . ', ' . $address->city_name . ', ' . $address->province_name . ' ' . $address->postal_code }} - {{ $address->fullname }} - {{ $address->phone_number }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>

                            @if (isset($selected_address))
                                <div class="co_contact">
                                    <span class="alamat_name" id="alamat_name">{{ $selected_address->name }}</span>@if ($selected_address->is_default) <span class="alamat_utama" id="alamat_utama">Utama</span><br> @endif
                                    <span id="shipment_fullname">{{ $selected_address->fullname }}</span><br>
                                    <span id="shipment_phone_number">{{ $selected_address->phone_number }}</span><br>
                                    <span id="shipment_address_details">
                                        {{ $selected_address->address_details }}<br>
                                        {{ $selected_address->village_name . ', ' . $selected_address->sub_district_name . ', ' . $selected_address->city_name . ', ' . $selected_address->province_name . ' ' . $selected_address->postal_code }}
                                    </span>
                                </div>
                            @else
                                <div class="co_contact">
                                    <a href="{{ route('web.buyer.list_address') }}">
                                        Anda belum mendaftarkan alamat pengiriman. Klik di sini untuk mendaftarkan alamat pengiriman
                                    </a>
                                </div>
                            @endif
                        </div>

                        @php 
                            $subtotal       = 0; 
                            $seller_number  = 0; 
                        @endphp
                        @foreach ($data as $key => $item_seller)
                            <div class="co_wrapper">
                                <span class="title">{{ $key }}</span>
                                @php
                                    $subtotal_seller = 0; 
                                    $need_insurance_by_seller = 0;
                                @endphp

                                @foreach ($item_seller as $product_checkout)
                                    @php
                                        $total_price = $product_checkout['price'] * $product_checkout['qty'];

                                        if ($need_insurance_by_seller == 0) {
                                            $need_insurance_by_seller = $product_checkout['need_insurance'];
                                        }
                                    @endphp

                                    <div class="co_box">
                                        <div class="row_clear">
                                            <div class="co_img"><a href="javascript:void(0);" style="cursor: default;"><img src="{{ asset($product_checkout['image']) }}"></a></div>
                                            <div class="co_desc">
                                                <h4><a href="javascript:void(0);" style="cursor: default;">{{ $product_checkout['name'] }}</a></h4>
                                                <span class="co_price price_format">{{ $total_price }}</span>
                                                <span class="co_pcs">{{ $product_checkout['qty'] }} pcs</span>
                                            </div>
                                        </div>

                                        <div class="row_clear notes">
                                            @if (!empty($product_checkout['note']))
                                                <span class="notes_btn">{{ $product_checkout['note'] }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    @php $subtotal_seller += $total_price; @endphp
                                @endforeach

                                @php
                                    $shipment           = null;
                                    $seller_id          = null;
                                    $total_quantity     = null;
                                    $total_weight       = null;
                                    $origin_code        = null;
                                    $destination_code   = null;

                                    if (isset($shipment_detail[0])) {
                                        foreach ($shipment_detail as $detail) {
                                            if ($detail->seller_id == $item_seller[0]['seller_id']) {
                                                $seller_id          = Helper::generate_token($detail->seller_id);
                                                $total_quantity     = $detail->total_quantity;
                                                $total_weight       = $detail->calculate_weight;
                                                $shipment           = $detail->shipment->data;
                                                $origin_code        = $detail->origin_code;
                                                $destination_code   = $detail->destination_code;
                                                break;
                                            }
                                        }
                                    }
                                @endphp
                                
                                <div class="mtd_pengiriman">
                                    <span class="title_1">Metode Pengiriman</span>
                                    <input type="hidden" value="{{ $seller_id }}" id="seller_{{ $seller_number }}">
                                    <input type="hidden" value="{{ $total_quantity }}" id="total_quantity_{{ $seller_number }}">
                                    <input type="hidden" value="{{ $total_weight }}" id="total_weight_{{ $seller_number }}">
                                    <input type="hidden" value="{{ $origin_code }}" id="origin_code_{{ $seller_number }}">
                                    <input type="hidden" value="{{ $destination_code }}" id="destination_code_{{ $seller_number }}">

                                    <select class="select2 delivery_method" name="delivery_method[{{ $seller_id }}]" id="delivery_method_{{ $seller_number }}" onchange="generate_delivery_fee({{ $seller_number }})" required>
                                        <option selected disabled>- {{ ucwords('pilih salah satu') }} -</option>
                                        @if (!is_null($shipment))
                                            @foreach ($shipment as $v_shipment)
                                                @php $shipment_label = $v_shipment->product_name . ' - Rp' . number_format($v_shipment->rates,0,",",".") . ' (' . $v_shipment->etd_thru . ' Hari)'; @endphp
                                                <option value="{{ $v_shipment->product_code }}" data-service-type="{{ $v_shipment->product_name }}" data-price="{{ $v_shipment->rates }}" data-estimate="{{ $v_shipment->etd_last }}">{{ $shipment_label }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <div class="co_subtotal">
                                    <div class="row_clear">
                                        <div class="text_left">Subtotal</div>
                                        <div class="text_right price_format">{{ $subtotal_seller }}</div>
                                        <input type="hidden" id="subtotal_seller_{{ $seller_number }}" value="{{ $subtotal_seller }}">
                                    </div>

                                    <div class="row_clear">
                                        <div class="text_left">
                                            @if ($need_insurance_by_seller == 1)
                                                {{-- WAJIB ASURANSI OLEH SELLER --}}
                                                <label>
                                                    <input type="checkbox" onclick="use_insurance_shipping({{ $seller_number }});" id="insurance_shipping_{{ $seller_number }}" name="use_shipping_insurance[{{ $seller_id }}]" disabled checked>
                                                    <span>Asuransi Pengiriman</span>
                                                </label>

                                                @php
                                                    $insurance_script[] = "use_insurance_shipping(" . $seller_number . ");"
                                                @endphp
                                            @else
                                                <label>
                                                    <input type="checkbox" onclick="use_insurance_shipping({{ $seller_number }});" id="insurance_shipping_{{ $seller_number }}" name="use_shipping_insurance[{{ $seller_id }}]">
                                                    <span>Asuransi Pengiriman</span>
                                                </label>
                                            @endif
                                        </div>
                                        <div class="text_right" id="insurance_fee_view_{{ $seller_number }}">Rp0</div>
                                        <input type="hidden" id="insurance_fee_{{ $seller_number }}" value="0">
                                    </div>

                                    <div class="row_clear">
                                        <div class="text_left">Ongkos Kirim</div>
                                        <div class="text_right" id="delivery_price_view_{{ $seller_number }}">Rp0</div>
                                        <input type="hidden" id="delivery_price_{{ $seller_number }}" value="0">
                                    </div>
                                </div>
                            </div>
                            
                            @php
                                $subtotal += $subtotal_seller;
                                $seller_number++;
                            @endphp
                        @endforeach
                    </div>

                    <div class="checkout_right">
                        <div class="pos_sticky">
                            <h3>Rincian Pesanan</h3>
                            <div class="row_clear">
                                <div class="text_left">Subtotal</div>
                                <div class="text_right price_format" id="sub_total_view">{{ $subtotal }}</div>
                                <input type="hidden" id="sub_total" name="subtotal" value="{{ $subtotal }}" required>
                            </div>

                            <div class="row_clear">
                                <div class="text_left">Total Ongkos Kirim</div>
                                <div class="text_right" id="total_delivery_fee_view">Rp0</div>
                                <input type="hidden" id="total_delivery_fee" name="shipping_fee" value="0" required>
                            </div>

                            <div class="row_clear">
                                <div class="text_left">Asuransi Pengiriman</div>
                                <div class="text_right" id="total_insurance_fee_view">Rp0</div>
                                <input type="hidden" id="total_insurance_fee" name="shipping_insurance_fee" value="0" required>
                            </div>

                            <div class="row_clear" id="voucher_div"></div>
                            <input type="hidden" id="discount" name="discount_amount" value="0">
                            <div class="row_clear subtotal">
                                <div class="text_left bold">Harga Total</div>
                                <div class="text_right bold" id="total_price_view">Rp0</div>
                                <input type="hidden" id="total_price" name="total_amount" value="0" required>
                            </div>

                            <div class="voucher_box">
                                <span class="title">Punya voucher?</span>
                                <div class="row_clear">
                                    <input type="text" id="voucher_code" name="voucher_code">
                                    <button type="button" class="submit_btn" id="submit_voucher">Pakai</button>
                                </div>

                                <div id="voucher_text"></div> 
                            </div>

                            <div class="v_tnc_box">
                                <a href="#">Syarat dan Ketentuan</a>
                            </div>

                            <div class="button_wrapper">
                                <button type="submit" class="red_btn" id="btn_submit">Pilih Pembayaran</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    @include('_template_web.footer')

    <div class="popup_voucher">
        <div class="popup_voucher_box">
            <div class="container">
                <h3 id="voucher_name"></h3>
                <div id="voucher_period"></div>
                <h3>DESKRIPSI</h3>
                <div id="voucher_desc"></div>
            </div>
            <a href="#" class="close_btn">Close</a>
        </div>
    </div>
@endsection

@section('footer-script')
    <script>
        $("#buyer_address").change(function() {
            var CSRF_TOKEN  = $('meta[name="csrf-token"]').attr('content');
            var address_id = $(this).val();

            $.ajax({
                type: "POST",
                url: "{{ route('web.buyer.detail_buyer_address') }}",
                data: {
                    _token : CSRF_TOKEN,
                    address_id : address_id,
                },
                dataType: "json",
                beforeSend: function() {
                    // LOADING HERE
                    $("#shipment_fullname").text('Loading..');
                    $("#shipment_phone_number").text('Loading..');
                    $("#shipment_address_details").text('Loading..');
                }
            })
            .done(function(response) {
                console.log(response)
                if (typeof response != 'undefined') {
                    if (response.status == 'success') {
                        var data            = response.data;
                        console.log(data)
                        // var detail_address  = data.name + ' - ' + data.village_name + ',  ' + data.sub_district_name + ', ' + data.city_name + ', ' + data.province_name + ', ' + data.postal_code + ' - ' + data.address_details ;
                        var detail_address  = data.address_details + '<br>' + data.village_name + ',  ' + data.sub_district_name + ', ' + data.city_name + ', ' + data.province_name + ', ' + data.postal_code;

                        $("#alamat_name").text(data.name);
                        $("#alamat_utama").show();
                        if (data.is_default != 1) {
                            $("#alamat_utama").hide();
                        } 
                        $("#shipment_fullname").text(data.fullname);
                        $("#shipment_phone_number").text(data.phone_number);
                        $("#shipment_address_details").html(detail_address);
                    } else {
                        alert(response.message);
                    }
                } else {
                    alert('Maaf, respon server tidak dikenali. Mohon coba lagi atau refresh halaman ini.');
                }
            })
            .fail(function() {
                alert('Maaf, gagal menghubungi server. Mohon coba lagi atau refresh halaman ini.');
            })
            .always(function() {});

            // GENERATE NEW DELIVERY METHOD
            get_shipment();
        });

        function submit_voucher() {
            var CSRF_TOKEN  = $('meta[name="csrf-token"]').attr('content');
            var voucher_code = $("#voucher_code").val();
            var shipment_fee = $("#total_delivery_fee_view").text();
            shipment_fee = shipment_fee.replace(/[^0-9]/g,''); // Get all number from string

            if (voucher_code.length == 0) {
                return false
            }

            $.ajax({
                type: "POST",
                url: "{{ route('web.cart.submit_voucher') }}",
                data: {
                    _token : CSRF_TOKEN,
                    voucher_code : voucher_code,
                    shipment_fee : shipment_fee
                },
                dataType: "json",
                beforeSend: function() {
                    // DISABLE BUTTON FOR PREVENT MULTI REQUEST
                    $("#submit_voucher").attr("disabled", true);
                }
            })
            .done(function(response) {
                if (typeof response != 'undefined') {
                    if (response.status == 'success') {
                        var data = response.data;
                        
                        // DIV VOUCHER CODE
                        var voucher_div = "";

                        voucher_div +=  "<div class='row_clear'>Kode Voucher</div>";
                        voucher_div +=  "<div class='row_clear'>";
                        voucher_div +=      "<div class='text_left bold'>" + data.unique_code + "</div>";
                        voucher_div +=      "<div class='text_right'>-Rp" + formatNumber(data.discount) + "</div>";
                        voucher_div +=  "</div>";

                        $("#voucher_div").html(voucher_div);
                        $("#discount").val(data.discount);

                        // SPAN VOUCHER TEXT
                        var voucher_text = "<span class='voucher_success'>*Selamat! Kamu dapat potongan harga!</span>";
                        $("#voucher_text").html(voucher_text);

                        // POPUP VOUCHER
                        $("#voucher_name").text(data.name);
                        $("#voucher_period").html('Periode Voucher : ' + data.period_begin_text + ' - ' + data.period_end_text);
                        var voucher_desc = $("#voucher_desc").html(data.description).text(); // ENCODE HTML SPECIAL CHAR
                        $("#voucher_desc").html(voucher_desc);
                        $('.v_tnc_box').show();
                    } else {
                        // DIV VOUCHER CODE
                        var html = ''
                        $("#voucher_div").html(html);
                        $("#discount").val(0);

                        // SPAN VOUCHER TEXT
                        var voucher_text = ''
                        $("#voucher_text").html(voucher_text);

                        var voucher_text = "<span class='voucher_failed'>*" + response.message + "</span>";
                        $("#voucher_text").html(voucher_text);

                        // POPUP VOUCHER
                        $("#voucher_name").text('');
                        var voucher_desc = '';
                        $("#voucher_desc").html(voucher_desc);
                        $('.v_tnc_box').hide();
                    }
                } else {
                    // DIV VOUCHER CODE
                    $("#voucher_div").html('');
                    $("#discount").val(0);

                    // SPAN VOUCHER TEXT
                    $("#voucher_text").html('');

                    var voucher_text = "<span class='voucher_failed'>*" + response.message + "</span>";
                    $("#voucher_text").html(voucher_text);

                    // POPUP VOUCHER
                    $("#voucher_name").text('');
                    $("#voucher_desc").html('');
                    $('.v_tnc_box').hide();

                    alert('Maaf, respon server tidak dikenali. Mohon coba lagi atau refresh halaman ini.');
                }
            })
            .fail(function() {
                // DIV VOUCHER CODE
                $("#voucher_div").html('');
                $("#discount").val(0);

                // SPAN VOUCHER TEXT
                $("#voucher_text").html('');

                var voucher_text = "<span class='voucher_failed'>*" + response.message + "</span>";
                $("#voucher_text").html(voucher_text);

                // POPUP VOUCHER
                $("#voucher_name").text('');
                $("#voucher_desc").html('');
                $('.v_tnc_box').hide();
                
                alert('Maaf, gagal menghubungi server. Mohon coba lagi atau refresh halaman ini.');
            })
            .always(function() {
                $("#submit_voucher").attr("disabled", false);
                detail_order();
            });
        }

        $("#submit_voucher").click(function() {
            submit_voucher();
        });

        function generate_delivery_fee(id) {
            var service_type    = $('#delivery_method_' + id).find(':selected').data('service-type');
            var price_shipping  = $('#delivery_method_' + id).find(':selected').data('price');
            var estimate        = $('#delivery_method_' + id).find(':selected').data('estimate');
                    
            var formatter = new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
            });
            
            $('#delivery_price_' + id).val(price_shipping);
            $('#delivery_price_view_' + id).html('');
            $('#delivery_price_view_' + id).html('Rp' + formatNumber(price_shipping));

            detail_order();

            // CHECK VOUCHER AGAIN
            if ($("#voucher_code").val().length != 0) {
                submit_voucher();
            }
        }

        function get_shipment() {
            var destination     = $('#buyer_address').find(':selected').data('zipcode'); // CHECK ADDRESS
            var count_seller    = $('.delivery_method').length; // COUNT SELLER

            for (let i = 0; i < count_seller; i++) {
                var seller_id   = $('#seller_' + i).val();
                var weight      = $('#total_weight_' + i).val();

                $.ajax({
                    type: "POST",
                    url: "{{ route('web.order.ajax_check_shipment_anteraja') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        destination: destination,
                        seller_id: seller_id,
                        weight: weight
                    },
                    beforeSend: function () {
                        // DO SOMETHING BEFORE SEND THE DATA
                    },
                })
                .done(function (response) {
                    // CALLBACK HANDLER THAT WILL BE CALLED ON SUCCESS
                    if (typeof response != 'undefined') {
                        if (response.status == true) {
                            // SUCCESS RESPONSE
                            var select2_elm = $('#delivery_method_' + i);

                            // REMOVE ALL EXISTING OPTIONS IN SELECT2 ELEMENT
                            select2_elm.empty();

                            if (response.data != null) {
                                // SET DEFAULT OPTION - JUST WILL BE SELECTED IN "VIEW"
                                var newOption = new Option("- {{ ucwords('pilih salah satu') }} -", "", false, true);
                                select2_elm.append(newOption);
                                var newOption = '';
                                
                                // LOOPING THE RESPONSE DATA TO SET NEW OPTIONS
                                $.each(response.data.data, function(key, value) {
                                    var estimate    = value.etd_last;
                                    var text        = value.product_name + ' - Rp' + number_formatting(value.rates.toString(), '.') + ' (' + value.etd_thru + ' Hari)';
                                    newOption       = '<option value="' + value.product_code + '" data-service-type="' + value.product_name + '" data-price="' + value.rates + '" data-estimate="' + estimate + '">' + text + '</option>';

                                    select2_elm.append(newOption);
                                });

                                // ENABLE THE SELECT2 ELEMENT
                                select2_elm.prop('disabled', false);

                                $('#origin_code_' + i).val(response.origin_code);
                                $('#destination_code_' + i).val(response.destination_code);
                            } else {
                                // SET DEFAULT OPTION - JUST WILL BE SELECTED IN "VIEW"
                                var newOption = new Option("*{{ strtoupper('tidak ada data') }}", "", false, true);
                                select2_elm.append(newOption);
                                
                                // DISABLE THE SELECT2 ELEMENT
                                select2_elm.prop('disabled', true);
                            }
                        } else {
                            // FAILED RESPONSE
                            alert(response.message);

                            // SET DEFAULT OPTION - JUST WILL BE SELECTED IN "VIEW"
                            var select2_elm = $('#delivery_method_' + i);

                            // REMOVE ALL EXISTING OPTIONS IN SELECT2 ELEMENT
                            select2_elm.empty();

                            var newOption = new Option("*{{ strtoupper('tidak ada data') }}", "", false, true);
                            select2_elm.append(newOption);

                            // disable the select2 element
                            // select2_elm.prop('disabled', true);
                        }
                    } else {
                        alert('Server not respond, please try again.');
                    }
                })
                .fail(function (jqXHR, textStatus, errorThrown) {
                    // CALLBACK HANDLER THAT WILL BE CALLED ON FAILURE

                    // LOG THE ERROR TO THE CONSOLE
                    console.error("The following error occurred: " + textStatus, errorThrown);

                    alert("Gagal mendapatkan daftar layanan pengiriman yang dipilih, mohon coba lagi.");
                })
                .always(function () {
                    // CALLBACK HANDLER THAT WILL BE CALLED REGARDLESS
                    // IF THE REQUEST FAILED OR SUCCEEDED
                });
            }

            detail_order();
        }

        function use_insurance_shipping(id) {
            var price_subtotal  = parseInt($('#subtotal_seller_' + id).val());
            var price_insurance = 0;

            if ($('#delivery_price_' + id).val() != undefined) {
                price_shipping = parseInt($('#delivery_price_' + id).val());
            }

            if ($('#insurance_shipping_' + id).is(':checked')) {
                // ASURANSI = 0,2% X HARGA BARANG + 5000 (BIAYA ADM)
                var price_insurance = (0.2 / 100) * price_subtotal + 5000;
            } else {
                var price_insurance = 0;
            }

            $('#insurance_fee_' + id).val(price_insurance);
            $('#insurance_fee_view_' + id).html('');
            $('#insurance_fee_view_' + id).html('Rp' + formatNumber(price_insurance));

            detail_order();
        }

        function detail_order() {
            var count_seller = $('.delivery_method').length; // COUNT SELLER

            var subtotal_seller = 0;
            var insurance_fee   = 0;
            var delivery_price  = 0;
            var price_total     = 0;
            var discount        = parseInt($('#discount').val());

            for (let i = 0; i < count_seller; i++) {
                subtotal_seller += parseInt($('#subtotal_seller_' + i).val());
                insurance_fee   += parseInt($('#insurance_fee_' + i).val());
                delivery_price  += parseInt($('#delivery_price_' + i).val());
            }

            price_total = (subtotal_seller + insurance_fee + delivery_price) - discount;

            // INPUT HIDDEN
            $('#sub_total').val(subtotal_seller);
            $('#total_delivery_fee').val(delivery_price);
            $('#total_insurance_fee').val(insurance_fee);
            $('#total_price').val(price_total);
            
            // SHOW PRICE
            $('#sub_total_view').html('Rp' + formatNumber(subtotal_seller));
            $('#total_delivery_fee_view').html('Rp' + formatNumber(delivery_price));
            $('#total_insurance_fee_view').html('Rp' + formatNumber(insurance_fee));
            $('#total_price_view').html('Rp' + formatNumber(price_total));
        }

        function validate_form() {
            $('#btn_submit').attr('disabled', 'disabled');
            $('#btn_submit').html('LOADING...');
            show_loader();
        }

        $(document).ready(function() {
            $('.v_tnc_box').hide();

            $('.v_tnc_box').click(function() {
                $('.popup_voucher').show();
                return false;
            })

            $('.popup_voucher .close_btn').click(function() {
                $('.popup_voucher').hide();
                return false;
            })

            // PRICE FORMATING
            $('.price_format').each(function() {
                var price = $(this).text();

                if (!isNaN(price)) {
                    $(this).text('Rp'+formatNumber(price));
                }
            });

            @if (count($errors) > 0)
                @php
                    $error_message = [];
                    foreach ($errors->all() as $error) {
                        $error_message[] = $error;
                    }
                @endphp
                show_popup_alert("error", "Error!", "{{ implode(', ', $error_message) }}")
            @endif
            
            @if (session('error'))
                show_popup_alert("error", "Error!", "{!! session('error') !!}")
            @endif

            @if (isset($insurance_script))
                @foreach ($insurance_script as $script)
                    {{ $script }}
                @endforeach
            @endif
        })
    </script>
@endsection