@php
    if (isset($data)) {
        $title  = 'Ubah Alamat';
        $link   = route('web.buyer.update_address');
    } else {
        $title  = 'Tambah Alamat';
        $link   = route('web.buyer.store_address');
    }

    if (Session::has('from_page')) {
        // SAVE IT TO VARIABLE
        $redirect_to_page = Session::get('from_page');
        
        // THEN FORGET SESSION
        Session::forget('from_page');
    } else {
        $redirect_to_page = route('web.buyer.list_address');
    }
@endphp
@extends('_template_web.master')

@section('title', 'Daftar Alamat')

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
    @include('_template_web.header')
    <section class="white_bg no_categories">
        <div class="section_profile">
            <div class="profile_menu">
                <img class="logo" src="{{ asset('web/images/icon_square.png') }}">
                <div class="container">
                    <ul>
                    <li><a href="{{ route('web.buyer.profile') }}">Profil</a></li>
                        <li><a href="{{ route('web.buyer.list_address') }}" class="active">Daftar Alamat</a></li>
                        <li><a href="{{ route('web.order.history') }}">Riwayat Pesanan</a></li>
                    </ul>
                </div>
            </div>
            <div class="profile_box">
                <div class="container">
                    <h3>{{ $title }}</h3>

                    <form action="{{ $link }}" method="POST" id="submitform">
                        @csrf

                        @if(isset($_GET['from']) && $_GET['from'] == 'process-order')
                        <input type="hidden" name="from" value="{{ $_GET['from'] }}">
                        @endif

                        <div class="form_wrapper form_bg">
                            @if (isset($data)) <input type="hidden" name="id" value="{{ $raw_id }}"> @endif

                            <div class="form_box">
                                <span class="title" for="fullname">Nama Lengkap</span>
                                <input type="text" name="fullname" id="fullname" placeholder="Nama Lengkap" value="{{ isset($data->fullname) ? $data->fullname : old('fullname') }}">
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
                            
                            <div class="form_box">
                                <span class="title" for="phone_number">Nomor Telepon</span>
                                <input type="number" name="phone_number" id="phone_number" placeholder="No Telp" min="0" value="{{ isset($data->phone_number) ? $data->phone_number : old('phone_number') }}">
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

                            <div class="form_box">
                                <span class="title">Label Alamat</span>
                                <input type="text" name="name" placeholder="Contoh: Rumah, Kantor, Kost, dll." value="{{ isset($data->name) ? $data->name : old('name') }}">
                                <span class="error_msg" id="name-error" style="display: none">Label alamat wajib diisi</span>
                                @if (Session::has('error_name'))
                                    <span class="error_msg">{{ Session::get('error_name') }}.</span>
                                @endif

                                @if (count($errors) > 0)
                                    @foreach ($errors->messages() as $key => $error)
                                        @if ($key == 'name')
                                            <span class="error_msg">{{ $error[0] }}</span>
                                        @endif
                                    @endforeach
                                @endif
                            </div>

                            <div class="form_box">
                                <span class="title">Alamat Lengkap</span>
                                <textarea placeholder="Jl. XYZ Blok D No. 21 RT/RW 01/01" name="address_details">{{ isset($data->address_details) ? $data->address_details : old('address_details') }}</textarea>
                                <span class="error_msg" id="address_details-error" style="display: none">Alamat Lengkap wajib diisi</span>
                                @if (Session::has('error_address_details'))
                                    <span class="error_msg">{{ Session::get('error_address_details') }}.</span>
                                @endif

                                @if (count($errors) > 0)
                                    @foreach ($errors->messages() as $key => $error)
                                        @if ($key == 'address_details')
                                            <span class="error_msg">{{ $error[0] }}</span>
                                        @endif
                                    @endforeach
                                @endif
                            </div>

                            <div class="form_box">
                                <span class="title">Catatan</span>
                                <textarea placeholder="Tambahkan catatan jika perlu. Contoh: pagar hitam, sebelah kantor pos, dll." name="remarks">{{ isset($data->remarks) ? $data->remarks : old('remarks') }}</textarea>
                                <span class="error_msg" id="remarks-error" style="display: none">Catatan wajib diisi</span>
                                @if (Session::has('error_remarks'))
                                    <span class="error_msg">{{ Session::get('error_remarks') }}.</span>
                                @endif

                                @if (count($errors) > 0)
                                    @foreach ($errors->messages() as $key => $error)
                                        @if ($key == 'remarks')
                                            <span class="error_msg">{{ $error[0] }}</span>
                                        @endif
                                    @endforeach
                                @endif
                            </div>

                            <div class="form_box">
                                <span class="title">Provinsi</span>
                                <select class="select2" data-placeholder="Pilih Provinsi Kamu" name="province" id="province" onchange="filter_district();">
                                    <option value="">Pilih Provinsi Kamu</option>
                                    @if (isset($provinces[0]))
                                        @foreach ($provinces as $province)
                                            <option value="{{ $province->code }}" @if (isset($data) && $province->code == $data->province_code) selected @endif>
                                                {{ $province->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                <span class="error_msg" id="province-error" style="display: none">Provinsi wajib diisi</span>
                                @if (Session::has('error_province'))
                                    <span class="error_msg">{{ Session::get('error_province') }}.</span>
                                @endif

                                @if (count($errors) > 0)
                                    @foreach ($errors->messages() as $key => $error)
                                        @if ($key == 'province')
                                            <span class="error_msg">{{ $error[0] }}</span>
                                        @endif
                                    @endforeach
                                @endif
                            </div>

                            <div class="form_box">
                                <span class="title">Kota/Kabupaten</span>
                                <select class="select2" data-placeholder="Pilih Kota Kamu" name="district" id="district" onchange="filter_sub_district();" disabled>
                                    @if (isset($data))
                                        <option value="{{ $data->district_code }}">{{ $data->city_name }}</option>
                                    @endif
                                </select>
                                <span class="error_msg" id="district-error" style="display: none">Kota/Kabupaten wajib diisi</span>
                                @if (Session::has('error_district'))
                                    <span class="error_msg">{{ Session::get('error_district') }}.</span>
                                @endif

                                @if (count($errors) > 0)
                                    @foreach ($errors->messages() as $key => $error)
                                        @if ($key == 'district')
                                            <span class="error_msg">{{ $error[0] }}</span>
                                        @endif
                                    @endforeach
                                @endif
                            </div>

                            <div class="form_box">
                                <span class="title">Kecamatan</span>
                                <select class="select2" data-placeholder="Pilih Kecamatan Kamu" name="sub_district" id="sub_district" onchange="filter_village();" disabled>
                                    @if (isset($data))
                                        <option value="{{ $data->sub_district_code }}">{{ $data->sub_district_name }}</option>
                                    @endif
                                </select>
                                <span class="error_msg" id="sub_district-error" style="display: none">Kecamatan wajib diisi</span>
                                @if (Session::has('error_sub_district'))
                                    <span class="error_msg">{{ Session::get('error_sub_district') }}.</span>
                                @endif

                                @if (count($errors) > 0)
                                    @foreach ($errors->messages() as $key => $error)
                                        @if ($key == 'sub_district')
                                            <span class="error_msg">{{ $error[0] }}</span>
                                        @endif
                                    @endforeach
                                @endif
                            </div>

                            <div class="form_box">
                                <span class="title">Desa/Kelurahan</span>
                                <select class="select2" data-placeholder="Pilih Desa/Kelurahan Kamu" name="village" id="village" onchange="filter_postal_code();" disabled>
                                    @if (isset($data))
                                        <option value="{{ $data->village_code }}">{{ $data->village_name }}</option>
                                    @endif
                                </select>
                                <span class="error_msg" id="village-error" style="display: none">Desa/Kelurahan wajib diisi</span>
                                @if (Session::has('error_village'))
                                    <span class="error_msg">{{ Session::get('error_village') }}.</span>
                                @endif

                                @if (count($errors) > 0)
                                    @foreach ($errors->messages() as $key => $error)
                                        @if ($key == 'village')
                                            <span class="error_msg">{{ $error[0] }}</span>
                                        @endif
                                    @endforeach
                                @endif
                            </div>

                            <div class="form_box">
                                <span class="title">Kode Pos</span>
                                <select class="select2" data-placeholder="Pilih Kode Pos Kamu" name="postal_code" id="postal_code" disabled>
                                    @if (isset($data))
                                        <option value="{{ $data->postal_code }}">{{ $data->postal_code }}</option>
                                    @endif
                                </select>
                                <span class="error_msg" id="postal_code-error" style="display: none">Kode Pos wajib diisi</span>
                                @if (Session::has('error_postal_code'))
                                    <span class="error_msg">{{ Session::get('error_postal_code') }}.</span>
                                @endif

                                @if (count($errors) > 0)
                                    @foreach ($errors->messages() as $key => $error)
                                        @if ($key == 'postal_code')
                                            <span class="error_msg">{{ $error[0] }}</span>
                                        @endif
                                    @endforeach
                                @endif
                            </div>

                            <div class="button_wrapper">
                                <button class="red_btn" type="submit">Simpan</button>

                                @if(!empty(Session::get('from_page')))
                                    <a href="{{ $redirect_to_page }}" class="green_btn">Kembali</a>
                                @else
                                    <a href="{{ $redirect_to_page }}" class="green_btn">Kembali</a>
                                @endif
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
        function filter_district() {
            var parent_value    = $('#province').val();

            // Disable dan reset select setelah district
            resetAndDisableSelect('#sub_district', '- {{ ucwords(lang("please choose one", $translations)) }} -');
            resetAndDisableSelect('#village', '- {{ ucwords(lang("please choose one", $translations)) }} -');
            resetAndDisableSelect('#postal_code', '- {{ ucwords(lang("please choose one", $translations)) }} -');
            
            var select2_elm = $('#district');
            
            if (!parent_value) {
                resetAndDisableSelect(select2_elm, '- {{ ucwords(lang("please choose one", $translations)) }} -');
                return;
            }

            $.ajax({
                type: "POST",
                url: "{{ route('helper.filter_district') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    parent: parent_value
                },
                beforeSend: function () {
                    // do something before send the data
                    // set default option - just will be selected in "view"
                    var newOption = new Option("- {{ ucwords('harap tunggu') }} -", "", false, true);
                    select2_elm.append(newOption);
                },
            })
            .done(function (response) {
                // Callback handler that will be called on success
                if (typeof response != 'undefined') {
                    if (response.status == 'true') {
                        // SUCCESS RESPONSE

                        // remove all existing options in select2 element
                        select2_elm.empty();

                        if (response.data != null) {
                            // set default option - just will be selected in "view"
                            var newOption = new Option("- {{ ucwords('pilih salah satu') }} -", "", false, true);
                            select2_elm.append(newOption);
                            
                            // looping the response data to set new options
                            $.each(response.data, function(key, value) {
                                new_data = {
                                    id: value.kode,
                                    text: value.nama
                                };
                                newOption = new Option(new_data.text, new_data.id, false, false);
                                select2_elm.append(newOption);
                            });

                            // reset selected value of select2 element
                            // select2_elm.val(null);

                            // Notify any JS components that the value changed
                            // select2_elm.trigger('change');

                            // enable the select2 element
                            select2_elm.prop('disabled', false);

                            filter_sub_district();
                        } else {
                            // set default option - just will be selected in "view"
                            var newOption = new Option("*{{ strtoupper('tidak ada data') }}", "", false, true);
                            select2_elm.append(newOption);
                            // disable the select2 element
                            select2_elm.prop('disabled', true);
                        }
                    } else {
                        // FAILED RESPONSE
                        alert('ERROR: ' + response.message);
                    }
                } else {
                    alert('Server not respond, please try again.');
                }
            })
            .fail(function (jqXHR, textStatus, errorThrown) {
                // Callback handler that will be called on failure

                // Log the error to the console
                console.error("The following error occurred: " + textStatus, errorThrown);

                alert("The following error occurred: " + textStatus + "\n" + errorThrown);
                // location.reload();
            })
            .always(function () {
                // Callback handler that will be called regardless
                // if the request failed or succeeded
        });
        }

        function filter_sub_district() {
            var parent_value = $('#district').val();

            // Disable dan reset field di bawahnya
            resetAndDisableSelect('#village', '- {{ ucwords(lang("please choose one", $translations)) }} -');
            resetAndDisableSelect('#postal_code', '- {{ ucwords(lang("please choose one", $translations)) }} -');

            var select2_elm = $('#sub_district');

            if (!parent_value) {
                resetAndDisableSelect(select2_elm, '- {{ ucwords(lang("please choose one", $translations)) }} -');
                return;
            }
            
            // Stop function if parent_value is empty/null
            if (!parent_value) {
                // Kosongkan dan disable elemen jika sebelumnya sudah ada data
                select2_elm.empty().prop('disabled', true);
                var newOption = new Option("- {{ ucwords(lang('please choose one', $translations)) }} -", "", false, true);
                select2_elm.append(newOption);
                return; // Jangan lanjut ke AJAX
            }
            $.ajax({
                type: "POST",
                url: "{{ route('helper.filter_sub_district') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    parent: parent_value
                },
                beforeSend: function () {
                    // do something before send the data
                    // set default option - just will be selected in "view"
                    var newOption = new Option("- {{ ucwords(lang('please wait', $translations)) }} -", "", false, true);
                    select2_elm.append(newOption);
                },
            })
            .done(function (response) {
                // Callback handler that will be called on success
                if (typeof response != 'undefined') {
                    if (response.status == 'true') {
                        // SUCCESS RESPONSE

                        // remove all existing options in select2 element
                        select2_elm.empty();

                        if (response.data != null) {
                            // set default option - just will be selected in "view"
                            var newOption = new Option("- {{ ucwords('pilih salah satu') }} -", "", false, true);
                            select2_elm.append(newOption);
                            
                            // looping the response data to set new options
                            $.each(response.data, function(key, value) {
                                new_data = {
                                    id: value.kode,
                                    text: value.nama
                                };
                                newOption = new Option(new_data.text, new_data.id, false, false);
                                select2_elm.append(newOption);
                            });

                            // reset selected value of select2 element
                            // select2_elm.val(null);

                            // Notify any JS components that the value changed
                            // select2_elm.trigger('change');

                            // enable the select2 element
                            select2_elm.prop('disabled', false);

                            filter_village();
                        } else {
                            // set default option - just will be selected in "view"
                            var newOption = new Option("*{{ strtoupper('tidak ada data') }}", "", false, true);
                            select2_elm.append(newOption);
                            // disable the select2 element
                            select2_elm.prop('disabled', true);
                        }
                    } else {
                        // FAILED RESPONSE
                        alert('ERROR: ' + response.message);
                    }
                } else {
                    alert('Server not respond, please try again.');
                }
            })
            .fail(function (jqXHR, textStatus, errorThrown) {
                // Callback handler that will be called on failure

                // Log the error to the console
                console.error("The following error occurred: " + textStatus, errorThrown);

                alert("The following error occurred: " + textStatus + "\n" + errorThrown);
                // location.reload();
            })
            .always(function () {
                // Callback handler that will be called regardless
                // if the request failed or succeeded
            });
        }

        function filter_village() {
            var parent_value = $('#sub_district').val();

            // Disable postal code
            resetAndDisableSelect('#postal_code', '- {{ ucwords(lang("please choose one", $translations)) }} -');

            var select2_elm = $('#village');

            if (!parent_value) {
                resetAndDisableSelect(select2_elm, '- {{ ucwords(lang("please choose one", $translations)) }} -');
                return;
            }
            
            // Stop function if parent_value is empty/null
            if (!parent_value) {
                // Kosongkan dan disable elemen jika sebelumnya sudah ada data
                select2_elm.empty().prop('disabled', true);
                var newOption = new Option("- {{ ucwords(lang('please choose one', $translations)) }} -", "", false, true);
                select2_elm.append(newOption);
                return; // Jangan lanjut ke AJAX
            }

            $.ajax({
                type: "POST",
                url: "{{ route('helper.filter_village') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    parent: parent_value
                },
                beforeSend: function () {
                    // do something before send the data
                    // set default option - just will be selected in "view"
                    var newOption = new Option("- {{ ucwords(lang('please wait', $translations)) }} -", "", false, true);
                    select2_elm.append(newOption);
                },
            })
            .done(function (response) {
                // Callback handler that will be called on success
                if (typeof response != 'undefined') {
                    if (response.status == 'true') {
                        // SUCCESS RESPONSE

                        // remove all existing options in select2 element
                        select2_elm.empty();

                        if (response.data != null) {
                            // set default option - just will be selected in "view"
                            var newOption = new Option("- {{ ucwords('pilih salah satu') }} -", "", false, true);
                            select2_elm.append(newOption);
                            
                            // looping the response data to set new options
                            $.each(response.data, function(key, value) {
                                new_data = {
                                    id: value.kode,
                                    text: value.nama
                                };
                                newOption = new Option(new_data.text, new_data.id, false, false);
                                select2_elm.append(newOption);
                            });

                            // reset selected value of select2 element
                            // select2_elm.val(null);

                            // Notify any JS components that the value changed
                            // select2_elm.trigger('change');

                            // enable the select2 element
                            select2_elm.prop('disabled', false);

                            filter_postal_code();
                        } else {
                            // set default option - just will be selected in "view"
                            var newOption = new Option("*{{ strtoupper('tidak ada data') }}", "", false, true);
                            select2_elm.append(newOption);
                            // disable the select2 element
                            select2_elm.prop('disabled', true);
                        }
                    } else {
                        // FAILED RESPONSE
                        alert('ERROR: ' + response.message);
                    }
                } else {
                    alert('Server not respond, please try again.');
                }
            })
            .fail(function (jqXHR, textStatus, errorThrown) {
                // Callback handler that will be called on failure

                // Log the error to the console
                console.error("The following error occurred: " + textStatus, errorThrown);

                alert("The following error occurred: " + textStatus + "\n" + errorThrown);
                // location.reload();
            })
            .always(function () {
                // Callback handler that will be called regardless
                // if the request failed or succeeded
            });
        }

        function filter_postal_code() {
            var parent_value = $('#village').val();
            var select2_elm = $('#postal_code');
            
            // Stop function if parent_value is empty/null
            if (!parent_value) {
                // Kosongkan dan disable elemen jika sebelumnya sudah ada data
                select2_elm.empty().prop('disabled', true);
                var newOption = new Option("- {{ ucwords(lang('please choose one', $translations)) }} -", "", false, true);
                select2_elm.append(newOption);
                return; // Jangan lanjut ke AJAX
            }

            $.ajax({
                type: "POST",
                url: "{{ route('helper.filter_postal_code') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    parent: parent_value
                },
                beforeSend: function () {
                    // do something before send the data
                    // set default option - just will be selected in "view"
                    var newOption = new Option("- {{ ucwords(lang('please wait', $translations)) }} -", "", false, true);
                    select2_elm.append(newOption);
                },
            })
            .done(function (response) {
                // Callback handler that will be called on success
                if (typeof response != 'undefined') {
                    if (response.status == 'true') {
                        // SUCCESS RESPONSE

                        // remove all existing options in select2 element
                        select2_elm.empty();

                        if (response.data != null) {
                            // set default option - just will be selected in "view"
                            var newOption = new Option("- {{ ucwords('pilih salah satu') }} -", "", false, true);
                            select2_elm.append(newOption);
                            
                            // looping the response data to set new options
                            $.each(response.data, function(key, value) {
                                new_data = {
                                    id: value.kode,
                                    text: value.nama
                                };
                                newOption = new Option(new_data.text, new_data.id, false, false);
                                select2_elm.append(newOption);
                            });

                            // reset selected value of select2 element
                            // select2_elm.val(null);

                            // Notify any JS components that the value changed
                            // select2_elm.trigger('change');

                            // enable the select2 element
                            select2_elm.prop('disabled', false);
                        } else {
                            // set default option - just will be selected in "view"
                            var newOption = new Option("*{{ strtoupper('tidak ada data') }}", "", false, true);
                            select2_elm.append(newOption);
                            // disable the select2 element
                            select2_elm.prop('disabled', true);
                        }
                    } else {
                        // FAILED RESPONSE
                        alert('ERROR: ' + response.message);
                    }
                } else {
                    alert('Server not respond, please try again.');
                }
            })
            .fail(function (jqXHR, textStatus, errorThrown) {
                // Callback handler that will be called on failure

                // Log the error to the console
                console.error("The following error occurred: " + textStatus, errorThrown);

                alert("The following error occurred: " + textStatus + "\n" + errorThrown);
                // location.reload();
            })
            .always(function () {
                // Callback handler that will be called regardless
                // if the request failed or succeeded
            });
        }

        function resetAndDisableSelect(selector, placeholderText) {
            var select2_elm = $(selector);
            select2_elm.empty().prop('disabled', true);
            var newOption = new Option(placeholderText, "", false, true);
            select2_elm.append(newOption);
        }

        $(document).ready(function () {
            $("#submitform").on('submit',function(e) {
                $('.error_msg').hide();

                var data_form = $(this).serialize();
                var split_data = data_form.split('&');
                var continue_step = true;
                $.each(split_data , function (index, value) {
                    var split_tmp = value.split('=');
                    
                    var elm_name = 'name';
                    if (split_tmp[0] == elm_name && split_tmp[1] == '') {
                        continue_step = false;
                        $('#'+elm_name+'-error').show();
                    }

                    var elm_name = 'address_details';
                    if (split_tmp[0] == elm_name && split_tmp[1] == '') {
                        continue_step = false;
                        $('#'+elm_name+'-error').show();
                    }
                });

                var elm_name = 'province';
                if ($('#'+elm_name).val() == null || $('#'+elm_name).val() == '') {
                    continue_step = false;
                    $('#'+elm_name+'-error').show();
                }

                var elm_name = 'district';
                if ($('#'+elm_name).val() == null || $('#'+elm_name).val() == '') {
                    continue_step = false;
                    $('#'+elm_name+'-error').show();
                }

                var elm_name = 'sub_district';
                if ($('#'+elm_name).val() == null || $('#'+elm_name).val() == '') {
                    continue_step = false;
                    $('#'+elm_name+'-error').show();
                }

                var elm_name = 'village';
                if ($('#'+elm_name).val() == null || $('#'+elm_name).val() == '') {
                    continue_step = false;
                    $('#'+elm_name+'-error').show();
                }

                var elm_name = 'postal_code';
                if ($('#'+elm_name).val() == null || $('#'+elm_name).val() == '') {
                    continue_step = false;
                    $('#'+elm_name+'-error').show();
                }

                if (!continue_step) {
                    return false;
                }

                return true;
            });
        });
    </script>
@endsection