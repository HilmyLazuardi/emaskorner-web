@extends('_template_web.master')

@php
    // LIBRARIES
    use App\Libraries\Helper;
    use App\Libraries\HelperWeb;
    
    // MODELS
    use App\Models\seller;
    
    // if add new, declare empty variables
    $access = []; 
@endphp

@section('title', 'Keranjang')

@section('css-plugins')
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/jquery-ui.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/jquery-ui.theme.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/slick.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('web/css/slick-theme.css') }}">
@endsection

@section('script-plugins')
    <script type="text/javascript" src="{{ asset('web/js/jquery-ui.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('web/js/select2.full.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('web/js/slick.js') }}"></script>

    <script>
        $(document).ready(function(){
            @if (session('error'))
                show_popup_alert("error", "Error!", "{!! session('error') !!}")
            @endif

            $('#master_check_all').change(function() {
                if ($(this).is(':checked')) {
                    var drop_data = '';
                    $('#cart_product input[type="checkbox"]').each(function() {
                        $('input[type="checkbox"]').prop('checked', true);
                        var id_data = $(this).attr('id');
				        $('#delete_btn').show();
                        $('.red_btn').attr('disabled', false);
                        
                        // COLECT DATA
                        drop_data += $('#' + id_data).val() + ',';

                        // EMPTY DATA
                        $('#drop_cart').val('');

                        // PUT DATA
                        $('#drop_cart').val(drop_data);
                    });
                } else {
                    $('input[type="checkbox"]').prop('checked', false);
				    $('#delete_btn').hide();
                    $('.red_btn').attr('disabled', true);
                    // EMPTY DATA
                    $('#drop_cart').val('');
                }
            });
        });

        // CHECKBOX PRODUCT BY SELLER - BEGIN
        // $('#master_check_all').on("click", function() {
        //     is_all_seller_checked(,true);
        // });

        function check_all_seller(id, flag_id) {
            var all = $('.'+id).length;
            var total = $('.'+id+':checked').length;
            // console.log('seller_product',total)
            // console.log('seller_product',all)

            if (total == all){
                $("."+id).prop('checked', false);
                $("#master_check_all").prop('checked', false);
                if ($('.cart_product input:checkbox:checked').length == 0) {
                    $('#drop_cart').val('');
                    $('#delete_btn').hide();
                    $('.red_btn').attr('disabled', true);
                } else {
                    $('#drop_cart').val($('#product_cart_' + flag_id).val() + ',');
                }
            } else {
                $("."+id).prop("checked", true);
                is_all_seller_checked()
                if ($("#check_all_seller_"+id).is(':checked')) {
                    var drop_data = '';
                    var checked_items = $('#drop_cart').val();
                    $('.cart_product_'+id+' input[type="checkbox"]').each(function() {
                        // $('input[type="checkbox"]').prop('checked', true);
                        var id_data = $(this).attr('id');
                        // console.log(id_data)
                        if ($('.cart_product input:checkbox:checked').length > 0) {
                            $('#delete_btn').show();
                            $('.red_btn').attr('disabled', false);
                        }
                        
                        // COLECT DATA
                        drop_data = $('#' + id_data).val() + ',';

                        // EMPTY DATA
                        // $('#drop_cart').val('');

                        if (checked_items.includes(id_data)) {
                            // ITEM ALREADY EXIST
                        } else {
                            // PUSH DATA TO ARRAY
                            checked_items += drop_data;
                            $('#drop_cart').val(checked_items)
                        }

                        // PUT DATA
                        // $('#drop_cart').val(drop_data);
                    });
                }
            }
        }

        function is_all_product_checked(id, flag_id) {
            var all = $('.'+id).length;
            var total = $('.'+id+':checked').length;
            // console.log('product',total)
            // console.log('product',all)
            
            if ($('#product_cart_' + flag_id).is(':checked')) {
                if (total == all) {
                    $("#check_all_seller_"+id).prop("checked", "checked");
                }
                // is_all_seller_checked(id, true); // OLD
                is_all_seller_checked();
                check_item(flag_id);
				$('#delete_btn').show();
                $('.red_btn').attr('disabled', false);
            } else {
                // console.log('sini')
                $("#check_all_seller_"+id).prop('checked', false);
                $("#master_check_all").prop('checked', false);
                // EMPTY DATA
                if ($('.cart_product input:checkbox:checked').length == 0) {
                    $('#drop_cart').val('');
                    $('#delete_btn').hide();
                    $('.red_btn').attr('disabled', true);
                } else {
                    $('#drop_cart').val($('#product_cart_' + flag_id).val() + ',');
                }
            }
        }

        function is_all_seller_checked(active = false) {
            var all = $('.access_seller').length;
            var total = $('.access_seller:checked').length;
            // console.log('acc_seller',total)
            // console.log('acc_seller',all)
            // console.log(active)

            if (active) {
                if (total == all && $('#master_check_all:checked').length == 0){
                    // console.log($('#master_check_all:checked').length == 0)
                    // console.log('sini')
                    $(".access_seller_"+id).prop('checked', false);
				    $('#delete_btn').hide();
                    $('.red_btn').attr('disabled', true);
                } else {
                    // console.log('sana')
                    $(".access_seller_"+id).prop("checked", "checked");
				    $('#delete_btn').show();
                    $('.red_btn').attr('disabled', false);
                }
            } else {
                if (total == all && $('#master_check_all:checked').length == 0){
                    $("#master_check_all").prop("checked", "checked");
				    $('#delete_btn').show();
                    $('.red_btn').attr('disabled', false);
                }
            }
        }
        // CHECKBOX PRODUCT BY SELLER - END

        function check_item(id) {
            var checked_items = $('#drop_cart').val();

            if ($('#product_cart_' + id).is(':checked')) {
                var target = $('#product_cart_' + id).val() + ',';
                // console.log(target)
                if (checked_items.includes(target)) {
                    // ITEM ALREADY EXIST
                } else {
                    // PUSH DATA TO ARRAY
                    checked_items += target;
                    $('#drop_cart').val(checked_items)
                }
            } 
        }

        // DELETE PRODUCT CART
        function delete_product(nonactive = false) {
            if (nonactive) {
                var count_nonactive = "{{ $count_nonactive }}"
                var result = confirm("Anda yakin ingin menghapus " + count_nonactive + " barang yang tidak bisa diproses?");
            } else {
                var result = confirm("Anda yakin ingin menghapus barang ini dari keranjang?");
            }

            if (result == true) {
                delete_data(nonactive);
            } else {
                event.preventDefault();
                $('#drop_cart').val('');
                $('#delete_btn').hide();
                $('.red_btn').attr('disabled', true);
                return false;
            }
        }

        function delete_data(nonactive = false) {
            var CSRF_TOKEN  = $('meta[name="csrf-token"]').attr('content');
            var params      = $('#drop_cart').val()

            if (nonactive) {
                params = '';
                $('.product_nonactive').each(function(i, obj) {
                    params += $(this).attr("data-id") + ',';
                });
            }

            $.ajax({
                type: "POST",
                url: "{{ route('web.buyer.delete_cart_ajax') }}",
                data: {
                    _token : CSRF_TOKEN,
                    params : params,
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

                        setTimeout(function() {
                            location.reload();
                            return true;
                        }, 2300);
                    } else {
                        show_popup_alert("error", "Error!", response.message);
                    }
                } else {
                    show_popup_alert("error", "Error!", 'Maaf, respon server tidak dikenali. Mohon coba lagi atau refresh halaman ini.');
                }
            })
            .fail(function() {
                show_popup_alert("error", "Error!", 'Maaf, gagal menghubungi server. Mohon coba lagi atau refresh halaman ini.');
            })
            .always(function() {});
        }

        // ADD TO WISHLIST
        function create_wishlist(id) {
            var CSRF_TOKEN  = $('meta[name="csrf-token"]').attr('content');
            var variant     = id;

            $.ajax({
                type: "POST",
                url: "{{ route('web.buyer.add_wishlist_ajax') }}",
                data: {
                    _token : CSRF_TOKEN,
                    variant : variant,
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

                        setTimeout(function() {
                            location.reload();
                            return true;
                        }, 2300);
                    } else {
                        show_popup_alert("error", "Error!", response.message);
                    }
                } else {
                    show_popup_alert("error", "Error!", 'Maaf, respon server tidak dikenali. Mohon coba lagi atau refresh halaman ini.');
                }
            })
            .fail(function() {
                show_popup_alert("error", "Error!", 'Maaf, gagal menghubungi server. Mohon coba lagi atau refresh halaman ini.');
            })
            .always(function() {});
        }

        // ADD TO CART IN SECTION 'Wujudkan Wishlist Kamu!'
        function add_cart(id, element) {
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            var variant = id;

            $.ajax({
                type: "POST",
                url: "{{ route('web.buyer.add_cart') }}",
                data: {
                    _token : CSRF_TOKEN,
                    variant : variant,
                },
                dataType: "json",
                beforeSend: function() {
                    // LOADING
                    $(element).html('LOADING...');
                    $(element).attr('disabled', 'disabled');
                }
            })
            .done(function(response) {
                if (typeof response != 'undefined') {
                    if (response.status == 'success') {
                        show_popup_alert("success", "Berhasil", response.message);
                        // alert(response.message);

                        setTimeout(function() {
                            location.reload();
                            return true;
                        }, 2300);
                        
                        return true;
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
            .always(function() {
                refresh_data_count_cart();

                $(element).html('+ Keranjang');
                $(element).removeAttr('disabled');
            });
        }
    </script>

    <script>
        // INCREMENT DECREMENT AND INPUT VALUE QUANTITY - BEGIN
        function increment_quantity(cart_id, price, id) {
            var inputQuantityElement = $("#input-quantity-"+cart_id);
            var newQuantity = parseInt($(inputQuantityElement).val())+1;
            var sub_total_new = parseInt(price) * newQuantity;
            sub_total_new = 'Rp' + number_formatting(sub_total_new.toString(), '.');
            
            fieldName = $('.increment_'+cart_id).attr('data-field-'+cart_id);
            type      = $('.increment_'+cart_id).attr('data-type-'+cart_id);
            var input = $("input[name='"+fieldName+"']");
            var currentVal = parseInt(inputQuantityElement.val());

            if (!isNaN(currentVal)) {
                if(type == 'plus') {
                    if(currentVal < input.attr('max')) {
                        // input.val(currentVal + 1).change();
                        // $(inputQuantityElement).val(newQuantity);
                        update_cart(cart_id, newQuantity, sub_total_new, id);
                        // $('#harga_by_total_'+cart_id).html(sub_total_new);
                    }

                    if(parseInt(input.val()) == input.attr('max')) {
                        // $(inputQuantityElement).attr('disabled', true);
                    }
                }
            }
        }

        $(document).ready(function(){
            $('.input-number').each(function(i, obj){
                $(obj).data('oldValue', $(obj).val());
            });
        });

        function input_number(cart_id, price, id) {
            $('.input-number-'+cart_id).focusin(function(){
                $(this).data('oldValue', $(this).val());
            });

            $('.input-number-'+cart_id).change(function() {
                // alert('number change');
                minValue =  parseInt($("#input-quantity-"+cart_id).attr('min'));
                maxValue =  parseInt($("#input-quantity-"+cart_id).attr('max'));
                valueCurrent = parseInt($("#input-quantity-"+cart_id).val());
                var old_total = parseInt(price) * valueCurrent;
                old_total = 'Rp' + number_formatting(old_total.toString(), '.');

                name = $("#input-quantity-"+cart_id).attr('name');
                if(valueCurrent >= minValue) {
                    $(".min_btn[data-type-"+cart_id+"='minus'][data-field-"+cart_id+"='"+name+"']").removeAttr('disabled')
                } else {
                    show_popup_alert("error", "Error!", 'Maaf, kamu telah mencapai jumlah minimum');
                    // var a = $('#input-quantity-'+cart_id).val($("#input-quantity-"+cart_id).data('oldValue'));
                    var a = $(this).val($(this).data('oldValue'));
                }
                if(valueCurrent <= maxValue) {
                    $(".max-btn-[data-type-"+cart_id+"='plus'][data-field-"+cart_id+"='"+name+"']").removeAttr('disabled')
                } else {
                    show_popup_alert("error", "Error!", 'Maaf, stok barang sudah mencapai jumlah maksimum');
                    // $('.input-number-'+cart_id).val($('.input-number-'+cart_id).data('oldValue'));
                    $(this).val($(this).data('oldValue'));
                }
                if (valueCurrent <= maxValue && valueCurrent >= minValue) {
                    update_cart(cart_id, valueCurrent, old_total, id)
                }
            });

            $(".input-number-"+cart_id).keydown(function (e) {
                // alert('number keydown');
                // Allow: backspace, delete, tab, escape, enter and .
                if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
                    // Allow: Ctrl+A
                    (e.keyCode == 65 && e.ctrlKey === true) || 
                    // Allow: home, end, left, right
                    (e.keyCode >= 35 && e.keyCode <= 39)) {
                    // let it happen, don't do anything
                    return;
                }
                // Ensure that it is a number and stop the keypress
                if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                    e.preventDefault();
                }
            });
        }

        function decrement_quantity(cart_id, price, id) {
            var inputQuantityElement = $("#input-quantity-"+cart_id);
            if($(inputQuantityElement).val() > 1) 
            {
                var newQuantity = parseInt($(inputQuantityElement).val()) - 1;
                var sub_total_new = parseInt(price) * newQuantity;
                sub_total_new = 'Rp' + number_formatting(sub_total_new.toString(), '.');
                
                fieldName = $('.decrement_'+cart_id).attr('data-field-'+cart_id);
                type      = $('.decrement_'+cart_id).attr('data-type-'+cart_id);
                var input = $("input[name='"+fieldName+"']");
                var currentVal = parseInt(inputQuantityElement.val());

                if (!isNaN(currentVal)) {
                    if(type == 'minus') {
                        if(currentVal > input.attr('min')) {
                            // input.val(currentVal + 1).change();
                            // $(inputQuantityElement).val(newQuantity);
                            update_cart(cart_id, newQuantity, sub_total_new, id);
                            // $('#harga_by_total_'+cart_id).html(sub_total_new);
                        }

                        if(parseInt(input.val()) == input.attr('min')) {
                            // $(inputQuantityElement).attr('disabled', true);
                        }
                    }
                }
            }
        }

        function update_cart(cart_id, new_qty, new_total, id) {
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            var inputQuantityElement = $("#input-quantity-"+cart_id);

            $.ajax({
                type: "POST",
                url: "{{ route('web.buyer.update_cart') }}",
                data: {
                    _token : CSRF_TOKEN,
                    id : id,
                    new_qty : new_qty
                },
                dataType: "json",
                beforeSend: function() {
                    // LOADING HERE
                }
            })
            .done(function(response) {
                if (typeof response != 'undefined') {
                    if (response.status == 'success') {
                        $(inputQuantityElement).val(new_qty);
                        $('#harga_by_total_'+cart_id).html(new_total);
                        return true;
                    } else {
                        show_popup_alert("error", "Error!", response.message);
                    }
                } else {
                    show_popup_alert("error", "Error!", 'Maaf, respon server tidak dikenali. Mohon coba lagi atau refresh halaman ini.');
                }
            })
            .fail(function() {
                show_popup_alert("error", "Error!", 'Maaf, gagal menghubungi server. Mohon coba lagi atau refresh halaman ini.');
            })
            .always(function() {});
        }

        /**
         * for replaces some characters with some other characters in a string.
         */
        function replace_all(search, replacement, target) {
            if (target !== null) {
                return target.split(search).join(replacement);
            }
            return "";
        }

        /**
         * formats a number with grouped thousands.
         *
         * @param {integer} number required
         * @param {char} separator optional
         */
        function number_formatting(number, separator = ",") {
            // sanitizing number
            number = replace_all(" ", "", number);
            number = replace_all(",", "", number);
            number = replace_all(".", "", number);

            // check is negative number
            var negative = number.substring(0, 1);
            if (negative == "-") {
                number = number.substring(1);
            } else {
                negative = "";
            }

            number = "" + Math.round(number);
            if (number.length > 3) {
                var mod = number.length % 3;
                var output = mod > 0 ? number.substring(0, mod) : "";
                for (i = 0; i < Math.floor(number.length / 3); i++) {
                    if (mod == 0 && i == 0)
                        output += number.substring(mod + 3 * i, mod + 3 * i + 3);
                    else
                        output +=
                        separator + number.substring(mod + 3 * i, mod + 3 * i + 3);
                }
                return negative + output;
            } else return negative + number;
        }
        // INCREMENT DECREMENT AND INPUT VALUE QUANTITY - END
    </script>
@endsection

@section('content')
    @include('_template_web.header_with_categories')
    @include('_template_web.alert_popup')

    
    <section class="white_bg no_categories">
        <div class="section_profile wishlist">
            <div class="profile_menu">
                <h2>Keranjang</h2>
            </div>
        </div>
        <div class="section_cart_wrapper">
            <div class="new_container">
                <div class="scw_left">
                    <div class="row_clear sc_top">
                        <div class="container width960">
                            <div class="ca_btn">
                                <input type="checkbox" class="choose_all" id="master_check_all" name="access_all" value="AL"><span>Pilih Semua</span>
                            </div>
                            <div class="delete_btn" id="delete_btn" style="display: none;">
                                <a href="javascript:void(0)" onclick="delete_product()">Hapus</a>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION CART - BEGIN --}}
                    @if (!empty($cart_active))
                        <input type="hidden" id="drop_cart">
                        <input type="hidden" id="selected_product">
                        @foreach ($cart_active as $key => $data)
                            @php
                                // FLAG SELLER ID
                                $flag_seller_id = date('Ymd') . $key . date('His');
                            
                                $flag_id = date('Ymd') . $data['variant_id'] . date('His');
                            @endphp
                            <div class="section_cart">
                                <div class="container width1040">
                                    <div class="label_seller">
                                        <input type="checkbox" class="access_seller" id="check_all_seller_{{ $flag_seller_id }}" onclick="check_all_seller(`{{ $flag_seller_id }}`,`{{ $flag_id }}`)"><span>{{ $data['seller_name'] }}</span>
                                    </div>
                                    @foreach ($data['data'] as $item)
                                        @php
                                            // ENCRIPT VARIANT ID
                                            $object_id = $item->variant_id;
                                            if (env('CRYPTOGRAPHY_MODE', false)) {
                                                $object_id = Helper::generate_token($item->variant_id);
                                            }
                            
                                            $flag_id = date('Ymd') . $item->variant_id . date('His');
                            
                                            // SET NAME
                                            $item->set_name = $item->name . ' - ' . $item->variant_name;
                                            
                                            // SET IMAGE
                                            $item->set_image = $item->image;
                                            if (!is_null($item->variant_image)) {
                                                $item->set_image = $item->variant_image;
                                            }

                                            // SET QTY
                                            $item->set_qty = $item->qty;
                                            if (!$item->global_stock) {
                                                $item->set_qty = $item->variant_qty;
                                            }

                                            // SET PRICE
                                            if (isset($item->variant_price)) {
                                                $calculate_price = $item->variant_price * $item->cart_qty;
                                                $harga = number_format($item->variant_price, 0, ',', '.');
                                                $sub_total = number_format($calculate_price, 0, ',', '.');
                                            }
                                        @endphp
                                        <div class="cart_product_wrapper cart_product_{{ $flag_seller_id }}" id="cart_product">
                                            <div class="cart_product">
                                                <div class="label_product">
                                                    <input type="checkbox" class="check_product access_seller {{ $flag_seller_id }}" name="check_product" id="product_cart_{{ $flag_id }}" value="{{ $object_id }}" onclick="is_all_product_checked(`{{ $flag_seller_id }}`,`{{ $flag_id }}`)"><span></span>
                                                </div>
                                                <div class="cp_left">
                                                    <div class="row_clear">
                                                        <div class="cp_img">
                                                            <a href="{{ route('web.product.detail', $item->variant_slug) }}"><img src="{{ asset($item->set_image) }}"></a>
                                                        </div>
                                                        <div class="cp_desc">
                                                            <h4><a href="{{ route('web.product.detail', $item->variant_slug) }}">{{ $item->set_name }}</a></h4>
                                                            <span class="price">Rp{{ $harga }}</span>
                                                            <span class="weight">Berat : {{ $item->variant_weight }} gram</span>

                                                            {{-- @if ($item->flag_soldout == false && $item->flag_campaign_end == false) --}}
                                                                <div class="row_clear notes">
                                                                    <a href="javascript:void(0);" class="notes_btn notes_btn_{{ $flag_id }}" id="{{ $flag_id }}" @if (!is_null($item->note))style="display: none;"@endif>Tulis Catatan</a>
                                                                    <div class="notes_box notes_box_{{ $flag_id }}">
                                                                        <textarea class="autosize notes_value_{{ $flag_id }}" onkeypress="handle(event)">{{$item->note}}</textarea>
                                                                    </div>
                                                                    <div class="notes_field notes_field_{{ $flag_id }}" @if (!is_null($item->note))style="display: block;"@endif>
                                                                        <span class="copy_notes_{{ $flag_id }}">{{ $item->note }}</span>
                                                                        <a href="javascript:void(0);" class="ubah_btn" id="{{ $flag_id }}">Ubah</a>
                                                                    </div>
                                                                </div>
                                                            {{-- @endif --}}
                                                        </div>
                                                    </div>
                                                </div>
                                                {{-- @if ($item->flag_soldout == false && $item->flag_campaign_end == false) --}}
                                                    <div class="cp_right">
                                                        <div class="row_clear bottom">
                                                            <div class="bottom_box">
                                                                @if ($item->wishlist == true)
                                                                    <a>Sudah ada di wishlist</a> | <a href="javascript:void(0);" class="delete_btn" value="{{ $object_id }}" onclick="delete_data_item(`{{ $object_id }}`)"></a>
                                                                @else
                                                                    <a href="javascript:void(0);" class="wl_btn" id="add_to_wishlist" onclick="create_wishlist(`{{ $object_id }}`)">Pindahkan ke wishlist</a> | <a href="javascript:void(0);" class="delete_btn" value="{{ $object_id }}" onclick="delete_data_item(`{{ $object_id }}`)"></a>
                                                                @endif
                                                            </div>
                                                            <div class="quantity_box">
                                                                @php
                                                                    // ATTR MINUS
                                                                    $data_type_minus = "data-type-" . $flag_id . "=minus";
                                                                    $data_field_minus = "data-field-" . $flag_id . "=quant_".$flag_id;

                                                                    // ATTR PLUS
                                                                    $data_type_plus = "data-type-" . $flag_id . "=plus";
                                                                    $data_field_plus = "data-field-" . $flag_id . "=quant_".$flag_id;
                                                                @endphp
                                                                <span class="min_btn decrement_{{ $flag_id }}" onclick="decrement_quantity(`{{ $flag_id }}`,`{{ $item->variant_price }}`, `{{ $object_id }}`)" {{ $data_type_minus }} {{ $data_field_minus }}>-</span>
                                                                <input type="text" class="input-number-{{ $flag_id }} input-number" onkeyup="input_number(`{{ $flag_id }}`,`{{ $item->variant_price }}`, `{{ $object_id }}`)" value="{{ $item->cart_qty }}" id="input-quantity-{{ $flag_id }}" min="1" max="{{ $item->set_qty }}" name="quant_{{ $flag_id }}">
                                                                <span class="max_btn increment_{{ $flag_id }}" onclick="increment_quantity(`{{ $flag_id }}`,`{{ $item->variant_price }}`, `{{ $object_id }}`)" {{ $data_type_plus }} {{ $data_field_plus }}>+</span>
                                                            </div>
                                                        </div>
                                                        <div class="row_clear sub_price">
                                                            <p id="harga_by_total_{{ $flag_id }}">Rp{{ $sub_total }}</p>
                                                        </div>
                                                    </div>
                                                {{-- @endif --}}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="empty_box">
                            <center><b><strong>Belum ada barang di keranjangmu, isi sekarang!</strong></b></center> 
                        </div>
                    @endif

                    @if(!empty($cart_nonactive))
                        <div class="row_clear sc_top">
                            <div class="container width960">
                                {{-- <div class="ca_btn"> --}}
                                    <span>Tidak bisa diproses ({{ $count_nonactive }})</span>
                                {{-- </div> --}}
                                <div class="delete_btn">
                                    <a href="javascript:void(0);" onclick="delete_product(true)">Hapus</a>
                                </div>
                            </div>
                        </div>
                        @foreach ($cart_nonactive as $key => $value)
                            <div class="section_cart empty">
                                <div class="container width1040">
                                    {{-- <div class="label_seller"> --}}
                                        <span class="seller_name">{{ $value['seller_name'] }}</span>
                                    {{-- </div> --}}
                                    @foreach ($value['data'] as $item)
                                        @php
                                            // ENCRIPT VARIANT ID
                                            $object_id = $item->variant_id;
                                            if (env('CRYPTOGRAPHY_MODE', false)) {
                                                $object_id = Helper::generate_token($item->variant_id);
                                            }
                            
                                            // SET NAME
                                            $item->set_name = $item->name . ' - ' . $item->variant_name;
                                            
                                            // SET IMAGE
                                            $item->set_image = $item->image;
                                            if (!is_null($item->variant_image)) {
                                                $item->set_image = $item->variant_image;
                                            }

                                            // SET QTY
                                            $item->set_qty = $item->qty;
                                            if (!$item->global_stock) {
                                                $item->set_qty = $item->variant_qty;
                                            }

                                            // SET PRICE
                                            if (isset($item->variant_price)) {
                                                $calculate_price = $item->variant_price * $item->cart_qty;
                                                $harga = number_format($item->variant_price, 0, ',', '.');
                                                $sub_total = number_format($calculate_price, 0, ',', '.');
                                            }
                                        @endphp
                                        <div class="cart_product_wrapper">
                                            <div class="cart_product">
                                                <div class="cp_left">
                                                    <div class="row_clear">
                                                        <div class="cp_img">
                                                            <a href="{{ route('web.product.detail', $item->variant_slug) }}"><img src="{{ asset($item->set_image) }}" class="product_nonactive" data-id="{{ $object_id }}"></a>
                                                        </div>
                                                        <div class="cp_desc">
                                                            @if ($item->flag_soldout)
                                                                <div class="row_clear">
                                                                    <span class="soldout">Habis</span>
                                                                    <a href="javascript:void(0);" class="delete_btn" value="{{ $object_id }}" onclick="delete_data_item(`{{ $object_id }}`)">Hapus</a>
                                                                </div>
                                                            @elseif ($item->flag_campaign_end)
                                                                <div class="row_clear">
                                                                    <span class="finished">Campaign Berakhir</span>
                                                                    <a href="javascript:void(0);" class="delete_btn" value="{{ $object_id }}" onclick="delete_data_item(`{{ $object_id }}`)">Hapus</a>
                                                                </div>
                                                            @endif
                                                            {{-- <div class="row_clear">
                                                                <span class="soldout">Habis</span>
                                                                <span class="finished">Campaign Berakhir</span>
                                                                <a href="#" class="delete_btn">Hapus</a>
                                                            </div> --}}
                                                            <h4><a href="{{ route('web.product.detail', $item->variant_slug) }}">{{ $item->set_name }}</a></h4>
                                                            <span class="price">Rp{{ $harga }}</span>
                                                            <span class="weight">Berat : {{ $item->variant_weight }} gram</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @endif
                    {{-- SECTION CART - END --}}

                    {{-- SECTION WISHLIST - BEGIN --}}
                    @if (count($wishlists) >= 3)
                        <div class="section_product_list white_bg custom_one custom_keranjang">
                            <div class="row_clear container ck_title">
                                Wujudkan Wishlist Kamu!
                                <a href="{{ route('web.buyer.wishlist') }}">Lihat Semua</a>
                            </div>
                            <div class="row_clear container slider_rp_new">
                                @foreach ($wishlists as $wishlist)
                                    <div class="product_list_box">
                                        <div class="product_list">
                                            @php
                                                // SET IMAGE
                                                $wishlist_image = asset($wishlist->image);
                                                if (!is_null($wishlist->variant_image)) {
                                                    $wishlist_image = asset($wishlist->variant_image);
                                                }

                                                // SET PRICE
                                                if (isset($wishlist->variant_price)) {
                                                    $wishlist_price = 'Rp' . number_format($wishlist->variant_price, 0, ',', '.');
                                                }

                                                // SET NAME
                                                $wishlist_name = $wishlist->name . ' - ' . $wishlist->variant_name;

                                                // ENCRIPT VARIANT ID
                                                $variant_id = $wishlist->variant_id;
                                                if (env('CRYPTOGRAPHY_MODE', false)) {
                                                    $variant_id = Helper::generate_token($wishlist->variant_id);
                                                }
                                            @endphp
                                            <div class="pl_img"><a href="{{ route('web.product.detail', $wishlist->slug) }}"><img src="{{ $wishlist_image }}"></a></div>
                                            <div class="pl_info">
                                                <div class="pl_name"><h4><a href="{{ route('web.product.detail', $wishlist->slug) }}">{{ $wishlist_name }}</a></h4></div>
                                                <div class="pl_owner">by: <a href="{{ route('web.product.detail', $wishlist->slug) }}">{{ $wishlist->seller_name }}</a></div>
                                                <div class="pl_price">{{ $wishlist_price }}</div>
                                                <a href="javascript:void(0);" class="cart_btn" id="{{ 'add_to_cart-' . $variant_id }}" onclick="add_cart('{{ $variant_id }}', this)">+ Keranjang</a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    {{-- SECTION WISHLIST - END --}}

                    {{-- SECTION MENARIK PERTHATIAN - BEGIN --}}
                    <div class="section_product_list white_bg custom_one custom_keranjang">
                        <div class="row_clear container ck_title">
                            Yang Menarik Perhatian!
                        </div>
                        <div class="row_clear container slider_rp_new">
                            @foreach ($interisting_products as $interisting_product)
                                <div class="product_list_box">
                                    <div class="product_list">
                                        @php
                                            // SET IMAGE
                                            $interisting_product_image = asset($interisting_product->image);
                                            if (!is_null($interisting_product->variant_image)) {
                                                $interisting_product_image = asset($interisting_product->variant_image);
                                            }

                                            // SET PRICE
                                            if (isset($interisting_product->variant_price)) {
                                                $interisting_product_price = 'Rp' . number_format($interisting_product->variant_price, 0, ',', '.');
                                            }

                                            // SET NAME
                                            $interisting_product_name = $interisting_product->name . ' - ' . $interisting_product->variant_name;

                                            // ENCRIPT VARIANT ID
                                            $variant_id = $interisting_product->variant_id;
                                            if (env('CRYPTOGRAPHY_MODE', false)) {
                                                $variant_id = Helper::generate_token($interisting_product->variant_id);
                                            }
                                        @endphp
                                        <div class="pl_img"><a href="{{ route('web.product.detail', $interisting_product->slug) }}"><img src="{{ $interisting_product_image }}"></a></div>
                                        <div class="pl_info">
                                            <div class="pl_name"><h4><a href="{{ route('web.product.detail', $interisting_product->slug) }}">{{ $interisting_product_name }}</a></h4></div>
                                            <div class="pl_owner">by: <a href="{{ route('web.product.detail', $interisting_product->slug) }}">{{ $interisting_product->seller_name }}</a></div>
                                            <div class="pl_price">{{ $interisting_product_price }}</div>
                                            <a href="javascript:void(0);" class="cart_btn" id="{{ 'add_to_cart-' . $variant_id }}" onclick="add_cart('{{ $variant_id }}', this)">+ Keranjang</a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    {{-- SECTION MENARIK PERTHATIAN - BEGIN --}}
                </div>

                <div class="scw_right">
                    <div class="pos_sticky">
                        <div class="v_tnc_box">
                            <span>Info Penting:</span>
                            <ul>
                                <li>Dengan klik “Lanjut”, Anda setuju dengan syarat & ketentuan yang berlaku dan bahwa pembayaran tidak bisa dibatalkan.</li>
                                <li>Produk masih berbentuk prototype. Dana Anda akan dikembalikan jika produk tidak selesai diproduksi dalam batas waktu yang ditentukan.</li>
                                <li>Cek <a href="{{ route('web.faq') }}" target="_blank">FAQ</a> untuk syarat & ketentuan, serta info lainnya.</li>
                            </ul>
                        </div>
                        <div class="button_wrapper">
                            <button type="button" class="red_btn" id="btn_lanjut" disabled="disabled">Lanjut</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('footer-script')
    <script>
		function autosize(){
			var text = $('.autosize');

			text.each(function(){
				$(this).attr('rows',1);
				resize($(this));
			});

			text.on('input', function(){
				resize($(this));
			});
			
			function resize ($text) {
				$text.css('height', 'auto');
				$text.css('min-height', '40px');
				$text.css('height', $text[0].scrollHeight+'px');
			}
		}
		
        function getval(){
			var yt = $('.autosize').val();
			$('.notes_field span').text(yt);
			$('.notes_field').hide();
		}
		
        $(document).ready(function() {
            autosize();

			$('.notes_btn, .ubah_btn').click(function(e) {
                // console.log(e)
				$('#selected_product').val(e.target.id);
				$('.notes_btn_' + e.target.id).hide();
				$('.notes_box_' + e.target.id).show();
				$('.notes_field_' + e.target.id).hide();
				$('.notes_box_' + e.target.id + ' textarea').focus();
				e.stopPropagation();
			});

            $(document).click(function(e) {
                update_item_note();
            });
			
            $('.notes_box').click(function(e) {
				e.stopPropagation();
			})
			
            $('.v_tnc_box span').click(function() {
				if ($(window).width() < 960) {
					if ($('.v_tnc_box ul').css('display') == "none") {
						$('.v_tnc_box ul').slideDown();
                        $('.v_tnc_box').addClass('active');
					} else {
						$('.v_tnc_box ul').slideUp();
                        $('.v_tnc_box').removeClass('active');
					}
				}
			})
		})

        function handle(e){
            if(e.keyCode === 13){
                e.preventDefault(); // Ensure it is only this code that runs

                // alert("Enter was pressed was presses");
                $(document).click();
            }
        }

        function update_item_note() {
            // GET ID
            var selected_product = $('#selected_product').val();
            if ($('.notes_box_' + selected_product).css('display') == "block") {
                $('.notes_box_' + selected_product).hide();
                $('.notes_field_' + selected_product).show();
                $('.copy_notes_' + selected_product).html($('.notes_value_' + selected_product).val());

                var CSRF_TOKEN  = $('meta[name="csrf-token"]').attr('content');
                var id          = $('#product_cart_' + selected_product).val();

                $.ajax({
                    type: "POST",
                    url: "{{ route('web.buyer.update_cart') }}",
                    data: {
                        _token : CSRF_TOKEN,
                        id : id,
                        notes : $('.notes_value_' + selected_product).val()
                    },
                    dataType: "json",
                    beforeSend: function() {
                        // LOADING HERE
                    }
                })
                .done(function(response) {
                    if (typeof response != 'undefined') {
                        if (response.status == 'success') {
                            console.log('Berhasil mengubah catatan');
                        } else {
                            show_popup_alert("error", "Error!", response.message);
                        }
                    } else {
                        show_popup_alert("error", "Error!", 'Maaf, respon server tidak dikenali. Mohon coba lagi atau refresh halaman ini.');
                    }
                })
                .fail(function() {
                    show_popup_alert("error", "Error!", 'Maaf, gagal menghubungi server. Mohon coba lagi atau refresh halaman ini.');
                })
                .always(function() {});
            }
        }

        function delete_data_item(id) {
            var CSRF_TOKEN  = $('meta[name="csrf-token"]').attr('content');
            var params      = id + ',';

            $.ajax({
                type: "POST",
                url: "{{ route('web.buyer.delete_cart_ajax') }}",
                data: {
                    _token : CSRF_TOKEN,
                    params : params,
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

                        setTimeout(function() {
                            location.reload();
                            return true;
                        }, 2300);
                    } else {
                        show_popup_alert("error", "Error!", response.message);
                    }
                } else {
                    show_popup_alert("error", "Error!", 'Maaf, respon server tidak dikenali. Mohon coba lagi atau refresh halaman ini.');
                }
            })
            .fail(function() {
                show_popup_alert("error", "Error!", 'Maaf, gagal menghubungi server. Mohon coba lagi atau refresh halaman ini.');
            })
            .always(function() {});
        }

        $("#btn_lanjut").click(function(){
            // DISABLE BUTTON FOR PREVENT MULTI REQUEST
            $(this).attr("disabled", true);
            $(this).html("LOADING...");
            show_loader();

            var CSRF_TOKEN  = $('meta[name="csrf-token"]').attr('content');

            // GET CHECKED VARIANT
            var product_item_variant_id=[];
            $('#cart_product input[type="checkbox"]').each(function () {
                if (this.checked) {
                    product_item_variant_id[product_item_variant_id.length] = $(this).val();
                }
            });

            if (product_item_variant_id.length == 0) {
                show_popup_alert("error", "Error!", 'Anda belum memilih barang untuk di checkout');

                $('#delete_btn').hide();
                $(this).attr("disabled", true);
                $(this).html("LANJUT");
            }

            $.ajax({
                type: "POST",
                url: "{{ route('web.cart.checkout') }}",
                data: {
                    _token : CSRF_TOKEN,
                    product_item_variant_id : product_item_variant_id,
                },
                dataType: "json",
                beforeSend: function() {
                    
                }
            })
            .done(function(response) {
                if (typeof response != 'undefined') {
                    if (response.status == 'success') {
                        var checkout_url = "{{ route('web.cart.checkout') }}"
                        window.location.href = checkout_url;
                    } else {
                        show_popup_alert("error", "Error!", response.message);
                    }
                } else {
                    show_popup_alert("error", "Error!", 'Maaf, respon server tidak dikenali. Mohon coba lagi atau refresh halaman ini.');
                }
            })
            .fail(function() {
                show_popup_alert("error", "Error!", 'Maaf, gagal menghubungi server. Mohon coba lagi atau refresh halaman ini.');
            })
            .always(function() {
                $(this).attr("disabled", false);
                $(this).html("LANJUT");
            });
        });
    </script>
@endsection