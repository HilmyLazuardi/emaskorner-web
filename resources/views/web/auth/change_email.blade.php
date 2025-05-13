@extends('_template_web.master')

@section('title', 'Ubah Email')

@section('content')
    @include('_template_web.header')
    
    <section class="white_bg no_categories">
        <div class="section_profile">
            <div class="profile_box">
                <div class="container">
                    <img class="logo" src="{{ asset('web/images/icon_square.png') }}">

                    <h3>Ganti Email</h3>
                    <input type="hidden" id="step" value="1">
                    <div class="form_wrapper form_bg">
                        <div class="form_box form_password">
                            <input type="password" id="password" placeholder="Ketik password Anda di sini">
                        </div>

                        <div class="form_box form_email">
                        </div>

                        <div class="button_wrapper">
                            <button type="button" class="red_btn" id="send">Lanjut</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('script-plugins')
    <script>
        $(document).ready(function() {
            $('#send').click(function() {
                var CSRF_TOKEN  = '{{ csrf_token() }}';

                // CHECK LINK
                if ($('#step').val() == '1') {
                    $.ajax({
                        type: "POST",
                        url: "{{ route('web.auth.ajax_check_password') }}",
                        data: {
                            _token          : CSRF_TOKEN,
                            password        : $('#password').val(),
                            user_id         : "{{ Session::get('buyer')->id }}",
                            user_email      : "{{ Session::get('buyer')->email }}",
                            user_new_email  : $('#new_email').val()
                        },
                        dataType: "json",
                        beforeSend: function() {}
                    })
                    .done(function(response) {
                        if (typeof response != undefined) {
                            if (response.status) {
                                // HIDE PASSWORD SECTION
                                $('.form_password').hide();

                                // CHANGE TO STEP 2
                                $('#step').val(2);

                                // THEN APPEND NEW HTML
                                $('.form_email').append(`<input type="email" id="new_email" placeholder="Ketik email baru Anda di sini">`);
                            } else {
                                if (response.data != undefined) {
                                    var html_errors = '';
                                    for (i = 0; i < response.data.length; ++i) {
                                        html_errors += response.data[i] + ' ';
                                    }
                                    alert('ERROR: ' + html_errors);
                                } else {
                                    alert('ERROR: ' + response.message);
                                }
                            }
                        } else {
                            alert('Maaf, respon server tidak dikenali. Mohon coba lagi atau refresh halaman ini.');
                        }
                    })
                    .fail(function() {
                        alert('Maaf, gagal menghubungi server. Mohon coba lagi atau refresh halaman ini.');
                    })
                    .always(function() {});
                } else {
                    $.ajax({
                        type: "POST",
                        url: "{{ route('web.auth.ajax_change_email') }}",
                        data: {
                            _token          : CSRF_TOKEN,
                            password        : $('#password').val(),
                            user_id         : "{{ Session::get('buyer')->id }}",
                            user_email      : "{{ Session::get('buyer')->email }}",
                            user_new_email  : $('#new_email').val()
                        },
                        dataType: "json",
                        beforeSend: function() {}
                    })
                    .done(function(response) {
                        if (typeof response != undefined) {
                            if (response.status) {
                                // REDIRECT TO PAGE SUCCESS CHANGE EMAIL
                                alert(response.message);
                                window.location.href = "{{ route('web.auth.change_email_success')}}";
                            } else {
                                if (response.data != undefined) {
                                    var html_errors = '';
                                    for (i = 0; i < response.data.length; ++i) {
                                        html_errors += response.data[i] + ' ';
                                    }
                                    alert('ERROR: ' + html_errors);
                                } else {
                                    alert('ERROR: ' + response.message);
                                }
                            }
                        } else {
                            alert('Maaf, respon server tidak dikenali. Mohon coba lagi atau refresh halaman ini.');
                        }
                    })
                    .fail(function() {
                        alert('Maaf, gagal menghubungi server. Mohon coba lagi atau refresh halaman ini.');
                    })
                    .always(function() {});
                }
            });
        });
    </script>
@endsection