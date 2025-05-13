@extends('_template_web.master')

@section('title', 'Selamat berbelanja')

@section('content')
    @include('_template_web.header')

    <section class="no_categories white_bg">
        <div class="section_profile">
            <div class="profile_box custom_two">
                <div class="container">
                    <img class="logo_sukses" src="{{ asset('web/images/checklist_big.png') }}">
                    <h3>Terima kasih sudah verifikasi akun kamu. <br>Selamat belanja!</h3>
                    <div class="button_wrapper">
                        <a href="{{ route('web.auth.login') }}" class="red_btn">Masuk</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--<section class="section_form">
        <div class="section_thanks">
            <div class="container">
                <img src="{{ asset('web/images/checklist_big.png') }}">
                <h3>Terima kasih telah memverifikasi akun Anda.<br>Selamat berbelanja.</h3>
                
                <div class="button_wrapper">
                    <a href="{{ route('web.auth.login') }}" class="red_btn">Masuk</a>
                </div>
            </div>
        </div>
    </section>-->
@endsection