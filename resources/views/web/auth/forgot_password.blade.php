@extends('_template_web.master')

@section('css') @endsection

@section('js-head') @endsection

@section('content')
<section class="section_form">
    <div class="container">
        <div class="logo_box"><h1 title="Lokal Korner"><img src="{{ asset('/web/images/logo_hijau.png') }}"></h1></div>
        <h2>Lupa Password?</h2>

        <div class="form_wrapper form_bg">
            <form action="{{ route('web.auth.forgot_password.submit') }}" method="POST">
                @csrf
                
                <div class="form_box">
                    <input type="text" placeholder="Ketik alamat email di sini" name="email">
                    @if (Session::has('error_email'))
                        <span class="error_msg">{{ Session::get('error_email') }}.</span>
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
                <div class="misc_box font_15">
                    Link untuk buat password baru akan dikirim ke email ini.
                </div>
                <div class="button_box">
                    <button class="red_btn" type="submit">Kirim Email Verifikasi</button>
                </div>
            </form>
            <!--<div class="misc_box">
                <span>Belum terima email? <a href="#">Kirim Ulang</a></span>
            </div>-->
        </div>
    </div>
</section>
@endsection

@section('js')
    <script></script>
@endsection