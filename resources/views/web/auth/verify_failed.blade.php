@extends('_template_web.master')

@section('title', 'Link expired')

@section('content')
    @include('_template_web.header')
    <section class="no_categories white_bg">
        <div class="section_profile">
            <div class="profile_box custom_two">
                <div class="container">
                    <img class="logo_sukses" src="{{ asset('web/images/warning_big.png') }}">
                    <h3>Mohon maaf <br>Link anda sudah expired</h3>
                    <span style="margin:0 0 20px;display: block;">Silahkan hubungi kami kembali untuk mendapatkan link baru</span>
                    <div class="button_wrapper custom_one">
                        <a href="#" class="red_btn">Hubungi Kami</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- <section class="section_form">
        <div class="section_thanks">
            <div class="container">
                <img src="{{ asset('web/images/warning_big.png') }}">
                <h3>Mohon maaf<br>Link Anda telah kadaluarsa atau tidak valid.</h3>
                
                {{-- <div class="misc_box">
                    <span>Silahkan klik tombol di bawah ini untuk mendapatkan link baru</span>
                </div>
                <div class="button_wrapper">
                    <a href="{{ route('web.auth.login') }}" class="red_btn">Masuk</a>
                </div> --}}
            </div>
        </div>
    </section> -->
@endsection