@extends('_template_adm.master')

@php
    use App\Libraries\Helper;

    $pagetitle      = ucwords(lang('buyer', $translations));
    if(isset($data)){
        $pagetitle .= ' ('.ucwords(lang('Detail', $translations)).')';
        $link       = route('admin.buyer.update', $raw_id);
    }else {
        $pagetitle .= ' ('.ucwords(lang('new', $translations)).')';
        $link       = route('admin.buyer.store');
        $data       = null;
    }
@endphp

@section('title', $pagetitle)

@section('content')
    <div class="">
        <!-- message info -->
        @include('_template_adm.message')

        <div class="page-title">
            <div class="title_left">
                <h3>{{ $pagetitle }}</h3>
            </div>
        </div>

        <div class="clearfix"></div>

        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>{{ ucwords(lang('form details', $translations)) }}</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <br />
                        <form id="form_data" class="form-horizontal form-label-left" action="{{ $link }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            @php
                                // set_input_form($type, $input_name, $label_name, $data, $errors, $required = false, $config = null)
                                $config             = new \stdClass();
                                $config->attributes = 'readonly';
                                echo set_input_form('text', 'fullname', ucwords(lang('full name', $translations)), $data, $errors, false, $config);

                                $config                 = new \stdClass();
                                $config->placeholder    = '8123456789';
                                // $config->input_addon    = '62';
                                $config->attributes     = 'readonly';
                                echo set_input_form('number_only', 'phone_number', ucwords(lang('Phone', $translations)), $data, $errors, false, $config);

                                $config             = new \stdClass();
                                $config->attributes = 'readonly';
                                echo set_input_form('text', 'email', ucwords(lang('email', $translations)), $data, $errors, false, $config);

                                // $config                 = new \stdClass();
                                // $config->value          = '';
                                // $config->attributes     = 'autocomplete="off"';
                                // $config->viewable       = true;
                                // echo set_input_form('password', 'password', ucwords(lang('password', $translations)), $data, $errors, false, $config);

                                // $config                 = new \stdClass();
                                // $config->viewable       = true;
                                // echo set_input_form('password', 'password_confirmation', ucwords(lang('password confirmation', $translations)), $data, $errors, false, $config);

                                $config                 = new \stdClass();
                                $config->attributes     = 'readonly';
                                $config->placeholder    = 'dd/mm/yyyy';
                                echo set_input_form('text', 'birth_date', ucwords(lang('birth date', $translations)), $data, $errors, false, $config);

                                // $config                 = new \stdClass();
                                // $config->default        = 'checked';
                                // $config->attributes     = 'readonly';
                                // echo set_input_form('switch', 'status', ucfirst(lang('status', $translations)), $data, $errors, false, $config);

                                // only show when edit
                                if ($data) {
                                    $time_ago           = Helper::time_ago(strtotime($data->created_at), lang('ago', $translations), Helper::get_periods($translations));
                                    $config             = new \stdClass();
                                    $config->attributes = 'readonly';
                                    $config->value      = Helper::locale_timestamp($data->created_at) . ' - ' . $time_ago;
                                    echo set_input_form('text', 'created_at', ucwords(lang('created at', $translations)), $data, $errors, false, $config);
                                    
                                    $time_ago           = Helper::time_ago(strtotime($data->updated_at), lang('ago', $translations), Helper::get_periods($translations));
                                    $config             = new \stdClass();
                                    $config->attributes = 'readonly';
                                    $config->value      = Helper::locale_timestamp($data->updated_at) . ' - ' . $time_ago;
                                    echo set_input_form('text', 'updated_at', ucwords(lang('last updated at', $translations)), $data, $errors, false, $config);
                                }
                            @endphp
                            
                            <div class="ln_solid"></div>

                            <div class="form-group">
                                {{-- @php
                                    echo set_input_form('switch', 'stay_on_page', ucfirst(lang('stay on this page after submitting', $translations)), $data, $errors, false);
                                @endphp --}}
                                <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                    {{-- <button type="submit" class="btn btn-success"><i class="fa fa-save"></i>&nbsp; 
                                        @if (isset($data))
                                            {{ ucwords(lang('save', $translations)) }}
                                        @else
                                            {{ ucwords(lang('submit', $translations)) }}
                                        @endif
                                    </button> --}}
                                    <a href="{{ route('admin.buyer') }}" class="btn btn-default"><i class="fa fa-times"></i>&nbsp; 
                                        @if (isset($data))
                                            {{ ucwords(lang('close', $translations)) }}
                                        @else
                                            {{ ucwords(lang('cancel', $translations)) }}
                                        @endif
                                    </a>

                                    {{-- @if (isset($raw_id))
                                        | <span class="btn btn-danger" onclick="$('#form_delete').submit()"><i class="fa fa-trash"></i></span>
                                    @endif --}}
                                </div>
                            </div>

                        </form>

                        @if (isset($raw_id))
                            <form id="form_delete" action="{{ route('admin.buyer.delete') }}" method="POST" onsubmit="return confirm('{!! lang('Are you sure to delete this #item?', $translations, ['#item' => 'data']) !!}');" style="display: none">
                                @csrf
                                <input type="hidden" name="id" value="{{ $raw_id }}">
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
    <!-- Switchery -->
    @include('_vendors.switchery.css')
    <!-- Select2 -->
    @include('_vendors.select2.css')
    <!-- bootstrap-datetimepicker -->
    @include('_vendors.datetimepicker.css')
