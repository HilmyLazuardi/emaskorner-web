@if (isset($raw_id))
    {{-- ADD HTML SMALL MODAL - BEGIN --}}
    @extends('_template_adm.modal_small')
    {{-- SMALL MODAL CONFIG --}}
    @section('small_modal_title', ucwords(lang('reset #item', $translations, ['#item' => lang('password', $translations)])))
    @section('small_modal_id', 'modal_password')
    @section('small_modal_content')
        <label>{{ ucwords(lang('new #item', $translations, ['#item' => lang('password', $translations)])) }}</label>
        <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-eye-slash" id="viewable-new_pass" style="cursor:pointer" onclick="viewable_password('new_pass')"></i></span>
            <input type="password" name="new_pass" id="new_pass" required="required" autocomplete="off" class="form-control col-md-7 col-xs-12">
        </div>

        <label>{{ ucwords(lang('confirm #item', $translations, ['#item' => lang('password', $translations)])) }}</label>
        <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-eye-slash" id="viewable-new_pass_confirmation" style="cursor:pointer" onclick="viewable_password('new_pass_confirmation')"></i></span>
            <input type="password" name="new_pass_confirmation" id="new_pass_confirmation" required="required" autocomplete="off" class="form-control col-md-7 col-xs-12">
        </div>

        <label><i class="fa fa-warning"></i>&nbsp; {{ ucwords(lang('password criteria', $translations)) }}</label>
        <ul style="padding-left: 20px !important;">
            <li>{{ lang('must be at least #min characters in length', $translations, ['#min'=>8]) }}</li>
            <li>{{ lang('must contain at least one lowercase letter', $translations) }}</li>
            <li>{{ lang('must contain at least one uppercase letter', $translations) }}</li>
            <li>{{ lang('must contain at least one digit numeric', $translations) }}</li>
            <li>{{ lang('must contain a special character (?!@#$%^&*~`_+=:;.,"><\'-)', $translations) }}</li>
        </ul>
    @endsection
    @section('small_modal_btn_label', ucwords(lang('submit', $translations)))
    @section('small_modal_form', true)
    @section('small_modal_method', 'POST')
    @section('small_modal_url', route('admin.user_admin.reset_password', $raw_id))
    @section('small_modal_form_validation', 'return validate_form()')
    @section('small_modal_script')
        <script>
            function validate_form() {
                var password = $('#new_pass').val();
                var password_confirmation = $('#new_pass_confirmation').val();

                if (password != password_confirmation) {
                    alert("{{ lang('#item confirmation does not match', $translations, ['#item'=>ucwords(lang('password', $translations))]) }}");
                    return false;
                }

                // validate password criteria
                // var regex = /^
                //     (?=.*\d)                                 // must contain at least one digit numeric
                //     (?=.*[a-z])                              // must contain at least one lowercase letter
                //     (?=.*[A-Z])                              // must contain at least one uppercase letter
                //     (?=.*[?!@#$%^&*~`_+=:;.,"><'-])          // must contain a special character (?!@#$%^&*~`_+=:;.,"><'-)
                //     [\da-zA-Z?!@#$%^&*~`_+=:;.,"><'-]{8,}    // must contain at least 8 from the mentioned characters
                // $/;
                var regex = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[?!@#$%^&*~`_+=:;.,"><'-])[\da-zA-Z?!@#$%^&*~`_+=:;.,"><'-]{8,}$/;

                if (regex.test(password)) {
                    // continue
                } else {
                    alert("{{ lang('#item format is invalid', $translations, ['#item'=>ucwords(lang('password', $translations))]) }}");
                    return false;
                }

                $('.btn-submit').addClass('disabled');
                $('.btn-submit').html('<i class="fa fa-spin fa-spinner"></i>&nbsp; {{ ucwords(lang("loading", $translations)) }}');
                return true;
            }
        </script>
    @endsection
    {{-- ADD HTML SMALL MODAL - END --}}
@endif

@extends('_template_adm.master')

@php
    use App\Libraries\Helper;

    $pagetitle = ucwords(lang('administrator', $translations));
    if(isset($data)){
        $pagetitle .= ' ('.ucwords(lang('edit', $translations)).')';
        $link = route('admin.user_admin.update', $raw_id);
    }else {
        $pagetitle .= ' ('.ucwords(lang('new', $translations)).')';
        $link = route('admin.user_admin.store');
        $data = null;

        // if add new, declare empty variables
        $group = [];
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
                        <form id="form_data" class="form-horizontal form-label-left" action="{{ $link }}" method="POST" onsubmit="return validate_form_new()">
                            @csrf

                            @php
                                // set_input_form($type, $input_name, $label_name, $data, $errors, $required = false, $config = null)
                                $config = new \stdClass();
                                $config->attributes = 'autocomplete="off"';
                                echo set_input_form('text', 'firstname', ucwords(lang('firstname', $translations)), $data, $errors, true, $config);
                                echo set_input_form('text', 'lastname', ucwords(lang('lastname', $translations)), $data, $errors, true, $config);

                                $config->placeholder = 'name@domain.com';
                                echo set_input_form('email', 'email', ucwords(lang('email', $translations)), $data, $errors, true, $config);
                                
                                $config = new \stdClass();
                                $config->attributes = 'autocomplete="off"';
                                $config->placeholder = '8123456789';
                                $config->input_addon = env('COUNTRY_CODE');
                                echo set_input_form('number_only', 'phone', ucwords(lang('phone', $translations)), $data, $errors, false, $config);

                                $config = new \stdClass();
                                $config->placeholder = '- '.ucwords(lang('please choose one', $translations)).' -';
                                $config->defined_data = $groups;
                                $config->field_value = 'id';
                                $config->field_text = 'name';
                                echo set_input_form('select2', 'group', ucwords(lang('group', $translations)), $data, $errors, true, $config);

                                $config = new \stdClass();
                                $config->attributes = 'autocomplete="off"';
                                echo set_input_form('text', 'username', ucwords(lang('username', $translations)), $data, $errors, true, $config);

                                // only show when add new
                                if (!$data) {
                                    $config->viewable = true;
                                    echo set_input_form('password', 'password', ucwords(lang('password', $translations)), $data, $errors, true, $config);
                                    
                                    $info_password = '<i class="fa fa-warning"></i>&nbsp; '.ucwords(lang('password criteria', $translations));
                                    $info_password .= '<ul style="padding-left: 20px !important;">'; 
                                    $info_password .= '<li>'.lang('must be at least #min characters in length', $translations, ['#min'=>8]).'</li>'; 
                                    $info_password .= '<li>'.lang('must contain at least one lowercase letter', $translations).'</li>'; 
                                    $info_password .= '<li>'.lang('must contain at least one uppercase letter', $translations).'</li>'; 
                                    $info_password .= '<li>'.lang('must contain at least one digit numeric', $translations).'</li>'; 
                                    $info_password .= '<li>'.lang('must contain a special character (?!@#$%^&*~`_+=:;.,"><\'-)', $translations).'</li>'; 
                                    $info_password .= '</ul>'; 
                                    $config->info_text = $info_password;
                                    echo set_input_form('password', 'password_confirmation', ucwords(lang('#item confirmation', $translations, ['#item' => lang('password', $translations)])), $data, $errors, true, $config);
                                }

                                $config = new \stdClass();
                                $config->autosize = true;
                                echo set_input_form('textarea', 'remarks', ucwords(lang('remarks', $translations)), $data, $errors, false, $config);

                                $config = new \stdClass();
                                $config->default = 'checked';
                                echo set_input_form('switch', 'status', ucfirst(lang('status', $translations)), $data, $errors, false, $config);

                                // only show when edit
                                if ($data) {
                                    $time_ago = Helper::time_ago(strtotime($data->created_at), lang('ago', $translations), Helper::get_periods($translations));
                                    $config = new \stdClass();
                                    $config->attributes = 'readonly';
                                    $config->value = Helper::locale_timestamp($data->created_at) . ' - ' . $time_ago;
                                    echo set_input_form('text', 'created_at', ucwords(lang('created at', $translations)), $data, $errors, false, $config);
                                    
                                    $time_ago = Helper::time_ago(strtotime($data->updated_at), lang('ago', $translations), Helper::get_periods($translations));
                                    $config = new \stdClass();
                                    $config->attributes = 'readonly';
                                    $config->value = Helper::locale_timestamp($data->updated_at) . ' - ' . $time_ago;
                                    echo set_input_form('text', 'updated_at', ucwords(lang('last updated at', $translations)), $data, $errors, false, $config);
                                }
                            @endphp

                            {{-- only show when edit --}}
                            @if ($data)
                                <div class="form-group">
                                    <div class="col-md-12 text-center">
                                        <span class="btn btn-primary btn-round" data-toggle="modal" data-target="#modal_password">
                                            <i class="fa fa-unlock-alt"></i>&nbsp; {{ ucwords(lang('reset #item', $translations, ['#item' => lang('password', $translations)])) }}
                                        </span>
                                    </div>
                                </div>  
                            @endif
                            
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
                                    <a href="{{ route('admin.user_admin') }}" class="btn btn-default"><i class="fa fa-times"></i>&nbsp; 
                                        @if (isset($data))
                                            {{ ucwords(lang('close', $translations)) }}
                                        @else
                                            {{ ucwords(lang('cancel', $translations)) }}
                                        @endif
                                    </a>

                                    @if (isset($raw_id))
                                        | <span class="btn btn-danger" onclick="$('#form_delete').submit()"><i class="fa fa-trash"></i></span>
                                    @endif
                                </div>
                            </div>

                        </form>

                        @if (isset($raw_id))
                            <form id="form_delete" action="{{ route('admin.user_admin.delete') }}" method="POST" onsubmit="return confirm('{!! lang('Are you sure to delete this #item?', $translations, ['#item' => 'data']) !!}');" style="display: none">
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
@endsection

@section('script')
    <!-- Switchery -->
    @include('_vendors.switchery.script')
    <!-- Select2 -->
    @include('_vendors.select2.script')
    <!-- Textarea Autosize -->
    @include('_vendors.autosize.script')

    <script>
        function validate_form_new() {
            {{-- only validate when add new --}}
            @if (!$data)
                var password = $('#password').val();
                var password_confirmation = $('#password_confirmation').val();

                if (password != password_confirmation) {
                    alert("{{ lang('#item confirmation does not match', $translations, ['#item'=>ucwords(lang('password', $translations))]) }}");
                    return false;
                }

                // validate password criteria
                // var regex = /^
                //     (?=.*\d)                                 // must contain at least one digit numeric
                //     (?=.*[a-z])                              // must contain at least one lowercase letter
                //     (?=.*[A-Z])                              // must contain at least one uppercase letter
                //     (?=.*[?!@#$%^&*~`_+=:;.,"><'-])          // must contain a special character (?!@#$%^&*~`_+=:;.,"><'-)
                //     [\da-zA-Z?!@#$%^&*~`_+=:;.,"><'-]{8,}    // must contain at least 8 from the mentioned characters
                // $/;
                var regex = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[?!@#$%^&*~`_+=:;.,"><'-])[\da-zA-Z?!@#$%^&*~`_+=:;.,"><'-]{8,}$/;

                if (regex.test(password)) {
                    // continue
                } else {
                    alert("{{ lang('#item format is invalid', $translations, ['#item'=>ucwords(lang('password', $translations))]) }}");
                    return false;
                }
            @endif

            return true;
        }
    </script>
@endsection