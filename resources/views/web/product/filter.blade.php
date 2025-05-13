@extends('_template_web.master')

@php
    use App\Libraries\Helper;

    $opened_tab = 'product';

    //$opened_page = 1;
    //if(isset($_GET['page'])){
    //    $opened_page = (int) $_GET['page'];
    //}
@endphp

@section('css-plugins')
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/slick.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/slick-theme.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/jquery.fancybox.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/content_element.css') }}">

    <style>
        .click-able {
            cursor: pointer;
        }
        .click-ableselected {
            cursor: default;
        }
    </style>
@endsection

@section('script-plugins')
    <script type="text/javascript" src="{{ asset('web/js/select2.full.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('web/js/slick.js') }}"></script>
    <script type="text/javascript" src="{{ asset('web/js/jquery.fancybox.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/web/js/canvasjs.min.js') }}"></script>
    
    <script>
        var opened_tab = "{{ $opened_tab }}";
        var opened_page = "{{ $page }}";
        var opened_perpage = "{{ $perpage }}";
        var keyword = "{{ $keyword }}";
        var orderby = "{{ $orderby }}";
        var price_min = "{{ $price_min }}";
        var price_max = "{{ $price_max }}";

        function open_page(page) {
            set_param_url('page', page);
            opened_page = page;
            refresh_data();
            // scroll_top();
        }

        function open_tab(tab_name) {
            if (opened_tab != tab_name) {
                // set_param_url('tab', tab_name);
                opened_tab = tab_name;
                // reset to first page
                open_page(1);
            }
        }

        function refresh_data() {
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                type: "POST",
                url: "{{ route('web.product.search_ajax') }}",
                data: {
                    _token : CSRF_TOKEN,
                    page : opened_page,
                    perpage : opened_perpage,
                    keyword : keyword,
                    orderby : orderby,
                    price_min : price_min,
                    price_max : price_max,
                    slug : "{{ $category }}"
                },
                dataType: "json",
                beforeSend: function() {
                    $("#list-"+opened_tab).html();
                    $("#pagination-"+opened_tab).html('');
                    $("#page-"+opened_tab).hide();
                }
            })
            .done(function(response) {
                if (typeof response != 'undefined') {
                    if(response.status == 'success'){
                        $("#list-"+opened_tab).html(response.html);
                        $("#pagination-"+opened_tab).html(response.pagination);
                        if (response.pagination != '') {
                            $("#page-"+opened_tab).show();
                        }

                        if(response.total_product > 0) {
                            response.product.forEach(generate_canvasjs)
                        }

                        
                    }else{
                        alert('ERROR: '+response.message);
                    }
                } else {
                    alert('Maaf, respon server tidak dikenali. Mohon coba lagi atau refresh halaman ini.');
                }
            })
            .fail(function() {
                alert('Maaf, gagal menghubungi server. Mohon coba lagi atau refresh halaman ini.');
            })
            .always(function() {
                // console.log('refresh data: '+opened_tab);
            });
        }

        $( document ).ready(function() {
            @if(isset($product_filters[0]))
                refresh_data();
            @endif
            $('.reset_btn').click(function(){
                $('.filter_box input[type="radio"]').prop('checked',false);
                $('.filter_box input[type="text"]').val('');
            })
            $('.input-number').focusin(function(){
                $(this).data('oldValue', $(this).val());
            });

            $(".input-number").keydown(function (e) {
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
        });

        function generate_canvasjs(item) {

            // format persentase lama
            // var terjual     = (item.order_qty / item.qty) * 100;
            if (item.global_stock) {
                var terjual     = ((item.qty_sold + item.qty_booked) / (item.qty_sold + item.qty_booked + item.qty)) * 100;
                var tersedia    = 100 - terjual;
            } else {
                var qty = 0;
                if (parseInt(item.variant_qty) > 0) {
                    qty = parseInt(item.variant_qty);
                }

                var qty_booked = 0;
                if (parseInt(item.variant_qty_booked) > 0) {
                    qty_booked = parseInt(item.variant_qty_booked);
                }

                var qty_sold = 0;
                if (parseInt(item.variant_qty_sold) > 0) {
                    qty_sold = parseInt(item.variant_qty_sold);
                }

                var terjual     = ((qty_sold + qty_booked) / (qty_sold + qty_booked + qty)) * 100;
                var tersedia    = 100 - terjual;
            }

            var chart = new CanvasJS.Chart("chartContainer"+item.id, {
                animationEnabled: true,
                backgroundColor: "transparent",
                toolTip:{
                            enabled: false   //enable here
                        },
                        axisY:{
                            crosshair: {
                                enabled: false,
                                opacity: 1
                            }
                        },
                        data: [{
                            type: "doughnut",
                            startAngle: 0,
                            radius: "100%", 
                            innerRadius: '80%',
                            dataPoints: [
                            { y: terjual, color: "#D3A381"},
                            { y: tersedia, color: "#0C6663" }
                            ]
                        }]
                    });

            chart.render();

        }
    </script>
@endsection

@section('content')
    @include('_template_web.header_with_categories')

    <section>
        <div class="section_filter">
            <div class="row_clear container">
                <div class="sort_btn grid_btn"></div>
                <div class="filter_btn">Filter</div>
            </div>
        </div>
        <div class="section_product_list search_result">
            @if (!is_null($orderby) || (!is_null($price_min) || !is_null($price_max)))
                @php
                    $filter = '';
                    if(!is_null($orderby) && is_null($price_min) && is_null($price_max)) {
                        if($orderby == 'highestprice') {
                            $filter = 'Harga Tertinggi';
                        } else if($orderby == 'lowestprice') {
                            $filter = 'Harga Terendah';
                        } else if($orderby == 'newestproduct') {
                            $filter = 'Produk Terbaru';
                        } else if($orderby == 'oldestproduct') {
                            $filter = 'Produk Terlama';
                        } else if($orderby == 'bestseller') {
                            $filter = 'Produk Terlaris';
                        }

                        if(!is_null($price_min)) {
                            $filter .= ' Harga Min Rp'.number_format($price_min,0,',','.');
                        }

                        if(!is_null($price_max)) {
                            $filter .= ' Harga Maks Rp'.number_format($price_max,0,',','.');
                        }
                    } else if (is_null($orderby) && !is_null($price_min) && !is_null($price_max)) {
                        if($orderby == 'highestprice') {
                            $filter = 'Harga Tertinggi';
                        } else if($orderby == 'lowestprice') {
                            $filter = 'Harga Terendah';
                        } else if($orderby == 'newestproduct') {
                            $filter = 'Produk Terbaru';
                        } else if($orderby == 'oldestproduct') {
                            $filter = 'Produk Terlama';
                        } else if($orderby == 'bestseller') {
                            $filter = 'Produk Terlaris';
                        }

                        if(!is_null($price_min)) {
                            $filter .= ' Harga Min Rp'.number_format($price_min,0,',','.');
                        }

                        if(!is_null($price_max)) {
                            $filter .= ' Harga Maks Rp'.number_format($price_max,0,',','.');
                        }
                    } else {
                        if($orderby == 'highestprice') {
                            $filter = 'Harga Tertinggi';
                        } else if($orderby == 'lowestprice') {
                            $filter = 'Harga Terendah';
                        } else if($orderby == 'newestproduct') {
                            $filter = 'Produk Terbaru';
                        } else if($orderby == 'oldestproduct') {
                            $filter = 'Produk Terlama';
                        } else if($orderby == 'bestseller') {
                            $filter = 'Produk Terlaris';
                        }

                        if(!is_null($price_min)) {
                            $filter .= ' Harga Min Rp'.number_format($price_min,0,',','.');
                        }

                        if(!is_null($price_max)) {
                            $filter .= ' Harga Maks Rp'.number_format($price_max,0,',','.');
                        }
                    }
                @endphp
                <div class="row_clear container search_text">
                    Hasil Filter untuk {{ $filter }}
                </div>
            @endif

            <div class="row_clear container">
                <div id="list-product">

                </div>
                <div class="overlay"></div>
            </div>
            <span id="page-product"></span>
            <div class="paging_box" id="pagination-product">

            </div>
        </div>
    </section>

    @include('_template_web.footer')

    <div class="popup_filter">
        <div class="popup_box">
            <div class="filter_top">
                <span>Filter</span>
                @if(!empty($category))
                    <a href="javascript:void(0);" class="reset_btn">Reset</a>
                @endif
                @if(!empty($keyword))
                    <form action="{{ route('web.product.search') }}">
                        <input type="hidden" name="page" value="{{ $page }}">
                        <input type="hidden" name="keyword" value="{{ $keyword }}">
                        <button class="reset_btn" type="submit">Reset</button>
                    </form>
                @endif
            </div>
            <form action="{{ route('web.product.filter') }}">
                <input type="hidden" name="page" value="{{ $page }}">
                <input type="hidden" name="slug" value="{{ $category }}">
                <input type="hidden" name="keyword" value="{{ $keyword }}">
                <div class="filter_box">
                    <h5>Urutkan</h5>
                    <ul>
                        <li><input type="radio" name="orderby" value="highestprice" {{ $orderby == 'highestprice' ? 'checked' : '' }}><span>Harga Tertinggi</span></li>
                        <li><input type="radio" name="orderby" value="lowestprice" {{ $orderby == 'lowestprice' ? 'checked' : '' }}><span>Harga Terendah</span></li>
                        <li><input type="radio" name="orderby" value="newestproduct" {{ $orderby == 'newestproduct' ? 'checked' : '' }}><span>Produk Terbaru</span></li>
                        <li><input type="radio" name="orderby" value="oldestproduct" {{ $orderby == 'oldestproduct' ? 'checked' : '' }}><span>Produk Terlama</span></li>
                        <li><input type="radio" name="orderby" value="bestseller" {{ $orderby == 'bestseller' ? 'checked' : '' }}><span>Produk Terlaris</span></li>
                    </ul>
                </div>
                <div class="filter_box">
                    <h5>Rentang Harga</h5>
                    @php
                        $minimal = '';
                        if(!is_null($price_min)) {
                            $minimal = $price_min;
                        } 

                        $maksimal = '';
                        if(!is_null($price_max)) {
                            $maksimal = $price_max;
                        }
                    @endphp
                    <div class="form_filter">
                        <input type="text" name="price_min" class="form-control input-number" min="0" value="{{ $minimal }}" placeholder="Minimum">
                        <span></span>
                        <input type="text" name="price_max" class="form-control input-number" min="0" value="{{ $maksimal }}" placeholder="Maksimum">
                    </div>
                </div>
                {{-- <div class="filter_box">
                    <h5>Kategori</h5>
                    <ul>
                        <li><input type="checkbox" name="kategori_1"><span>Art &amp; Craft</span></li>
                        <li><input type="checkbox" name="kategori_2"><span>Fashion &amp; Accessories</span></li>
                        <li><input type="checkbox" name="kategori_3"><span>Gadget &amp; Tech</span></li>
                        <li><input type="checkbox" name="kategori_4"><span>Comodities</span></li>
                    </ul>
                </div> --}}
                <div class="button_wrapper">
                    <button class="red_btn" type="submit">Terapkan</button>
                </div>
            </form>
            <div class="close_btn"></div>
        </div>
    </div>
@endsection