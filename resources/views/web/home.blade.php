@extends('_template_web.master')

@php
    use App\Libraries\Helper;

    $opened_tab = 'product';

    $opened_page = 1;
    if(isset($_GET['page'])){
        $opened_page = (int) $_GET['page'];
    }
@endphp

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
        var opened_tab = "{{ $opened_tab }}";
        var opened_page = "{{ $opened_page }}";

        function open_page(page) {
            set_param_url('page', page);
            opened_page = page;
            refresh_data();
            // scroll_top();
        }

        function refresh_data() {
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                type: "POST",
                url: "{{ route('web.home.products_new') }}",
                data: {
                    _token : CSRF_TOKEN,
                    page : opened_page,
                },
                dataType: "json",
                beforeSend: function() {
                    $("#list-"+opened_tab).html();
                    $("#pagination-"+opened_tab).html('');
                    $("#page-"+opened_tab).hide();
                }
            })
            .done(function(response) {
                // console.log(response)
                if (typeof response != 'undefined') {
                    if(response.status == 'success'){
                        $("#list-"+opened_tab).html(response.html);
                        $("#pagination-"+opened_tab).html(response.pagination);
                        if (response.pagination != '') {
                            $("#page-"+opened_tab).show();
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
            refresh_data();
        });
    </script>
@endsection

@section('content')
    @include('_template_web.header_with_categories')
    @include('_template_web.alert_popup')

    <section>
        <div class="section_banner">
            <div class="slider_banner_home">
                @if (isset($banners[0]))
                    @foreach ($banners as $item)
                        @if ($item->link_type != 'none')
                            @php
                                $link_target = '_self';
                                if ($item->link_target == 'new window') {
                                    $link_target = '_blank';
                                }

                                if ($item->link_type == 'external') {
                                    $link = $item->link_external;
                                } else {
                                    $link = url($item->link_internal);
                                }
                            @endphp
                            <a href="{{ $link }}" target="{{ $link_target }}">
                        @endif
                        <div class="bs_img"><img class="desktop" src="{{ $item->image }}"><img class="mobile" src="{{ $item->image_mobile }}"></div>
                        @if ($item->link_type != 'none')
                            </a>
                        @endif
                    @endforeach
                @endif
            </div>
        </div>

        {{-- <div class="section_about_home">
            <div class="container"> --}}
                {{--<h2>Tentang {!! $global_config->app_name !!}</h2>--}}
                {{-- <p>{!! $company_info->description !!}</p> --}}
                {{-- <p><span>LokalKorner</span>, tempatnya kamu, para creative entrepreneur yang #KayaIdeKreatif untuk menciptakan produk yang unik dan inovatif. Semua prototype yang ditampilkan sudah melewati proses kurasi yang ketat, limited edition, dan dijual secara eksklusif di LokalKorner. Tidak hanya mempertemukan produk inovatif dengan konsumen, LokalKorner juga memudahkan penjual memantau potensi produk dan mendapatkan apresiasi dari calon konsumen yang melakukan pemesanan yang dapat membantu produksi prototype mereka. Bersama-sama mari kita dukung produk lokal dan wujudkan visi agar konsumen Indonesia dapat memiliki dan menggunakan produk lokal berkualitas sebanyak 80% dari keseluruhan produk-produk dalam keseharian mereka.</p> --}}
            {{-- </div>
        </div> --}}

        @if (isset($product_featured[0]))
            @foreach ($product_featured as $key => $product)
                @if ($key == 0)
                    @php
                        // Jika global stock, ambil dari product.qty
                        if ($product->global_stock) {
                            // Hitung persentase
                            $persentase = (($product->qty_sold + $product->qty_booked) / ($product->qty_sold + $product->qty_booked + $product->qty)) * 100;

                            // Ribbon sold out dan campaign end
                            if ($product->qty < 1) {
                                $product->flag_soldout         = true;
                                $product->flag_campaign_end    = false;
                            } else if ($product->qty > 0 && (date('Y-m-d H:i:s') > $product->campaign_end)) {
                                $product->flag_soldout         = false;
                                $product->flag_campaign_end    = true;
                            } else {
                                $product->flag_soldout         = false;
                                $product->flag_campaign_end    = false;
                            }
                        } else { // Jika bukan global stock, ambil total stock seluruh variant
                            // 0 sebagai default value, jadi tidak menghitung data null
                            $variant_qty = 0;
                            if ($item->variant_qty > 0) {
                                $variant_qty = $item->variant_qty;
                            }
                            $variant_qty_booked = 0;
                            if ($item->variant_qty_booked > 0) {
                                $variant_qty_booked = $item->variant_qty_booked;
                            }
                            $variant_qty_sold = 0;
                            if ($item->variant_qty_sold > 0) {
                                $variant_qty_sold = $item->variant_qty_sold;
                            }
                            
                            // Hitung persentase
                            $persentase = (($product->variant_qty_sold + $product->variant_qty_booked) / ($product->variant_qty_sold + $product->variant_qty_booked + $product->variant_qty)) * 100;

                            // Ribbon sold out dan campaign end
                            if ($product->variant_qty < 1) {
                                $product->flag_soldout         = true;
                                $product->flag_campaign_end    = false;
                            } else if ($product->variant_qty > 0 && (date('Y-m-d H:i:s') > $product->campaign_end)) {
                                $product->flag_soldout         = false;
                                $product->flag_campaign_end    = true;
                            } else {
                                $product->flag_soldout         = false;
                                $product->flag_campaign_end    = false;
                            }
                        }
                    @endphp
                    
                    <div class="section_about_home section_product white_bg">
                        <div class="container">
                            <h2>Yang Mencuri Perhatian</h2>
                            <div class="row_flex">
                                <div class="pp_img_box">
                                    <div class="pp_img">
                                        @if ($product->flag_soldout)
                                            <span class="flag_soldout"></span>
                                        @elseif ($product->flag_campaign_end)
                                            <span class="flag_ended"></span>
                                        @endif
                                        <a href="{{ route('web.product.detail', $product->slug) }}"><img src="{{ asset($product->image) }}"></a>
                                    </div>
                                </div>
                                <div class="pp_info_box">
                                    <h3><a href="{{ route('web.product.detail', $product->slug) }}">{{ $product->name }}</a></h3>
                                    <span>by: {{ $product->seller_name }}</span>
                                    <span class="pp_price">Rp{{ number_format($product->price, 0, ',', '.') }}</span>
                                    {{-- <p>{{ substr($product->summary, 0, 500) .((strlen($product->summary) > 500) ? '...' : '') }}</p> --}}
                                    <div class="pp_desc">
                                        <p>{{ Helper::read_more($product->summary, 150) }}</p>
                                    </div>
                                    <div class="pl_stock_view">
                                        <div class="sv_track"><span class="sv_bar" style="width:  {{ ceil($persentase) }}%;"></span></div>
                                        <div class="sv_info">{{ ceil($persentase) }}% Dipesan</div>
                                    </div>
                                    <a href="{{ route('web.product.detail', $product->slug) }}" class="detail_btn">Cek Produknya</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach

            <!--<div class="section_product_list">
                <div class="row_clear container">
                    @foreach ($product_featured as $key => $products)
                        @if ($key != 0)
                            @php
                                // PROSES PERSENTASE
                                $persentase = 0;

                                // Jika global stock, ambil dari product.qty
                                if ($item->global_stock) {
                                    $persentase = ($products->order_qty / $products->qty) * 100;
                                } else { // Jika bukan global stock, ambil total stock seluruh variant
                                    $persentase = ($products->order_qty / $products->variant_qty) * 100;
                                }
                            @endphp
                            <div class="product_list_box">
                                <div class="product_list">
                                    <div class="pl_img"><a href="{{ route('web.product.detail', $products->slug) }}"><img src="{{ asset($products->image) }}"></a></div>
                                    <div class="pl_info">
                                        <div class="pl_name"><h4><a href="{{ route('web.product.detail', $products->slug) }}">{{ $products->name }}</a></h4></div>
                                        <div class="pl_owner">by: <a href="{{ route('web.product.detail', $products->slug) }}">{{ $products->seller_name }}</a></div>
                                        <div class="pl_price">Rp{{ number_format($products->price, 0, ',', '.') }}</div>
                                        <div class="pl_stock_view">
                                            <div class="sv_track"><span class="sv_bar" style="width: {{ ceil($persentase) }}%;"></span></div>
                                            <div class="sv_info">{{ ceil($persentase) }}% Dipesan</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- <div class="product_list_box">
                                <div class="product_list">
                                    <div class="pl_img"><a href="#"><img src="images/sepatu.png"></a></div>
                                    <div class="pl_info">
                                        <div class="pl_name"><h4><a href="#">Sepatu Pria Bahan Kulit Sapi Muda</a></h4></div>
                                        <div class="pl_owner">by: <a href="#">Teuku Muda Raji</a></div>
                                        <div class="pl_price">Rp400.000</div>
                                        <div class="pl_stock_view">
                                            <div class="sv_track"><span class="sv_bar" style="width: 50%;"></span></div>
                                            <div class="sv_info">50% Dipesan</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="product_list_box">
                                <div class="product_list">
                                    <div class="pl_img"><a href="#"><img src="images/strap.png"></a></div>
                                    <div class="pl_info">
                                        <div class="pl_name"><h4><a href="#">Strap Jam Tangan Dengan Magnet</a></h4></div>
                                        <div class="pl_owner">by: <a href="#">Rico Setiawan</a></div>
                                        <div class="pl_price">Rp20.000</div>
                                        <div class="pl_stock_view">
                                            <div class="sv_track"><span class="sv_bar" style="width: 25%;"></span></div>
                                            <div class="sv_info">25% Dipesan</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="product_list_box">
                                <div class="product_list">
                                    <div class="pl_img"><a href="#"><img src="images/aroma.png"></a></div>
                                    <div class="pl_info">
                                        <div class="pl_name"><h4><a href="#">Aroma Akar Wangi Untuk Terapi Tidur</a></h4></div>
                                        <div class="pl_owner">by: <a href="#">Rumah Akar Wangi</a></div>
                                        <div class="pl_price">Rp90.000</div>
                                        <div class="pl_stock_view">
                                            <div class="sv_track"><span class="sv_bar" style="width: 0%;"></span></div>
                                            <div class="sv_info">0% Dipesan</div>
                                        </div>
                                    </div>
                                </div>
                            </div> --}}
                        @endif
                    @endforeach
                </div>
            </div>-->
        @endif

        <div class="section_product_list green_bg custom_one padding_slider">
            <div class="row_clear container">
                <h2>Yang Terlaris</h2>
            </div>
            <div class="row_clear container slider_rp">
                @foreach ($product_best_seller as $best_seller)
                    @if ((int) $best_seller->percentage > 0)
                        @php
                            // Jika global stock, ambil dari product.qty
                            if ($best_seller->global_stock) {
                                if ($best_seller->qty < 1) {
                                    $best_seller->flag_soldout         = true;
                                    $best_seller->flag_campaign_end    = false;
                                } elseif ($best_seller->qty > 0 && date('Y-m-d H:i:s') > $best_seller->campaign_end) {
                                    $best_seller->flag_soldout         = false;
                                    $best_seller->flag_campaign_end    = true;
                                } else {
                                    $best_seller->flag_soldout         = false;
                                    $best_seller->flag_campaign_end    = false;
                                }
                            } else { // Jika bukan global stock, ambil total stock seluruh variant
                                if ($best_seller->variant_qty < 1) {
                                    $best_seller->flag_soldout         = true;
                                    $best_seller->flag_campaign_end    = false;
                                } elseif ($best_seller->variant_qty > 0 && date('Y-m-d H:i:s') > $best_seller->campaign_end) {
                                    $best_seller->flag_soldout         = false;
                                    $best_seller->flag_campaign_end    = true;
                                } else {
                                    $best_seller->flag_soldout         = false;
                                    $best_seller->flag_campaign_end    = false;
                                }
                            }
                        @endphp

                        <div class="product_list_box">
                            <div class="product_list">
                                <div class="pl_img">
                                    @if ($best_seller->flag_soldout)
                                        <span class="flag_soldout"></span>
                                    @elseif ($best_seller->flag_campaign_end)
                                        <span class="flag_ended"></span>
                                    @endif
                                    <a href="{{ route('web.product.detail', $best_seller->slug) }}"><img src="{{ asset($best_seller->image) }}"></a>
                                </div>
                                <div class="pl_info">
                                    <div class="pl_name"><h4><a href="{{ route('web.product.detail', $best_seller->slug) }}">{{ $best_seller->name }}</a></h4></div>
                                    <div class="pl_owner">by: <a href="#">{{ $best_seller->seller_name }}</a></div>
                                    <div class="pl_price">Rp{{ number_format($best_seller->price, 0, ',', '.') }}</div>
                                    <div class="pl_stock_view">
                                        <div class="sv_track"><span class="sv_bar" style="width: {{ ceil($best_seller->percentage) }}%;"></span></div>
                                        <div class="sv_info">{{ ceil($best_seller->percentage) }}% Dipesan</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
        
        <div class="section_product_list custom_one white_bg">
            <div id="list-product" class="list-product-home">
                
            </div>
            <div class="overlay"></div>
            <span id="page-product"></span>
            <div class="paging_box" id="pagination-product">

            </div>
        </div>

    </section>

    @include('_template_web.footer')

    @if ($banner_popup)
        <div class="popup_landing" style="display: none;">
            <div class="popup_box">
                @php
                    $link = '#';
                    if ($banner_popup->link_type == 'external') {
                        $link = $banner_popup->link_external;
                    } elseif ($banner_popup->link_type == 'internal') {
                        $link = $banner_popup->link_internal;
                    }

                    $target = '';
                    if ($banner_popup->link_target == 'new window' && $link != '#') {
                        $target = '_blank';
                    }
                @endphp
                <a href="{{ $link }}" target="{{ $target }}">
                    <img src="{{ $banner_popup->image }}">
                </a>
                {{-- <a href="{{ $link }}" target="{{ $target }}">
                    <img src="{{ $banner_popup->image_mobile }}">
                </a> --}}
                <div class="close_btn"></div>
            </div>
        </div>
    @endif
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

        $(document).ready(function() {
            var x = get_cookie('banner_popup');
            if (x) {
                // HIDE BANNER
            } else {
                $('.popup_landing').show();
                set_cookie('banner_popup', 'true', 1);
            }
        });
    </script>
@endsection