@endsection

@section('script')
    <!-- Switchery -->
    @include('_vendors.switchery.script')
    <!-- Select2 -->
    @include('_vendors.select2.script')
    <!-- bootstrap-datetimepicker -->
    @include('_vendors.datetimepicker.script')

    <script>
        function filter_district() {
            var parent_value = $('#province_code').val();
            var select2_elm = $('#district_code');
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
                                var newOption = new Option("- {{ ucwords(lang('please choose one', $translations)) }} -", "", false, true);
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
                                var newOption = new Option("*{{ strtoupper(lang('no data available', $translations)) }}", "", false, true);
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
            var parent_value = $('#district_code').val();
            $.ajax({
                type: "POST",
                url: "{{ route('helper.filter_sub_district') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    parent: parent_value
                },
                beforeSend: function () {
                    // do something before send the data
                },
            })
                .done(function (response) {
                    // Callback handler that will be called on success
                    if (typeof response != 'undefined') {
                        if (response.status == 'true') {
                            // SUCCESS RESPONSE
                            var select2_elm = $('#sub_district_code');

                            // remove all existing options in select2 element
                            select2_elm.empty();

                            if (response.data != null) {
                                // set default option - just will be selected in "view"
                                var newOption = new Option("- {{ ucwords(lang('please choose one', $translations)) }} -", "", false, true);
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
                                var newOption = new Option("*{{ strtoupper(lang('no data available', $translations)) }}", "", false, true);
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
            var parent_value = $('#sub_district_code').val();
            $.ajax({
                type: "POST",
                url: "{{ route('helper.filter_village') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    parent: parent_value
                },
                beforeSend: function () {
                    // do something before send the data
                },
            })
                .done(function (response) {
                    // Callback handler that will be called on success
                    if (typeof response != 'undefined') {
                        if (response.status == 'true') {
                            // SUCCESS RESPONSE
                            var select2_elm = $('#village_code');

                            // remove all existing options in select2 element
                            select2_elm.empty();

                            if (response.data != null) {
                                // set default option - just will be selected in "view"
                                var newOption = new Option("- {{ ucwords(lang('please choose one', $translations)) }} -", "", false, true);
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
                                var newOption = new Option("*{{ strtoupper(lang('no data available', $translations)) }}", "", false, true);
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
            var parent_value = $('#village_code').val();
            $.ajax({
                type: "POST",
                url: "{{ route('helper.filter_postal_code') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    parent: parent_value
                },
                beforeSend: function () {
                    // do something before send the data
                },
            })
                .done(function (response) {
                    // Callback handler that will be called on success
                    if (typeof response != 'undefined') {
                        if (response.status == 'true') {
                            // SUCCESS RESPONSE
                            var select2_elm = $('#postal_code');

                            // remove all existing options in select2 element
                            select2_elm.empty();

                            if (response.data != null) {
                                // set default option - just will be selected in "view"
                                var newOption = new Option("- {{ ucwords(lang('please choose one', $translations)) }} -", "", false, true);
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
                                var newOption = new Option("*{{ strtoupper(lang('no data available', $translations)) }}", "", false, true);
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
    </script>
@endsection