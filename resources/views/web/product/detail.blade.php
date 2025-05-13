@extends('_template_web.master')

@php
    use App\Libraries\Helper;
    $content = json_decode($data->details);
    $pagetitle = $data->name . ' - ' . $data->variant_name;
@endphp

@section('title', $pagetitle)

@section('open_graph')
    <meta name="description" content="Beli {{ $data->name }} di toko {{ $data->seller_store_name }} Rp{{ number_format($data->price, 0, ',', '.') }} di LokalKorner">
    <meta name="keywords" content="{!! str_replace(',', ', ', $global_config->meta_keywords) !!}">
    <meta name="author" content="{!! $global_config->meta_author !!}">

    <meta property="og:type" content="product" />
    <meta property="og:site_name" content="LokalKorner" />
    <meta property="og:title" content="{{ $data->name }} - LokalKorner" />
    <meta property="og:image" content="{{ asset($data->image) }}" />
    <meta property="og:description" content="Beli {{ $data->name }} di toko {{ $data->seller_store_name }} Rp{{ number_format($data->price, 0, ',', '.') }} di LokalKorner" />
    <meta property="og:url" content="{{ Helper::get_url() }}" />
@endsection

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
    <script type="text/javascript" src="{{ asset('web/js/canvasjs.min.js') }}"></script>
@endsection

@section('header-script')
@endsection

