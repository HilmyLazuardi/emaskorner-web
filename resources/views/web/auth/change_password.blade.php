@extends('_template_web.master')

@section('title', 'Ubah Password')

@section('content')
    @include('_template_web.header')
    @include('_template_web.alert_popup')
    
    <section class="white_bg no_categories">
        <div class="section_profile">
            <div class="profile_box">
                <div class="container">
                    <img class="logo" src="{{ asset('web/images/icon_square.png') }}">

                    <h3>Ganti Password?</h3>
                    <form action="{{ route('web.auth.change_password.submit') }}" method="POST">
                        @csrf
                        <div class="form_wrapper form_bg">
                            <div class="form_box">
                                <input type="password" name="old_password" id="old_password" placeholder="Masukan Password Saat ini">
                                @if (Session::has('error_old_password'))
                                    <span class="error_msg">{{ Session::get('error_old_password') }}.</span>
                                @endif

                                @if (count($errors) > 0)
                                    @foreach ($errors->messages() as $key => $error)
                                        @if ($key == 'old_password')
                                            <span class="error_msg">{{ $error[0] }}</span>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                            <div class="form_box">
                                <input type="password" name="password" placeholder="Password Baru">
                                @if (Session::has('error_password'))
                                    <span class="error_msg">{{ Session::get('error_password') }}.</span>
                                @endif
                            </div>
                            <div class="form_box">
                                <input type="password" name="password_confirmation" placeholder="Ulangi Password Baru" id="password_confirmation">
                                @if (count($errors) > 0)
                                    @foreach ($errors->messages() as $key => $error)
                                        @if ($key == 'password')
                                            <span class="error_msg">{{ $error[0] }}</span>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                            <div class="button_wrapper">
                                <button class="red_btn" type="submit">Lanjut</button>
                                <a href="{{ route('web.buyer.profile') }}" class="green_btn">Kembali</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('footer-script')
    <script>
        @if (session('success'))
            show_popup_success();
        @endif
    </script>
@endsection