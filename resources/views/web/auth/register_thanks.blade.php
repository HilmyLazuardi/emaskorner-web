@extends('_template_web.master')

@section('title', 'Terima kasih telah mendaftar')

@section('content')
    @include('_template_web.header')

    <section class="section_form">
        <div class="section_thanks">
            <div class="container">
                <img src="{{ asset('web/images/icon_msg.png') }}">
                <h3>Email Verifikasi Terkirim!</h3>
                <p>Cek link verifikasi di inbox/spam email baru kamu.</p>
                
                <div class="misc_box">
                    <span>Tidak terima email? Klik tombol Kirim Ulang di bawah.</span>
                </div>
                <div class="button_wrapper" id="resend_email">
                    <a href="#" class="red_btn" id="try_resend_email">Kirim Ulang</a>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('footer-script')
    <script>
        $(document).ready(function() {
            var retry = 0;
            
            $('#try_resend_email').click(function() {
                if (retry >= 2) {
                    // REMOVE THEN EXIT
                    $('#resend_email').remove();
                    return;
                }
                
                $.ajax({
                    type: "POST",
                    url: "{{ route('web.auth.resend_email_register') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        email_token: "{{ Session::get('email_token') }}"
                    },
                    beforeSend: function () {
                        // do something before send the data
                    },
                })
                .done(function (response) {
                    // Callback handler that will be called on success
                    if (typeof response != 'undefined') {
                        if (response.status == true) {
                            // INCREMENT RETRY
                            retry++;

                            // SHOW ALERT
                            alert(response.message);
                            aaaa
                            // THEN MUTE BUTTON FOR 30s
                        } else {
                            // FAILED RESPONSE
                            alert(response.message);
                        }
                    } else {
                        alert('Server not respond, please try again.');
                    }
                })
                .fail(function (jqXHR, textStatus, errorThrown) {
                    // Callback handler that will be called on failure

                    // Log the error to the console
                    console.error("The following error occurred: " + textStatus, errorThrown);

                    alert("Gagal mengirim ulang email, mohon coba lagi.");
                })
                .always(function () {
                    // Callback handler that will be called regardless
                });
            });
        });

    </script>
@endsection