@extends('_template_web.master')

@section('title', 'Profile')

@section('css-plugins')
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/jquery-ui.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/jquery-ui.theme.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('web/css/select2.css') }}">
@endsection

@section('script-plugins')
    <script type="text/javascript" src="{{ asset('web/js/jquery-ui.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('web/js/select2.full.min.js') }}"></script>
@endsection

@section('content')
    @include('_template_web.header_with_categories')
    @include('_template_web.alert_popup')
    
    <section class="white_bg no_categories">
        <div class="section_profile">
            <div class="profile_menu">
                <img class="logo" src="{{ asset('web/images/icon_square.png') }}">
                <div class="container">
                    <ul>
                        <li><a href="{{ route('web.buyer.profile') }}" class="active">Profil</a></li>
                        <li><a href="{{ route('web.buyer.list_address') }}">Daftar Alamat</a></li>
                        <li><a href="{{ route('web.order.history') }}">Riwayat Pesanan</a></li>
                    </ul>
                </div>
            </div>

            <div class="profile_box">
                <div class="container">
                    <h3>Profil</h3>
                    <form action="{{ route('web.buyer.profile_update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form_wrapper form_bg">
                            <div class="profile_picture">
                                <div class="pp_box">
                                    @if (!empty($data->avatar))
                                        <img src="{{ $data->avatar }}" class="profile_img">
                                    @else
                                        {{-- <img src="" class="profile_img"> --}}
                                    @endif
                                    <input type="file" name="photo" class="change_img">
                                </div>
                                @if (!isset($data->avatar))
                                    <span class="notes">*Maks. 2 MB</span>
                                @endif
                                @if (Session::has('error_avatar'))
                                    <span class="error_msg">{{ Session::get('error_avatar') }}</span>
                                @endif

                                @if (count($errors) > 0)
                                    @foreach ($errors->messages() as $key => $error)
                                        @if ($key == 'photo')
                                            <span class="error_msg">{{ $error[0] }}</span>
                                        @endif
                                    @endforeach
                                @endif
                            </div>

                            <div class="form_box">
                                <span class="title" for="fullname">Nama Lengkap</span>
                                <input type="text" name="fullname" id="fullname" value="{{ $data->fullname }}">
                                @if (Session::has('error_fullname'))
                                    <span class="error_msg">{{ Session::get('error_fullname') }}.</span>
                                @endif

                                @if (count($errors) > 0)
                                    @foreach ($errors->messages() as $key => $error)
                                        @if ($key == 'fullname')
                                            <span class="error_msg">{{ $error[0] }}</span>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                            
                            @php
                                if (isset($data->birth_date)) {
                                    $data->birth_date   = date('d-m-Y', strtotime($data->birth_date));
                                    $data->birth_day    = date('d', strtotime($data->birth_date));
                                    $data->birth_month  = date('m', strtotime($data->birth_date));
                                    $data->birth_year   = date('Y', strtotime($data->birth_date));
                                }
                            @endphp
                            <div class="form_box">
                                <span class="title">Tanggal lahir</span>
                                <div class="row_clear">
                                    <div class="date_box">
                                        <select class="select3" data-placeholder="Hari" name="birth_day">
                                            <option></option>
                                            @for ($d = 1; $d <= 31; $d++)
                                                <option value="{{ $d }}" @if ($d == $data->birth_day) selected @endif >{{ $d }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="date_box">
                                        <select class="select3" data-placeholder="Bulan" name="birth_month">
                                            <option></option>
                                            @for ($m = 1; $m <= 12; $m++)
                                                <option value="{{ $m }}" @if ($m == $data->birth_month) selected @endif >{{ $m }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="date_box">
                                        <select class="select3" data-placeholder="Tahun" name="birth_year">
                                            <option></option>
                                            @for ($y = date('Y'); $y >= 1900; $y--)
                                                <option value="{{ $y }}" @if ($y == $data->birth_year) selected @endif >{{ $y }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                                <!-- <input type="text" class="datepicker" name="birth_date" id="birth_date" value="{{ $data->birth_date }}"> -->
                                @if (Session::has('error_birth_date'))
                                    <span class="error_msg">{{ Session::get('error_birth_date') }}.</span>
                                @endif

                                @if (count($errors) > 0)
                                    @foreach ($errors->messages() as $key => $error)
                                        @if ($key == 'birth_date' || $key == 'birth_day' || $key == 'birth_month' || $key == 'birth_year')
                                            <span class="error_msg">{{ $error[0] }}</span>
                                            @php break; @endphp
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                            @if (!isset($buyer_provider[0]))
                                <div class="form_box">
                                    <span class="title" id="email">Email</span>
                                    <div class="row_clear">
                                        <input type="text" id="email" value="{{ $data->email }}" disabled>
                                        <a href="{{ route('web.auth.change_email') }}" class="green_btn">Ubah</a>
                                    </div>
                                </div>
                                <div class="form_box">
                                    <span class="title">Password</span>
                                    <div class="row_clear">
                                        <input type="password" name="password" value="boomboom" disabled="disabled">
                                        <a href="{{ route('web.auth.change_password') }}" class="green_btn">Ubah</a>
                                    </div>
                                </div>
                            @endif
                            <div class="form_box">
                                <span class="title">Jenis Kelamin</span>
                                <div class="row_clear">
                                    <div class="inline_block">
                                        <label>
                                            <input type="radio" name="gender" value="laki-laki" @if ($data && $data->gender == 'laki-laki') checked @endif>
                                            <span>Laki - Laki</span>
                                        </label>
                                    </div>
                                    <div class="inline_block">
                                        <label>
                                            <input type="radio" name="gender" value="perempuan" @if ($data && $data->gender == 'perempuan') checked @endif>
                                            <span>Perempuan</span>
                                        </label>
                                    </div>
                                    @if (Session::has('error_gender'))
                                        <span class="error_msg">{{ Session::get('error_gender') }}.</span>
                                    @endif

                                    @if (count($errors) > 0)
                                        @foreach ($errors->messages() as $key => $error)
                                            @if ($key == 'gender')
                                                <span class="error_msg">{{ $error[0] }}</span>
                                            @endif
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                            <div class="form_box">
                                <span class="title" for="phone_number">Nomor Telepon</span>
                                <input type="number" name="phone_number" id="phone_number" min="0" value="{{ $data->phone_number }}">
                                @if (Session::has('error_phone_number'))
                                    <span class="error_msg">{{ Session::get('error_phone_number') }}.</span>
                                @endif

                                @if (count($errors) > 0)
                                    @foreach ($errors->messages() as $key => $error)
                                        @if ($key == 'phone_number')
                                            <span class="error_msg">{{ $error[0] }}</span>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                            <div class="button_wrapper">
                                <button class="red_btn" type="submit" id="submit_btn">Simpan</button>
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
        @if (session('error_profile'))
            show_popup_failed();
        @endif

        @if (session('success'))
            show_popup_success();
        @endif
    </script>
@endsection