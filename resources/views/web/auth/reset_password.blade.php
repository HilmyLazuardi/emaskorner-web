@extends('_template_web.master')

@section('css') @endsection

@section('js-head') @endsection

@section('content')
<section class="section_form">
    <div class="container">
        <div class="logo_box"><h1 title="Lokal Korner"><img src="{{ asset('/web/images/logo_hijau.png') }}"></h1></div>
        <h2>Ganti Password</h2>
        <div class="form_wrapper form_bg">
            <form action="{{ route('web.auth.reset_password.submit') }}" method="POST">
                @csrf
                
                <div class="form_box">
                    <input type="text" placeholder="Email" value="{{ $email }}" readonly>
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
                        <span class="error_msg">{{ Session::get('error_password') }}.</span>
                    @endif
                </div>

                <div class="button_box">
                    <button class="red_btn" type="submit">Simpan</button>
                    <button class="green_btn" type="submit">Kembali</button>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection

@section('js')
    <script></script>
@endsection