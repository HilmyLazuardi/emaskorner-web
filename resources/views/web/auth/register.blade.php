@extends('_template_web.master')

@section('title', 'Daftar Sekarang')

@section('content')
    @include('_template_web.header')

    <section class="section_form">
        <div class="container">
            <div class="logo_box"><h1 title="Lokal Korner"><img src="{{ asset('web/images/logo_hijau.png') }}"></h1></div>
            <h2>Daftar Sekarang</h2>
            <div class="form_wrapper form_bg">
                <form action="{{ route('web.auth.register.submit') }}" method="POST" id="registerform">
                    @csrf
                    <div class="form_box">
                        <input type="text" placeholder="Email" name="email" value="{{ old('email') }}">
                        @if (Session::has('error_email'))
                            <span class="error_msg">{{ Session::get('error_email') }}</span>
                        @endif

                        @if (count($errors) > 0)
                            <!-- Alert Failed -->
                            @foreach ($errors->messages() as $key => $error)
                                @if ($key == 'email')
                                    <span class="error_msg">{{ $error[0] }}</span>
                                @endif
                            @endforeach
                        @endif
                    </div>
                    <div class="form_box">
                        <input type="password" placeholder="Password" name="password">
                        @if (count($errors) > 0)
                            <!-- Alert Failed -->
                            @foreach ($errors->messages() as $key => $error)
                                @if ($key == 'password')
                                    <span class="error_msg">{{ $error[0] }}</span>
                                @endif
                            @endforeach
                        @endif
                    </div>
                    <div class="form_box">
                        <input type="password" placeholder="Konfirmasi Password" name="password_confirmation">
                        @if (Session::has('error_password'))
                            <span class="error_msg">{{ Session::get('error_password') }}</span>
                        @endif
                    </div>
                    <div class="label_box">
                        <label><input type="checkbox" name="agreement"><span>Saya Menyetujui</span></label> <a href="{{ route('web.home') . '/terms-of-service' }}" target="_blank">Syarat dan Ketentuan</a>
                    </div>

                    @if (count($errors) > 0)
                        <!-- Alert Failed -->
                        @foreach ($errors->messages() as $key => $error)
                            @if ($key == 'agreement')
                                <span class="error_msg">{{ $error[0] }}</span>
                            @endif
                        @endforeach
                    @endif

                    @if (Session::has('error_agreement'))
                        <span class="error_msg" style="margin-bottom: 10px;">{{ Session::get('error_agreement') }}</span>
                    @endif

                    @if (!empty($global_config->recaptcha_site_key_public) && !empty($global_config->recaptcha_secret_key_public))
                        <div style="margin-bottom: 10px;">
                            <center>
                                <div class="g-recaptcha" data-sitekey="{{ $global_config->recaptcha_site_key_public }}"></div>
                            </center>
                        </div>
                    @endif
                    
                    <div class="button_box">
                        <button class="red_btn">Daftar</button>
                    </div>
                </form>
                
                <div class="misc_box">
                    <span>Punya Akun? <a href="{{ route('web.auth.login') }}">Masuk di sini</a></span>
                </div>
                <div class="other_box">
                    <span>Atau Masuk Dengan</span>
                </div>
                <div class="row_clear sosmed_button">
                    {{-- <a href="{{ route('web.auth.provider', 'facebook') }}" class="facebook_btn"><span>facebook</span></a> --}}
                    <a href="{{ route('web.auth.provider', 'google') }}" class="google_btn"><span>Google</span></a>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('script-plugins')
    @if (!empty($global_config->recaptcha_site_key_public) && !empty($global_config->recaptcha_secret_key_public))
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endif

    <script> 
        $(document).ready(function () {
            $("#registerform").on('submit',function(e) {

                @if (!empty($global_config->recaptcha_site_key_public) && !empty($global_config->recaptcha_secret_key_public))
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
    </script>
@endsection