@extends('_template_adm.master')

@php
    use App\Libraries\Helper;

    $pagetitle      = ucwords(lang('seller', $translations));
    if(isset($data)){
        $pagetitle .= ' ('.ucwords(lang('edit', $translations)).')';
        $link       = route('admin.seller.update', $raw_id);
    }else {
        $pagetitle .= ' ('.ucwords(lang('new', $translations)).')';
        $link       = route('admin.seller.store');
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
                                echo set_input_form('text', 'fullname', ucwords(lang('full name', $translations)), $data, $errors, true);

                                $config                 = new \stdClass();
                                $config->delete         = true;
                                $config->popup          = true;
                                $config->info = '<i class="fa fa-info-circle"></i>&nbsp; '.lang('Only support #allowed_ext, MAX #size', $translations, ['#allowed_ext'=>'jpg/jpeg/png', '#size'=>'2MB']);
                                echo set_input_form('image', 'avatar', ucwords(lang('avatar', $translations)), $data, $errors, false, $config);
                                
                                echo set_input_form('text', 'store_name', ucwords(lang('store name', $translations)), $data, $errors, true);

                                echo set_input_form('textarea', 'description', ucwords(lang('description', $translations)), $data, $errors, false);

                                $config                 = new \stdClass();
                                $config->attributes     = 'autocomplete="off"';
                                echo set_input_form('email', 'email', ucwords(lang('email', $translations)), $data, $errors, true, $config);

                                $config                 = new \stdClass();
                                $config->placeholder    = '8123456789';
                                $config->input_addon    = '62';
                                echo set_input_form('number_only', 'phone_number', ucwords(lang('phone', $translations)), $data, $errors, true, $config);

                                $config                 = new \stdClass();
                                $config->attributes     = 'readonly';
                                $config->placeholder    = 'dd/mm/yyyy';
                                echo set_input_form('datepicker', 'birth_date', ucwords(lang('birth date', $translations)), $data, $errors, false, $config);

                                $config                 = new \stdClass();
                                echo set_input_form('number_only', 'identity_number', ucwords(lang('identity number', $translations)), $data, $errors, false, $config);

                                $config                 = new \stdClass();
                                $config->delete         = true;
                                $config->popup          = true;
                                $config->info = '<i class="fa fa-info-circle"></i>&nbsp; '.lang('Only support #allowed_ext, MAX #size', $translations, ['#allowed_ext'=>'jpg/jpeg/png', '#size'=>'2MB']);
                                echo set_input_form('image', 'identity_image', ucwords(lang('identity image', $translations)), $data, $errors, false, $config);

                                $config                 = new \stdClass();
                                echo set_input_form('number_only', 'npwp_number', ucwords(lang('NPWP number', $translations)), $data, $errors, false, $config);

                                // BEGIN LOCATION =============================================================================================
                                $config                 = new \stdClass();
                                $config->defined_data   = $provinces;
                                $config->field_value    = 'code';
                                $config->field_text     = 'name';
                                $config->placeholder    = '- '.ucwords(lang('please choose one', $translations)).' -';
                                $config->attributes     = 'onchange="filter_district()"';
                                echo set_input_form('select2', 'province_code', ucfirst(lang('province', $translations)), $data, $errors, true, $config);
                                
                                $config                     = new \stdClass();
                                $config->defined_data       = $districts;
                                $config->field_value        = 'full_code';
                                $config->field_text         = 'name';
                                $config->placeholder        = '- '.ucwords(lang('please choose one', $translations)).' -';
                                $config->attributes         = 'onchange="filter_sub_district()"';
                                if (!$data) {
                                    $config->attributes    .= ' disabled';
                                }
                                echo set_input_form('select2', 'district_code', ucfirst(lang('district', $translations)), $data, $errors, true, $config);

                                $config                     = new \stdClass();
                                $config->defined_data       = $sub_districts;
                                $config->field_value        = 'full_code';
                                $config->field_text         = 'name';
                                $config->placeholder        = '- '.ucwords(lang('please choose one', $translations)).' -';
                                $config->attributes         = 'onchange="filter_village()"';
                                if (!$data) {
                                    $config->attributes    .= ' disabled';
                                }
                                echo set_input_form('select2', 'sub_district_code', ucwords(lang('sub district', $translations)), $data, $errors, true, $config);
                                
                                $config                     = new \stdClass();
                                $config->defined_data       = $villages;
                                $config->field_value        = 'full_code';
                                $config->field_text         = 'name';
                                $config->placeholder        = '- '.ucwords(lang('please choose one', $translations)).' -';
                                $config->attributes         = 'onchange="filter_postal_code()"';
                                if (!$data) {
                                    $config->attributes    .= ' disabled';
                                }
                                echo set_input_form('select2', 'village_code', ucfirst(lang('village', $translations)), $data, $errors, true, $config);

                                $config                     = new \stdClass();
                                $config->defined_data       = $postal_codes;
                                $config->field_value        = 'postal_code';
                                $config->field_text         = 'postal_code_label';
                                $config->placeholder        = '- '.ucwords(lang('please choose one', $translations)).' -';
                                if (!$data) {
                                    $config->attributes     = ' disabled';
                                }
                                echo set_input_form('select2', 'postal_code', ucwords(lang('postal code', $translations)), $data, $errors, true, $config);
                                
                                $config = new \stdClass();
                                $config->limit_chars = 255;
                                $config->placeholder = 'min 10 chars';
                                echo set_input_form('textarea', 'address_details', ucwords(lang('address details', $translations)), $data, $errors, true, $config);
                                // END LOCATION ===============================================================================================

                                $config                 = new \stdClass();
                                $config->defined_data   = $defined_jne_branches;
                                $config->placeholder    = '- '.ucwords(lang('please choose one', $translations)).' -';
                                echo set_input_form('select2', 'jne_branch', ucfirst(lang('JNE branch', $translations)), $data, $errors, false, $config);

                                $config                 = new \stdClass();
                                $config->default        = '';
                                echo set_input_form('switch', 'approval_status', ucwords(lang('approval status', $translations)), $data, $errors, false, $config);

                                $config                 = new \stdClass();
                                $config->default        = 'checked';
                                echo set_input_form('switch', 'status', ucfirst(lang('status', $translations)), $data, $errors, false, $config);

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
                                @php
                                    echo set_input_form('switch', 'stay_on_page', ucfirst(lang('stay on this page after submitting', $translations)), $data, $errors, false);
                                @endphp
                                <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i>&nbsp; 
                                        @if (isset($data))
                                            {{ ucwords(lang('save', $translations)) }}
                                        @else
                                            {{ ucwords(lang('submit', $translations)) }}
                                        @endif
                                    </button>
                                    <a href="{{ route('admin.seller') }}" class="btn btn-default"><i class="fa fa-times"></i>&nbsp; 
                                        @if (isset($data))
                                            {{ ucwords(lang('close', $translations)) }}
                                        @else
                                            {{ ucwords(lang('cancel', $translations)) }}
                                        @endif
                                    </a>

                                    @if (isset($raw_id))
                                        | <span class="btn btn-danger" onclick="$('#form_delete').submit()"><i class="fa fa-trash"></i></span>
                                    @endif

                                    @if ($data && !$data->approval_status)
                                        | <span class="btn btn-primary" onclick="$('#form_resend_email').submit()"><i class="fa fa-send"></i>&nbsp; Resend Email Seller Agreement</span>
                                    @endif
                                </div>
                            </div>

                        </form>

                        @if (isset($raw_id))
                            <form id="form_delete" action="{{ route('admin.seller.delete') }}" method="POST" onsubmit="return confirm('{!! lang('Are you sure to delete this #item?', $translations, ['#item' => 'data']) !!}');" style="display: none">
                                @csrf
                                <input type="hidden" name="id" value="{{ $raw_id }}">
                            </form>
                        @endif

                        @if ($data && !$data->approval_status)
                            <form id="form_resend_email" action="{{ route('admin.seller.resend_email') }}" method="POST" onsubmit="return confirm('{!! lang('Are you sure want to resend email seller agreement to this #item?', $translations, ['#item' => 'seller']) !!}');" style="display: none">
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

            // Disable dan reset select setelah district
            resetAndDisableSelect('#sub_district_code', '- {{ ucwords(lang("please choose one", $translations)) }} -');
            resetAndDisableSelect('#village_code', '- {{ ucwords(lang("please choose one", $translations)) }} -');
            resetAndDisableSelect('#postal_code', '- {{ ucwords(lang("please choose one", $translations)) }} -');
            
            var select2_elm = $('#district_code');
            
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

            // Disable dan reset field di bawahnya
            resetAndDisableSelect('#village_code', '- {{ ucwords(lang("please choose one", $translations)) }} -');
            resetAndDisableSelect('#postal_code', '- {{ ucwords(lang("please choose one", $translations)) }} -');

            var select2_elm = $('#sub_district_code');

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

            // Disable postal code
            resetAndDisableSelect('#postal_code', '- {{ ucwords(lang("please choose one", $translations)) }} -');

            var select2_elm = $('#village_code');

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

        function resetAndDisableSelect(selector, placeholderText) {
            var select2_elm = $(selector);
            select2_elm.empty().prop('disabled', true);
            var newOption = new Option(placeholderText, "", false, true);
            select2_elm.append(newOption);
        }

    </script>
@endsection