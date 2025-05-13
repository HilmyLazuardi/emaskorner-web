@php
    // Libraries
    use App\Libraries\Helper;
@endphp

<!DOCTYPE html>
<!--[if IE 8]>			<html class="ie ie8"> <![endif]-->
<!--[if IE 9]>			<html class="ie ie9"> <![endif]-->
<!--[if gt IE 9]><!-->
<html>
<!--<![endif]-->

    <head>
        <meta charset="utf-8">
        <meta http-equiv="content-type" content="text/html;charset=utf-8" />
        <meta name="viewport" content="target-densitydpi=device-dpi; width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;"/>
        <meta name="HandheldFriendly" content="true" />
        <link rel="icon" href="{{ asset($global_config->app_favicon) }}" />
        <title>@if(View::hasSection('title'))@yield('title') | {!! $global_config->app_name !!}@else {!! $global_config->meta_title !!} @endif</title>

        @if(View::hasSection('open_graph'))
            @yield('open_graph')
        @else
            <meta name="description" content="{!! $global_config->meta_description !!}">
            <meta name="keywords" content="{!! str_replace(',', ', ', $global_config->meta_keywords) !!}">
            <meta name="author" content="{!! $global_config->meta_author !!}">
            {{-- DEFAULT OPEN GRAPH --}}
            @if (isset($global_config->og_type))
                <meta property="og:type" content="{!! $global_config->og_type !!}" />
                <meta property="og:site_name" content="{!! $global_config->og_site_name !!}" />
                <meta property="og:title" content="@if(View::hasSection('title'))@yield('title')@else{!! $global_config->og_title !!}@endif" />
                <meta property="og:image" content="{{ asset($global_config->og_image) }}" />
                <meta property="og:description" content="{!! $global_config->og_description !!}" />
                <meta property="og:url" content="{{ Helper::get_url() }}" />

                @if ($global_config->fb_app_id)
                    <meta property="fb:app_id" content="{!! $global_config->fb_app_id !!}" />
                @endif

                <meta property="twitter:card" content="{!! $global_config->twitter_card !!}" />
                @if ($global_config->twitter_site)
                    <meta property="twitter:site" content="{!! $global_config->twitter_site !!}" />
                @endif
                @if ($global_config->twitter_site_id)
                    <meta property="twitter:site:id" content="{!! $global_config->twitter_site_id !!}" />
                @endif
                @if ($global_config->twitter_creator)
                    <meta property="twitter:creator" content="{!! $global_config->twitter_creator !!}" />
                @endif
                @if ($global_config->twitter_creator_id)
                    <meta property="twitter:creator:id" content="{!! $global_config->twitter_creator_id !!}" />
                @endif
            @endif
        @endif

        <!-- provide the csrf token -->
        <meta name="csrf-token" content="{{ csrf_token() }}" />

        <link rel="stylesheet" type="text/css" href="{{ asset('web/fonts/stylesheet.css') }}">
        @yield('css-plugins')
        <link rel="stylesheet" type="text/css" href="{{ asset('web/css/main.css') }}?v=4.7">

        <script type="text/javascript" src="{{ asset('web/js/jquery.js') }}"></script>
        @yield('script-plugins')
        <script type="text/javascript" src="{{ asset('web/js/main.js') }}?v=1.6"></script>

        <!-- Custom Script -->
        <script src="{{ asset('js/thehelper.js') }}?v=1.2"></script>

        <style>
            .popup_alert .overlay {
                width: 100%;
                height: 100%;
                background: #000;
                opacity: 0.5;
                position: fixed;
                top: 0;
                left: 0;
            }

            .popup_alert .popup_box {
                position: absolute;
                top: 50%;
                left: 50%;
                -webkit-transform: translateX(-50%) translateY(-50%);
                -moz-transform: translateX(-50%) translateY(-50%);
                transform: translateX(-50%) translateY(-50%);
                background: #fff;
                border-radius: 20px;
                padding: 20px;
                text-align: center;
                width: 100%;
                max-width: 360px;
            }

            .popup_alert .popup_box .icon_box {
                width: 42px;
                height: 42px;
                background: #0C6663;
                border-radius: 40px;
                position: relative;
                margin: 0 auto 10px;
            }

            .popup_alert .popup_box .icon_box img {
                position: absolute;
                top: 50%;
                left: 50%;
                -webkit-transform: translateX(-50%) translateY(-50%);
                -moz-transform: translateX(-50%) translateY(-50%);
                transform: translateX(-50%) translateY(-50%);
                display: block;
            }

            .popup_alert .popup_box h3 {
                margin-bottom: 10px;
            }

            .popup_alert .popup_box .close_btn {
                width: 30px;
                height: 30px;
                background: #fff url({{ asset('web/images/icon_close_grey.png') }}) no-repeat center/26px;
                position: absolute;
                top: -10px;
                right: -10px;
                display: block;
                text-indent: -9999px;
                border-radius: 20px;
                box-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
            }

            .bg-alert-error {
                background: #f00 !important;
            }

            .bg-alert-success {
                background: #0C6663 !important;
            }
        </style>

        <script type='text/javascript' src='https://platform-api.sharethis.com/js/sharethis.js#property=613ad210c82eda0019c36747&product=sop' async='async'></script>

        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-VB946CBTSZ"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());

            gtag('config', 'G-VB946CBTSZ');
        </script>

        @yield('header-script')
    </head>

    <body>
        @yield('body-script')

        {{-- CONTENT --}}
        @yield('content')
        
        <div class="loader"><div class="loader_box"><img src="{{ asset('web/images/loader.gif') }}"> <span>Loading . . .</span></div></div>

        <div class="popup_alert" style="display: none;">
            <div class="overlay"></div>
            <div class="popup_box">
                <div class="icon_box" id="popup_alert_icon"><img src="{{ asset('web/images/icon_close_white.png') }}"></div>
                <h3 id="popup_alert_title">Error!</h3>
                <p id="popup_alert_message">Ini pesan error</p>
                <a href="javascript:void(0)" class="close_btn" onclick="hide_popup_alert();">Close</a>
            </div>
        </div>
        
        </div>
        <!-- /.wrapper -->
         
        <!-- JAVASCRIPT FILES -->
        <script>
            // Function Loader
            function show_loader(){
                $('.loader').show();
            }
            function hide_loader(){
                $('.loader').hide();
            }

            function show_popup_alert(type, title, message){
                $("#popup_alert_icon").removeClass('bg-alert-success bg-alert-error');

                switch (type) {
                    case 'error':
                        $("#popup_alert_icon").show();
                        $("#popup_alert_icon").html('<img src="{{ asset("web/images/icon_close_white.png") }}">');
                        $("#popup_alert_icon").addClass('bg-alert-error');
                        $("#popup_alert_title").html(title);
                        $("#popup_alert_message").html(message);
                        $('.popup_alert').fadeIn();
                        break;

                    case 'success':
                        $("#popup_alert_icon").show();
                        $("#popup_alert_icon").html('<img src="{{ asset("web/images/success.png") }}">');
                        $("#popup_alert_icon").addClass('bg-alert-success');
                        $("#popup_alert_title").html(title);
                        $("#popup_alert_message").html(message);
                        $('.popup_alert').fadeIn();

                        setTimeout(function(){
                            $('.popup_alert').fadeOut();
                        }, 3000);
                        break;
                
                    default:
                        break;
                }
            }

            function hide_popup_alert() {
                $('.popup_alert').hide();
            }
        </script>

        <script>
            $(document).ready(function(){
                @if (Session::has('buyer'))
                    refresh_data_count_cart();
                @endif
            })
            
            $(window).bind('load resize',function(){
                if($(window).width()<767){
                    if($('.row_flex .flex_box .content a').last()){
                        $('.row_flex .flex_box .content a').last().parents('.flex_box').hide();
                        $('.row_flex .flex_box .content a').last().parents('.flex_box').siblings('.flex_box').css('margin-bottom',0);
                    }
                }
                else{
                    if($('.row_flex .flex_box .content a').last()){
                        $('.row_flex .flex_box .content a').last().parents('.flex_box').show();
                    }
                }
            })

            @if (Session::has('buyer'))
                function refresh_data_count_cart() {
                    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

                    $.ajax({
                        type: "POST",
                        url: "{{ route('web.buyer.total_cart') }}",
                        data: {
                            _token : CSRF_TOKEN,
                        },
                        dataType: "json",
                        beforeSend: function() {
                            $(".h_cart").html("");
                        }
                    })
                    .done(function(response) {
                        // console.log(response)
                        if (typeof response != 'undefined') {
                            if(response.status == 'success'){
                                $(".h_cart").html(response.html);
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
                        // console.log('refresh data: ');
                    });
                }
            @endif
        </script>

        @yield('footer-script')
    </body>

</html>