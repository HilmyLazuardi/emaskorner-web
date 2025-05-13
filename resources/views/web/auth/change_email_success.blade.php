@extends('_template_web.master')

@section('title', 'Ubah Email Berhasil')

@section('content')
    @include('_template_web.header')

    <section class="section_form">
        <div class="section_thanks">
            <div class="container">
                <img src="{{ asset('web/images/icon_msg.png') }}">
                <h3>Silahkan cek inbox/spam<br>kami telah mengirimkan<br>verifikasi ke emailmu</h3>
                
                {{-- TODO jika setelah 30 detik tidak mendapatkan email, tampilkan tombol resend email --}}
                <div class="misc_box">
                    <span>Tidak Menerima Email?<br>Kirim Ulang Email Verifikasi</span>
                </div>
                <div class="button_wrapper">
                    <a href="#" class="red_btn" id="send">Kirim</a>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('script-plugins')
    <script>
        $(document).ready(function() {
            $('#send').click(function() {
                $.ajax({
                    type: "POST",
                    url: "{{ route('web.auth.resend_change_email') }}",
                    data: {
                        _token  : "{{ csrf_token() }}",
                        id      : "{{ Session::get('change_email_id') }}"
                    },
                    dataType: "json",
                    beforeSend: function() {}
                })
                .done(function(response) {
                    if (typeof response != undefined) {
                        if (response.status) {
                            alert(response.message);
                        } else {
                            alert(response.message);
                        }
                    } else {
                        alert('Maaf, respon server tidak dikenali. Mohon coba lagi atau refresh halaman ini.');
                    }
                })
                .fail(function() {
                    alert('Maaf, gagal menghubungi server. Mohon coba lagi atau refresh halaman ini.');
                })
                .always(function() {});
            });
        });
    </script>
@endsection