@section('content')
    @include('_template_web.header_with_categories')
    @include('_template_web.alert_popup')

    <section>
        @if ($preview)
            <h2 style="text-align:center; color:#fff; display:block; padding:20px 0; background-color:darkgray;">
                PREVIEW
            </h2>
        @endif
        <input type="hidden" name="product_id" value="{{ $data->id }}">
        <input type="hidden" name="product_slug" value="{{ $slug }}">
        <div class="section_product_banner vtwo">
            <div class="row_flex container">
                <div class="spd_box">
                    <div class="spd_img_box">
                        {{-- @if ($data->flag_soldout)
                            <span class="flag_soldout"></span>
                        @elseif ($data->flag_campaign_end)
                            <span class="flag_ended"></span>
                        @endif --}}
                        <div class="slider_banner">
                            <div class="bs_img"><img src="{{ asset($data->image) }}"></div>
                            @if(isset($data->image_2))
                                <div class="bs_img"><img src="{{ asset($data->image_2) }}"></div>
                            @endif
                            @if(isset($data->image_3))
                                <div class="bs_img"><img src="{{ asset($data->image_3) }}"></div>
                            @endif
                            @if(isset($data->image_4))
                                <div class="bs_img"><img src="{{ asset($data->image_4) }}"></div>
                            @endif
                            @if(isset($data->image_5))
                                <div class="bs_img"><img src="{{ asset($data->image_5) }}"></div>
                            @endif
                            @foreach ($variants as $variant)
                                @if(isset($variant->variant_image))
                                    <div class="bs_img" id="{{ 'img_variant_' . $variant->raw_id  }}" ><img src="{{ asset($variant->variant_image) }}"></div>
                                @endif
                            @endforeach
                        </div>
                        <div class="wishlist_btn" >
                            <input type="checkbox" id="add_to_wishlist" value="{{ $variant->default_variant_id }}" onclick="create_wishlist()">
                            <span></span>
                        </div>
                        <span class="total_count"></span>
                    </div>
                    <div class="slider_thumb">
                        <div class="bs_img"><img src="{{ asset($data->image) }}"></div>
                        @if(isset($data->image_2))
                            <div class="bs_img"><img src="{{ asset($data->image_2) }}"></div>
                        @endif
                        @if(isset($data->image_3))
                            <div class="bs_img"><img src="{{ asset($data->image_3) }}"></div>
                        @endif
                        @if(isset($data->image_4))
                            <div class="bs_img"><img src="{{ asset($data->image_4) }}"></div>
                        @endif
                        @if(isset($data->image_5))
                            <div class="bs_img"><img src="{{ asset($data->image_5) }}"></div>
                        @endif
                        @foreach ($variants as $variant)
                            @if(isset($variant->variant_image))
                                <div class="bs_img" id="{{ 'thumb_img_variant_' . $variant->raw_id  }}" ><img src="{{ asset($variant->variant_image) }}"></div>
                            @endif
                        @endforeach
                    </div>
                </div>
                <div class="spn_box">
                    <div class="section_product_name">
                        <div class="container">
                            <h3 id="product_name">Loading..</h3>
                            <span class="price" id="product_price">Loading..</span>
                            <div class="share_btn"></div>
                            <div id="sharethis_box">
                                <!-- ShareThis BEGIN -->
                                <div class="sharethis-inline-share-buttons"></div>
                                <!-- ShareThis END -->
                            </div>
                        </div>
                    </div>
                    <div class="section_product_info">
                        <div class="row_flex">
                            {{-- <div class="spi_left">
                                <div class="spi_box">
                                    <span id="total_pemesan">-</span>
                                    Pemesan
                                </div>
                                <div class="spi_box">
                                    <span id="campaign_days_left">- hari</span>
                                    Tersisa
                                </div>
                            </div> --}}
                            <div class="spi_left">
                                <div class="spi_box">
                                    <div class="pl_diagram_box">
                                        <div id="chartContainerDiv">
                                            <div id="chartContainer" style="width: 160px;height: 160px;"></div>
                                        </div>
                                        <div class="info_stock" id="info_stock"><span>- Dipesan</span> dari -</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- FLAG SOLD OUT / FLAG CAMPAIGN END --}}
                        @php
                            $display_none = '';
                            if ($data->total_qty < 1 || $data->flag_campaign_end) {
                                // $display_none = 'style=display:none;';
                            }
                        @endphp
                        <div class="row_flex">
                            <div class="spi_right">
                                @if (!empty($data->variant_1) && !empty($data->variant_1_list))
                                    <div class="form_box select-variant" {{ $display_none }}>
                                        <span class="title">{{ $data->variant_1 }}</span>
                                        <div class="box_init">
                                            <select class="select3" data-placeholder="Varian" name="variant_1" id="variant_1">
                                                @foreach ($data->variant_1_list as $key => $variant_1_list)
                                                    @php
                                                        $selected_variant_1 = '';
                                                        if ($data->variant_1_slug[$key] == $data->variant_1_name) {
                                                            $selected_1 = $data->variant_1_name;
                                                            $selected_variant_1 = 'selected';
                                                        }
                                                        $disabled_variant_1 = '';
                                                    @endphp
                                                    <option value="{{ $data->variant_1_slug[$key] }}" {{ $selected_variant_1 }}>{{ ucwords($variant_1_list) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @endif
                                @if (!empty($data->variant_2) && !empty($data->variant_2_list))
                                    <div class="form_box select-variant" {{ $display_none }}>
                                        <span class="title">{{ $data->variant_2 }}</span>
                                        <div class="box_init">
                                            <select class="select3" data-placeholder="Varian" name="variant_2" id="variant_2">
                                                @foreach ($data->variant_2_list as $key => $variant_2_list)
                                                    @php
                                                        $selected_variant_2 = '';
                                                        if ($data->variant_2_slug[$key] == $data->variant_2_name) {
                                                            $selected_2 = $data->variant_2_name;
                                                            $selected_variant_2 = 'selected';
                                                        }
                                                    @endphp
                                                    <option value="{{ $data->variant_2_slug[$key] }}" {{ $selected_variant_2 }}>{{ ucwords($variant_2_list) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @endif
                                <div id="validation-row">
                                    @if ($data->flag_soldout)
                                        @if ($data->total_qty < 1)
                                            <div class="flag_text">
                                                <span>Terjual Habis</span>
                                                Terima kasih sudah mendukung kami. Stay tuned untuk cool project selanjutnya, ya!
                                                <a href="{{ route('web.home') }}" class="def_btn">CEK PRODUK LAINNYA</a>
                                            </div>
                                        @else
                                        <div class="flag_text">
                                            <span>Variant Ini Terjual Habis</span>
                                            Silahkan cek variant yang lain ya!
                                        </div>
                                        @endif
                                    @elseif ($data->flag_campaign_end)
                                        <div class="flag_text">
                                            <span>Campaign Berakhir</span>
                                            Terima kasih sudah mendukung kami. Stay tuned untuk cool project selanjutnya, ya!
                                            <a href="{{ route('web.home') }}" class="def_btn">CEK PRODUK LAINNYA</a>
                                        </div>
                                    @else
                                        <div class="form_box" id="input_qty">
                                            <span class="title">Jumlah</span>
                                            <div class="box_init">
                                                <button type="button" class="minus_btn btn-number" disabled data-type="minus" data-field="quant">-</button>
                                                <input type="text" value="1" name="quant" id="quant" class="form-control input-number" value="1" min="1" max="{{ $data->qty_available }}">
                                                <button type="button" class="plus_btn btn-number" data-type="plus" data-field="quant">+</button>
                                            </div>
                                        </div>
                                        @if ($preview)
                                            <button type="button" disabled class="red_btn">+ Keranjang</button>
                                        @else
                                            <button type="button" class="red_btn" id="btn_submit">+ Keranjang</button>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="section_product_menu">
            <div class="container">
                <ul class="tab_menu">
                    <li><a href="#tab-1" class="active">Deskripsi Produk</a></li>
                    <li><a href="#tab-2">FAQ</a></li>
                    <li><a href="#tab-3">Tentang Penjual</a></li>
                </ul>
                <div class="tab_wrapper" id="tab-1">
                    <div class="section_content_element_simple">
                        @if(!empty($content))
                            {{-- SECTIONS --}}
                            @foreach ($content as $item)
                                @if ($item->type == 'text')
                                    @php
                                        $text_value = $item->text;
                                    @endphp
                                    <div class="container">
                                        <div class="sce_text">
                                            @php echo $text_value; @endphp
                                        </div>
                                    </div>
                                @elseif ($item->type == 'image')
                                    @php
                                        $image_value = asset('uploads/page/'.$item->image);
                                    @endphp
                                    <div class="container">
                                        <div class="sce_image">
                                            <img src="{{ $image_value }}">
                                            {{-- <img class="mobile" src="{{ $image_value }}"> --}}
                                        </div>
                                    </div>
                                @elseif ($item->type == 'image & text')
                                    @php
                                        $image_value = asset('uploads/page/'.$item->image);
                                        $text_value = $item->text;
                                        $text_position_value = $item->text_position;
                                    @endphp
                                    <div class="container">
                                        <div class="sce_clear">
                                            @if ($text_position_value == 'right')
                                                <div class="sce_img_left">
                                                    <img src="{{ $image_value }}">
                                                </div>
                                                <div class="sce_desc_right">
                                                    @php echo $text_value; @endphp
                                                </div>
                                            @else
                                                <div class="sce_img_right">
                                                    <img src="{{ $image_value }}">
                                                </div>
                                                <div class="sce_desc_left">
                                                    @php echo $text_value; @endphp
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @elseif ($item->type == 'video')
                                    @php
                                        $video_value = $item->video;
                                        $param = explode('?v=', $video_value);
                                    @endphp
                                    @if (isset($param[1]))
                                        <div class="container">
                                            <div class="sce_video">
                                                <div class="sce_video_box">
                                                    <iframe width="560" height="315" src="https://www.youtube.com/embed/{{ $param[1] }}" frameborder="0" allowfullscreen></iframe>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <h3>Video (YouTube URL) yang diinput salah, harus mengikuti format "https://www.youtube.com/watch?v=XXX"</h3>
                                    @endif
                                @elseif ($item->type == 'video & text')
                                    @php
                                        $video_value = $item->video;
                                        $param = explode('?v=', $video_value);
                                        $text_value = $item->text;
                                        $text_position_value = $item->text_position;
                                    @endphp

                                    <div class="container">
                                        <div class="sce_clear">
                                            @if (isset($param[1]))
                                                @if ($text_position_value == 'right')
                                                    <div class="sce_img_left">
                                                        <div class="sce_video">
                                                            <div class="sce_video_box">
                                                                <iframe width="560" height="315" src="https://www.youtube.com/embed/{{ $param[1] }}" frameborder="0" allowfullscreen></iframe>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="sce_desc_right">
                                                        @php echo $text_value; @endphp
                                                    </div>
                                                @else
                                                    <div class="sce_img_right">
                                                        <div class="sce_video">
                                                            <div class="sce_video_box">
                                                                <iframe width="560" height="315" src="https://www.youtube.com/embed/{{ $param[1] }}" frameborder="0" allowfullscreen></iframe>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="sce_desc_left">
                                                        @php echo $text_value; @endphp
                                                    </div>
                                                @endif
                                            @else
                                                <h3>Video (YouTube URL) yang diinput salah, harus mengikuti format "https://www.youtube.com/watch?v=XXX"</h3>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @else
                            <div class="section_content_element">
                                <div class="container">
                                    <div class="ce_text width100">
                                        <p>Deskripsi produk tidak tersedia</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="tab_wrapper" id="tab-2">
                    <div class="faq_wrapper">
                        @if(isset($product_faq[0]))
                            @foreach($product_faq as $faq)
                                <div class="faq_box">
                                    <div class="faq_top">{{ $faq->question }}</div>
                                    <div class="faq_bottom">{!! nl2br($faq->answer) !!}</div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
                <div class="tab_wrapper" id="tab-3">
                    <div class="produsen_box">
                        <div class="row_clear">
                            <div class="produsen_img">
                                @php
                                    $seller_avatar = asset('images/no-image.png');
                                    if(isset($data->seller_avatar)){
                                        $seller_avatar = asset($data->seller_avatar);
                                    }
                                @endphp
                                <img src="{{ $seller_avatar }}"> 
                            </div>
                            <div class="produsen_name">
                                <h4>{{ $data->seller_store_name }}</h4>
                                <span>{{ $data->seller_city }}</span>
                            </div>
                        </div>
                        <div class="produsen_desc">
                            @if(isset($data->seller_desc))
                                <p>{{ $data->seller_desc }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('_template_web.footer')
@endsection

@section('footer-script')
    <script>
        var product_id = '';
        @if (count($errors) > 0)
            show_popup_failed();
        @endif
        @if (session('error'))
            show_popup_failed();
        @endif
        @if (session('error_variant'))
            show_popup_failed();
        @endif
        @if (session('success'))
            show_popup_success();
        @endif
        $( document ).ready(function() {
            get_variant_detail();
        });
        $('.select3').change(function(){
            get_variant_detail();
        });
        function create_wishlist() {
            var CSRF_TOKEN  = $('meta[name="csrf-token"]').attr('content');
            var variant     = $('#add_to_wishlist').val();
            $.ajax({
                type: "POST",
                url: "{{ route('web.buyer.add_wishlist_ajax') }}",
                data: {
                    _token : CSRF_TOKEN,
                    from : 'product-detail',
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
                        // alert(response.message);
                        
                        if (response.flag == 'add') {
                            $('#add_to_wishlist').prop('checked', true);
                        }
                        
                        if (response.flag == 'remove') {
                            $('#add_to_wishlist').prop('checked', false);
                        }
                    } else {
                        show_popup_alert("error", "Error!", response.message);
                        // alert(response.message);
                        if (response.need_auth) {
                            var login_url = "{{ route('web.auth.login') }}"
                            window.location.href = login_url;
                        }
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

        function get_variant_detail() {
            // INITIATE DATA
            var product_item_slug = "{{ $data->product_item_slug }}";
            var variant_1 = $("#variant_1").val();
            // GENERATE SLUG FOR 1 VARIANT ONLY
            var variant_slug = product_item_slug + '-' + variant_1;
            
            // GENERATE SLUG FOR 2 VARIANT
            var variant_2_exist = "{{ $data->variant_2_exist }}";
            if (variant_2_exist) {
                var variant_2 = $("#variant_2").val();
                variant_slug = product_item_slug + '-' + variant_1 + '-' + variant_2;
            }
            // AJAX GET DETAIL SELECTED PRODUCT VARIANT
            var CSRF_TOKEN  = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                type: "POST",
                url: "{{ route('web.product.detail_ajax') }}",
                data: {
                    _token : CSRF_TOKEN,
                    variant_slug : variant_slug,
                },
                dataType: "json",
                beforeSend: function() {
                    // LOADING HERE
                    $("#product_name").text('Loading..');
                    $("#product_price").text('Loading..');
                    $("#total_pemesan").text('-');
                    $("#campaign_days_left").text('-');
                    $("#info_stock").html('<span>- Dipesan</span> dari -');
                }
            })
            .done(function(response) {
                if (typeof response != 'undefined') {
                    if (response.status == 'success') {
                        // INIT VARIABLE AND LOCAL FUNCTION
                        var item = response.data;
                        function thousandSeparator(x) {
                            return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                        }
                        function render_chart() {
                            var pembagi = 1;
                            if (item.total_qty > 0) {
                                pembagi = item.total_qty;
                            }
                            var color_terjual = "#D3A381";
                            var color_tersedia = "#0B5C59";
                            if (item.persentase == 100) {
                                // set warna sold out
                                color_terjual = "#F00";
                                color_tersedia = "#F00";
                            }
                            var terjual     = item.persentase;
                            var tersedia    = 100 - terjual;
                            
                            var chart = new CanvasJS.Chart("chartContainer", {
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
                                    innerRadius: '90%',
                                    dataPoints: [
                                        { y: terjual, color: color_terjual },
                                        { y: tersedia, color: color_tersedia  }
                                    ]
                                }]
                            });
                            chart.render();
                        }
                        // UPDATE URL
                        var base_url = window.location.origin;
                        var first_path = window.location.href.split('/');
                        var new_url = base_url + '/' + first_path[3] + '/' + item.slug;
                        @if (request('preview'))
                            new_url += "?preview={{ request('preview') }}";
                        @endif
                        window.history.pushState(
                            $(document).find("title").text(),
                            $(document).find("title").text(),
                            new_url
                        );
                        // UPDATE NAME
                        var name = item.name + ' - ' + item.variant_name;
                        $("#product_name").text(name);
                        $(document).attr("title", name + " | {!! $global_config->app_name !!}");
                        
                        // UPDATE PRICE
                        var price = thousandSeparator(item.price);
                        $("#product_price").text('Rp'+price);
                        // UPDATE TOTAL PEMESAN
                        $("#total_pemesan").text(item.total_order);
                        
                        // UPDATE SISA WAKTU CAMPAIGN
                        $("#campaign_days_left").text(item.campaign_days_left + ' hari');
                        // UPDATE INFO STOCK (CHART TEXT)
                        var html_info_stock = '<span>' + item.total_order + ' Dipesan</span> dari ' + item.total_qty
                        $("#info_stock").html(html_info_stock);
                        
                        // RENDER NEW CHART
                        $("#chartContainerDiv").html('');
                        $("#chartContainerDiv").html('<div id="chartContainer" style="width: 160px;height: 160px;"></div>');
                        render_chart();
                        // UPDATE VALIDATION ROW (FLAG SOLD OUT / FLAG CAMPAIGN END)
                        var route_home = "{{ route('web.home') }}";
                        var html = '';
                        $("#validation-row").html(html);
                        if (item.flag_soldout) {
                            if (item.all_qty < 1) {
                                html += "<div class='flag_text'>";
                                    html += "<span>Terjual Habis</span>";
                                    html += "Terima kasih sudah mendukung kami.<br>Stay tuned untuk cool project selanjutnya, ya!";
                                html += "</div>";
                                $("#validation-row").html(html);
                                // $(".select-variant").remove();
                            } else {
                                html += "<div class='flag_text'>";
                                    html += "<span>Variant Ini Terjual Habis</span>";
                                    html += "Silahkan cek variant yang lain ya!";
                                html += "</div>";
                                $("#validation-row").html(html);
                            }
                        } else if (item.flag_campaign_end) {
                            html += "<div class='flag_text' style='padding-top:10px;'>";
                                html += "<span style='margin-bottom:5px;'>Campaign Berakhir</span>";
                                html += "Terima kasih sudah mendukung kami.<br>Stay tuned untuk cool project selanjutnya, ya!";
                            html += "</div>";
                            $("#validation-row").html(html);
                        } else if (!item.status){
                            html += "<div class='flag_text'>";
                                    html += "<span>Variant Ini Tidak Tersedia</span>";
                                    html += "Silahkan cek variant yang lain ya!";
                                html += "</div>";
                                $("#validation-row").html(html);
                        } else {
                            html += "<div class='form_box' id='input_qty'>";
                                html += "<span class='title'>Jumlah</span>";
                                html += "<div class='box_init'>";
                                    html += "<button type='button' class='minus_btn btn-number' disabled data-type='minus' data-field='quant'>-</button>";
                                    html += "<input type='text' value='1' name='quant' id='quant' class='form-control input-number' value='1' min='1' max=" + item.qty_available  + ">";
                                    html += "<button type='button' class='plus_btn btn-number' data-type='plus' data-field='quant'>+</button>";
                                html += "</div>";
                            html += "</div>";
                            var preview = "{{ $preview }}";
                            if (preview == 'true') {
                                html += "<button type='button' disabled class='red_btn'>+ Keranjang</button>";
                            } else {
                                html += "<button type='button' id='btn_add_cart' class='red_btn' onclick='add_cart()'>+ Keranjang</button>";
                            }
                            $("#validation-row").html(html);
                        }
                        // CHANGE MAX QTY
                        $("#quant").prop('max', item.qty_available);
                        // UPDATE WISHLIST BTN VALUE
                        product_id = item.object_id;
                        $('#add_to_wishlist').val(product_id);
                        if (item.is_wishlist) {
                            $('#add_to_wishlist').prop('checked', true);
                        } else {
                            $('#add_to_wishlist').prop('checked', false);
                        }
                        // INPUT QTY SCRIPT START
                        $('.btn-number').click(function(e){
                            e.preventDefault();
                            fieldName = $(this).attr('data-field');
                            type      = $(this).attr('data-type');
                            var input = $("input[name='"+fieldName+"']");
                            var currentVal = parseInt(input.val());
                            if (!isNaN(currentVal)) {
                                if(type == 'minus') {
                                    if(currentVal > input.attr('min')) {
                                        input.val(currentVal - 1).change();
                                    } 
                                    if(parseInt(input.val()) == input.attr('min')) {
                                        $(this).attr('disabled', true);
                                    }
                                } else if(type == 'plus') {
                                    if(currentVal < input.attr('max')) {
                                        input.val(currentVal + 1).change();
                                    }
                                    if(parseInt(input.val()) == input.attr('max')) {
                                        $(this).attr('disabled', true);
                                    }
                                }
                            } else {
                                input.val(0);
                            }
                        });
                        $('.input-number').focusin(function(){
                            $(this).data('oldValue', $(this).val());
                        });
                        
                        $('.input-number').change(function() {
                            minValue =  parseInt($(this).attr('min'));
                            maxValue =  parseInt($(this).attr('max'));
                            valueCurrent = parseInt($(this).val());
                            name = $(this).attr('name');
                            if(valueCurrent >= minValue) {
                                $(".btn-number[data-type='minus'][data-field='"+name+"']").removeAttr('disabled')
                            } else {
                                show_popup_alert("error", "Error!", 'Maaf, harap masukkan jumlah pembelian');
                                // alert('Maaf, harap masukkan jumlah pembelian');
                                $(this).val($(this).data('oldValue'));
                            }
                            if(valueCurrent <= maxValue) {
                                $(".btn-number[data-type='plus'][data-field='"+name+"']").removeAttr('disabled')
                            } else {
                                show_popup_alert("error", "Error!", 'Maaf, jumlah yang dimasukkan melebihi persediaan');
                                // alert('Maaf, jumlah yang dimasukkan melebihi persediaan');
                                $(this).val($(this).data('oldValue'));
                            }
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
                        // INPUT QTY SCRIPT END
                    } else {
                        show_popup_alert("error", "Error!", response.message);
                        // alert(response.message);
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
        function add_cart() {
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            var variant = product_id;
            var qty = $("#quant").val();
            $.ajax({
                type: "POST",
                url: "{{ route('web.buyer.add_cart') }}",
                data: {
                    _token : CSRF_TOKEN,
                    from : 'product-detail',
                    qty: qty,
                    variant : variant,
                },
                dataType: "json",
                beforeSend: function() {
                    // LOADING
                    $('#btn_add_cart').html('LOADING...');
                    $('#btn_add_cart').attr('disabled', 'disabled');
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
                        // alert(response.message);
                        if (response.need_auth) {
                            var login_url = "{{ route('web.auth.login') }}"
                            window.location.href = login_url;
                        }
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
                $('#btn_add_cart').html('PRE ORDER');
                $('#btn_add_cart').removeAttr('disabled');
            });
        }
    </script>
@endsection