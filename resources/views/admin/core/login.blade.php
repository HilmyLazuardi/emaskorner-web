<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <!-- Meta, title, CSS, favicons, etc. -->
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="{{ asset($global_config->app_favicon) }}" />

        <title>{!! $global_config->app_name !!}@if(env('ADMIN_DIR') != '') | Admin Panel @endif</title>

        <!-- Bootstrap -->
        <link href="{{ asset('vendors/bootstrap/dist/css/bootstrap.min.css') }}" rel="stylesheet">
        <!-- Font Awesome -->
        <link href="{{ asset('vendors/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">
        <!-- NProgress -->
        <link href="{{ asset('vendors/nprogress/nprogress.css') }}" rel="stylesheet">
        <!-- Animate.css -->
        <link href="{{ asset('vendors/animate.css/animate.min.css') }}" rel="stylesheet">

        <!-- FONT WORKSANS -->
        <link href="{{ asset('web/fonts/stylesheet.css') }}" rel="stylesheet">

        <!-- Custom Theme Style -->
        <link href="{{ asset('admin/css/custom.css') }}" rel="stylesheet">

        @php
            $bg_img = 'images/background.jpeg';
            if ($global_config->bg_images) {
                $bg_images_existing = json_decode($global_config->bg_images);
                if (isset($bg_images_existing->login)) {
                    $bg_img = $bg_images_existing->login;
                }
            }
        @endphp
        <style>
            body{
              font-family: 'WorkSans' !important;
            }
            #submitform{
                padding:20px !important;
            }
            .login_content h1{
              font-family: 'WorkSans' !important;                
            }
            .login_content h1::before,
            .login_content h1::after{
                display: none;
            }
            .vlogin {
                background: #F7F7F7 url("{{ asset($bg_img) }}") no-repeat fixed center;
                background-size: cover;
            }
            #submitform {
                background-color: #D3A381;
                color: aliceblue;
                padding: 5px;
                border-radius: 15px;
            }
        </style>

        @php
            $preview_class = '';
        @endphp
        @if (isset($preview))
            @php
                $preview_class = 'container_preview';
            @endphp
            @include('_vendors.simple_preview.css')
        @endif
    
        @if (!empty($global_config->recaptcha_site_key_admin) && !empty($global_config->recaptcha_secret_key_admin))
            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        @endif
    </head>

    <body class="login vlogin {{ $preview_class }}">
        @if (isset($preview))
            @include('_vendors.simple_preview.html')
        @endif
        <div>
            <div class="login_wrapper">
                <div class="animate form login_form">
                    {{-- display response message --}}
                    @include('_template_adm.message')

                    <section class="login_content">
                        <center>
                            <img src="{{ asset($global_config->app_logo) }}" class="img-responsive" alt="{!! $global_config->app_name !!}" style="max-width: 150px; max-height: 150px;">
                        </center>
                        <form action="{{ route('admin.login.auth') }}" method="POST" id="submitform">
                            @csrf

                            <h1>{{ ucwords(lang('admin login form', $translations)) }}</h1>
                            <div>
                                <input type="text" name="login_id" value="{{ old('login_id') }}" class="form-control" placeholder="{{ ucwords(lang('username', $translations)) }}" required autocomplete="off" />
                            </div>
                            <div>
                                <input type="password" name="login_pass" class="form-control" placeholder="{{ ucwords(lang('password', $translations)) }}" required autocomplete="off" />
                            </div>

                            @if (!empty($global_config->recaptcha_site_key_admin) && !empty($global_config->recaptcha_secret_key_admin))
                                <div style="margin-bottom: 10px;">
                                    <center>
                                        <div class="g-recaptcha" data-sitekey="{{ $global_config->recaptcha_site_key_admin }}"></div>
                                    </center>
                                </div>
                            @endif

                            <div>
                                <button type="submit" class="btn btn-primary btn-block submit" id="btn-login">{{ ucfirst(lang('log in', $translations)) }}</button>
                            </div>

                            <div class="clearfix"></div>

                            <!-- <div class="separator">
                                <div>
                                    <h1>{!! $global_config->app_name !!}</h1>
                                    <p>
                                        &copy; {{ $global_config->app_copyright_year }} {!! $global_config->app_name !!} {{ 'v'.$global_config->app_version }}
                                        @if (!empty($global_config->powered_by))
                                        - {{ lang('Powered by', $translations) }} <a href="{{ $global_config->powered_by_url }}">{!! $global_config->powered_by !!}</a>
                                        @endif
                                    </p>
                                </div>
                            </div>-->
                        </form>
                    </section>
                </div>
            </div>
        </div>

        <!-- jQuery -->
        <script src="{{ asset('vendors/jquery/dist/jquery.min.js') }}"></script>
        <!-- Bootstrap -->
        <script src="{{ asset('vendors/bootstrap/dist/js/bootstrap.min.js') }}"></script>
        <script>
            $(document).ready(function () {
                $("#submitform").on('submit',function(e) {
                    validate_form();

                    @if (!empty($global_config->recaptcha_site_key_admin) && !empty($global_config->recaptcha_secret_key_admin))
                        // check reCAPTCHA
                        var data_form = $(this).serialize();
                        var split_data = data_form.split('&');
                        var continue_step = true;
                        // check empty reCAPTCHA
                        $.each(split_data , function (index, value) {
                            var split_tmp = value.split('=');
                            if (split_tmp[0] == 'g-recaptcha-response' && split_tmp[1] == '') {
                                continue_step = false;
                                alert('{{ lang("Please check the captcha for continue", $translations) }}');
                                return false;
                            }
                        });
                        if (!continue_step) {
                            return false;
                        }
                    @endif

                    return true;
                });
            });

            function validate_form() {
                $('#btn-login').addClass('disabled');
                $('#btn-login').removeClass('btn-primary');
                $('#btn-login').addClass('btn-warning');
                $('#btn-login').html('<i class="fa fa-spin fa-spinner"></i>&nbsp; {{ ucwords(lang("loading", $translations)) }}...');

                setTimeout(function(){ window.location.reload(); }, 3000);
            }
        </script>
    </body>

</html>
