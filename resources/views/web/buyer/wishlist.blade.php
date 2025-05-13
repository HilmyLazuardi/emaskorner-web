@extends('_template_web.master')

@section('title', 'Wishlist')

@php
    use App\Libraries\Helper;
    $opened_page = 1;
@endphp

@section('css-plugins')
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/jquery-ui.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/jquery-ui.theme.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/select2.css') }}">
@endsection

@section('script-plugins')
    <script type="text/javascript" src="{{ asset('web/js/jquery-ui.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('web/js/select2.full.min.js') }}"></script>
    <script>
        var opened_page     = "{{ $opened_page }}";

        function open_page(page) {
            set_param_url('page', page);
            opened_page = page;
            refresh_data();
        }

        function refresh_data(filter_items = '') {
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

            $.ajax({
                type: "POST",
                url: "{{ route('web.buyer.wishlist_ajax') }}",
                data: {
                    _token : CSRF_TOKEN,
                    page : opened_page,
                    sort: filter_items
                },
                dataType: "json",
                beforeSend: function() {
                    $(".drop_wishlist").html();
                    $(".drop_pagination").html('');
                }
            })
            .done(function(response) {
                // console.log(response)
                if (typeof response != 'undefined') {
                    if (response.status == 'success') {
                        $(".drop_wishlist").html(response.html);
                        $(".drop_pagination").html(response.pagination);
                    } else {
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

        function delete_data() {
            var CSRF_TOKEN  = $('meta[name="csrf-token"]').attr('content');
            var params      = $('#drop_deleted_wishlist').val()

            $.ajax({
                type: "POST",
                url: "{{ route('web.buyer.delete_wishlist_ajax') }}",
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
                        alert(response.message);
                        location.reload();
                        return false;
                    } else {
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

        function check_item(id) {
            var checked_items = $('#drop_deleted_wishlist').val();

            if ($('#' + id).is(':checked')) {
                var target = $('#' + id).val() + ',';
                if (checked_items.includes(target)) {
                    // ITEM ALREADY EXIST
                } else {
                    // PUSH DATA TO ARRAY
                    checked_items += target;
                    $('#drop_deleted_wishlist').val(checked_items)
                }
            } else {
                var explode_checked_items   = checked_items.split(",");
                var target                  = $('#' + id).val() + ',';
                var new_string              = '';

                for (let index = 0; index < explode_checked_items.length; index++) {
                    var target_comparison = explode_checked_items[index] + ',';
                    if (explode_checked_items[index] != '' && target_comparison != target) {
                        new_string += explode_checked_items[index] + ',';
                    } else {
                        // DONT PUSH DATA TO VARIABLE
                    }
                }

                $('#drop_deleted_wishlist').val(new_string)
            }
        }

        function delete_wishlist() {
            var result = confirm("Are you sure want to delete this item from wishlist?");
            if (result == true) {
                delete_data();
            } else {
                event.preventDefault();
                return false;
            }
        }

        $(document).ready(function() {
            refresh_data();

            $('#sort').change(function() {
                refresh_data($('#sort').val());
            });

            $('#choose_btn').change(function() {
                if ($(this).is(':checked')) {
                    var drop_data = '';
                    $('.product_list_box input[type="checkbox"]').each(function() {
                        $('input[type="checkbox"]').prop('checked', true);
                        var id_data = $(this).attr('id');
                        
                        // COLECT DATA
                        drop_data += $('#' + id_data).val() + ',';

                        // EMPTY DATA
                        $('#drop_deleted_wishlist').val('');

                        // PUT DATA
                        $('#drop_deleted_wishlist').val(drop_data);
                    });
                } else {
                    $('input[type="checkbox"]').prop('checked', false);
                    // EMPTY DATA
                    $('#drop_deleted_wishlist').val('');
                }
            })
        });

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
@endsection

@section('content')
    @include('_template_web.header')
    @include('_template_web.alert_popup')

    <section class="white_bg no_categories">
        <div class="section_profile wishlist">
            <div class="profile_menu">
                <h2>Wishlist</h2>
                <span class="info_wl">{{ $count_wishlist }} Barang</span>
            </div>
        </div>

        <div class="row_clear sort_wrapper">
            <div class="container width755">
                <div class="sort_box">
                    Urutkan
                    <select class="select2" id="sort">
                        <option value="newest">Terbaru Disimpan</option>
                        <option value="oldest">Terlama Disimpan</option>
                        <option value="highest_price">Harga Tertinggi</option>
                        <option value="lowest_price">Harga Terendah</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="row_clear">
            <div class="container width755">
                <div class="ca_btn">
                    <input type="checkbox" class="choose_all" id="choose_btn"><span>Pilih Semua</span>
                </div>

                <div class="delete_btn">
                    <a href="#" onclick="delete_wishlist();">Hapus</a>
                </div>
            </div>
        </div>

        <div class="section_product_list white_bg custom_one">
            <div class="row_clear container pl_wishlist">
                <input type="hidden" id="drop_deleted_wishlist">
                <div class="drop_wishlist"></div>
                <div class="drop_pagination"></div>
            </div>
        </div>
    </section>
@endsection