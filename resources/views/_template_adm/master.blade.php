@php
    // Libraries
    use App\Libraries\Helper;
@endphp

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <!-- Meta, title, CSS, favicons, etc. -->
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="{{ asset($global_config->app_favicon) }}" />

        <title>@if(View::hasSection('title'))@yield('title') | @endif{!! $global_config->app_name !!}@if(env('ADMIN_DIR') != '') Admin @endif</title>

        <meta name="description" content="{!! $global_config->meta_description !!}">
        <meta name="keywords" content="{!! str_replace(',', ', ', $global_config->meta_keywords) !!}">

        @if(View::hasSection('open_graph'))
            @yield('open_graph')
        @else
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

        <!-- Bootstrap -->
        <link href="{{ asset('vendors/bootstrap/dist/css/bootstrap.min.css') }}" rel="stylesheet">
        <!-- Font Awesome -->
        <link href="{{ asset('vendors/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">
        <!-- NProgress -->
        <link href="{{ asset('vendors/nprogress/nprogress.css') }}" rel="stylesheet">
        <!-- jQuery custom content scroller -->
        <link href="{{ asset('vendors/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.min.css') }}" rel="stylesheet">
        <!-- bootstrap-progressbar -->
        <link href="{{ asset('vendors/bootstrap-progressbar/css/bootstrap-progressbar-3.3.4.min.css') }}" rel="stylesheet">
        <!-- animate.css -->
        <link href="{{ asset('vendors/animate.css/animate.min.css') }}" rel="stylesheet">
        
        <!-- FONT WORKSANS -->
        <link href="{{ asset('web/fonts/stylesheet.css') }}" rel="stylesheet">

        <!-- Custom Theme Style -->
        <link href="{{ asset('admin/css/custom.css') }}" rel="stylesheet">

        <!-- Skin -->
        <link href="{{ asset('admin/css/skin/'.$global_config->app_skin.'.css') }}" rel="stylesheet">

        <style>
            .scroll-top {
                width: 40px;
                height: 30px;
                position: fixed;
                bottom: 50px;
                right: 17px;
                display: none;
                -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=50)";       /* IE 8 */
                filter: alpha(opacity=50);  /* IE 5-7 */
                -moz-opacity: 0.5;          /* Netscape */
                -khtml-opacity: 0.5;        /* Safari 1.x */
                opacity: 0.5;               /* Good browsers */
            }
            .scroll-top i {
                display: inline-block;
                color: #FFFFFF;
            }
        </style>

        @yield('css')

        <!-- Custom Script -->
        <script src="{{ asset('js/thehelper.js') }}?v=1.0"></script>
        <script src="{{ asset('js/custom.js') }}?v=1.0.1"></script>
        
        @yield('script-head')
    </head>

    <body class="nav-md">
        <div class="container body">
            <div class="main_container">
                <div class="col-md-3 left_col menu_fixed">
                    <div class="left_col scroll-view">
                        <div class="navbar nav_title" style="border: 0;">
                            <a href="{{ route('admin.home') }}" class="site_title">
                                <img src="{{ asset($global_config->app_logo) }}" style="max-width:40px" />
                                <span>{!! $global_config->app_name !!}</span>
                            </a>
                        </div>

                        <div class="clearfix"></div>

                        <!-- menu profile quick info -->
                        <div class="profile clearfix">
                            <a href="{{ route('admin.profile') }}">
                                <div class="profile_pic">
                                    <img src="{{ Helper::get_avatar() }}" alt="avatar" class="img-circle profile_img" />
                                </div>
                            </a>
                            <div class="profile_info">
                                <span>{{ ucwords(lang('welcome', $translations)) }},</span>
                                <h2>{{ Session::get(env('SESSION_ADMIN_NAME', 'sysadmin'))->username }}</h2>
                            </div>
                        </div>
                        <!-- /menu profile quick info -->

                        <br />

                        @include('_template_adm.sidebar')

                        <!-- /menu footer buttons -->
                        <div class="sidebar-footer hidden-small">
                            <a data-toggle="tooltip" data-placement="top" title="{{ ucwords(lang('my profile', $translations)) }}" href="{{ route('admin.profile') }}">
                                <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                            </a>
                            <a data-toggle="tooltip" data-placement="top" title="{{ ucwords(lang('view site', $translations)) }}" href="{{ $global_config->app_url_site }}" target="_blank">
                                <span class="glyphicon glyphicon-globe" aria-hidden="true"></span>
                            </a>
                            <a data-toggle="tooltip" data-placement="top" title="{{ ucwords(lang('info', $translations)) }}" onclick="alert('{{ $global_config->app_info }}')">
                                <span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
                            </a>
                            <a data-toggle="tooltip" data-placement="top" title="{{ ucwords(lang('log out', $translations)) }}" href="{{ route('admin.logout') }}" onclick="return confirm('{{ lang('Are you sure to #action?', $translations, ['#action' => lang('log out', $translations)]) }}')">
                                <span class="glyphicon glyphicon-off" aria-hidden="true"></span>
                            </a>
                        </div>
                        <!-- /menu footer buttons -->
                    </div>
                    <!-- /left_col scroll-view -->
                </div>
                <!-- /left_col menu_fixed -->

                @include('_template_adm.nav')

                <!-- page content -->
                <div class="right_col" role="main">
                    @yield('content')
                </div>
                <!-- /page content -->

                <!-- footer content -->
                <footer>
                    <div class="pull-right">
                        &copy; {{ $global_config->app_copyright_year }} {!! $global_config->app_name !!} {{ 'v'.$global_config->app_version }}
                        @if (!empty($global_config->powered_by))
                        - {{ lang('Powered by', $translations) }} <a href="{{ $global_config->powered_by_url }}">{{ $global_config->powered_by }}</a>
                        @endif
                    </div>

                    <div class="clearfix"></div>
                
                    @if (env('DISPLAY_SESSION', false))
                        @include('_template_adm.debug')
                    @endif
                </footer>
                <!-- /footer content -->
            </div>
        </div>

        <button class="btn btn-primary scroll-top" data-scroll="up" type="button">
            <i class="fa fa-chevron-up"></i>
        </button>

        <!-- #modal-loading -->
        <div class="modal fade bs-modal-sm" tabindex="-1" id="modal-loading" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">{{ lang('Loading, please wait..', $translations) }}</h4>
                    </div>
                    <div class="modal-body">
                        <h5 class="text-center"><i class="fa fa-spin fa-spinner"></i>&nbsp; {{ lang('Loading, please wait..', $translations) }}</h5>
                    </div>
                </div>
            </div>
        </div>
        <!-- /#modal-loading -->

        <!-- jQuery -->
        <script src="{{ asset('vendors/jquery/dist/jquery.min.js') }}"></script>
        <!-- Bootstrap -->
        <script src="{{ asset('vendors/bootstrap/dist/js/bootstrap.min.js') }}"></script>
        <!-- FastClick -->
        <script src="{{ asset('vendors/fastclick/lib/fastclick.js') }}"></script>
        <!-- NProgress -->
        <script src="{{ asset('vendors/nprogress/nprogress.js') }}"></script>
        <!-- jQuery custom content scroller -->
        <script src="{{ asset('vendors/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.concat.min.js') }}"></script>
        <!-- bootstrap-progressbar -->
        <script src="{{ asset('vendors/bootstrap-progressbar/bootstrap-progressbar.min.js') }}"></script>

        @yield('script-sidebar')

        <!-- Custom Theme Scripts -->
        <script src="{{ asset('admin/js/custom.js') }}"></script>

        <script>
            $(document).ready(function () {
                $(window).scroll(function () {
                    if ($(this).scrollTop() > 100) {
                        $('.scroll-top').fadeIn();
                    } else {
                        $('.scroll-top').fadeOut();
                    }
                });

                $('.scroll-top').click(function () {
                    $("html, body").animate({
                        scrollTop: 0
                    }, 500);
                    return false;
                });

                @if (env('NOTIF_MODULE'))
                    get_notif();
                    setInterval(function(){ get_notif(); }, {{ env('NOTIF_INTERVAL', 60000) }});
                @endif
            });

            @if (env('NOTIF_MODULE'))
                function get_notif() {
                    $.ajax({
                        type: "GET",
                        url: "{{ route('admin.notif') }}",
                        dataType: "json",
                        beforeSend: function () {
                            // do something before send the data
                        },
                    })
                        .done(function (response) {
                            // Callback handler that will be called on success
                            if (typeof response != 'undefined') {
                                if (response.status == 'true') {
                                    // SUCCESS RESPONSE

                                    var html_notif_list = '<li><a><span class="message text-center">No Unread Notifications</span></a></li>';
                                    var html_notif_button = '<li><div class="text-center"><a href="{{ route('admin.notif.list') }}"><strong>{{ ucwords(lang('see all #item', $translations, ['#item' => lang('notifications', $translations)])) }}</strong>&nbsp; <i class="fa fa-angle-right"></i></a></div></li>';

                                    // get total notif
                                    var total_notif = response.total;
                                    if (total_notif > 0) {
                                        $('#sys_notif_badge').html(total_notif);
                                        $('#sys_notif_badge').show();

                                        // set notif list
                                        html_notif_list = '';
                                        response.data.forEach(element => {
                                            html_notif_list += '<li><a href="'+element.url+'">';
                                                html_notif_list += '<span class="image"><img src="{{ Helper::get_avatar() }}" alt="Profile Image" /></span>';
                                                html_notif_list += '<span>';
                                                    html_notif_list += '<span>&nbsp;</span>';
                                                    html_notif_list += '<span class="time">'+element.time_ago+'</span>';
                                                html_notif_list += '</span>';
                                                html_notif_list += '<span class="message" style="font-weight:bold">';
                                                    html_notif_list += element.subject;
                                                html_notif_list += '</span>';
                                                html_notif_list += '<span class="message">';
                                                    html_notif_list += element.summary;
                                                html_notif_list += '</span>';
                                            html_notif_list += '</a></li>';
                                        });
                                    } else {
                                        $('#sys_notif_badge').hide();
                                    }

                                    html_notif_list += html_notif_button;
                                    $('#menu-notif-list').html(html_notif_list);
                                } else {
                                    // FAILED RESPONSE
                                    alert('ERROR: ' + response.message);
                                }
                            } else {
                                alert('Server not respond, please try again.');
                            }
                        })
                        .fail(function (jqXHR, textStatus, errorThrown) {
                            // Callback handler that will be called on failure

                            // Log the error to the console
                            console.error("The following error occurred: " + textStatus, errorThrown);

                            alert("The following error occurred: " + textStatus + "\n" + errorThrown);
                            location.reload();
                        })
                        .always(function () {
                            // Callback handler that will be called regardless
                            // if the request failed or succeeded
                    });
                }
            @endif
        </script>

        @yield('script')
    </body>

</html>
