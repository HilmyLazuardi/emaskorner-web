@extends('_template_adm.master')

@php
    use App\Libraries\Helper;

    $pagetitle = ucwords(lang('error log', $translations));
    if(isset($data)){
        $pagetitle .= ' ('.ucwords(lang('view', $translations)).')';
        $link = route('admin.error_logs.update', $raw_id);
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
                        <h2>{{ ucwords(lang('error log details', $translations)) }}</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <br />
                        <form id="form_data" class="form-horizontal form-label-left" action="{{ $link }}" method="POST">
                            @csrf

                            @php
                                // set_input_form($type, $input_name, $label_name, $data, $errors, $required = false, $config = null)
                                $config = new \stdClass();
                                $config->attributes = 'readonly';
                                echo set_input_form('text', 'id', ucwords(lang('error log ID', $translations)), $data, $errors, false, $config);

                                $config = new \stdClass();
                                $config->autosize = true;
                                $config->attributes = 'readonly';
                                echo set_input_form('textarea', 'url_get_error', ucwords(lang('URL error', $translations)), $data, $errors, false, $config);
                                echo set_input_form('textarea', 'url_prev', ucwords(lang('URL previous', $translations)), $data, $errors, false, $config);
                                echo set_input_form('textarea', 'err_message', ucwords(lang('error message', $translations)), $data, $errors, false, $config);

                                echo set_input_form('text', 'username', ucwords(lang('username', $translations)), $data, $errors, false, $config);
                                echo set_input_form('text', 'module_name', ucwords(lang('module', $translations)), $data, $errors, false, $config);
                                echo set_input_form('text', 'target_id', ucwords(lang('target ID', $translations)), $data, $errors, false, $config);
                                echo set_input_form('text', 'ip_address', ucwords(lang('IP address', $translations)), $data, $errors, false, $config);

                                $config->autosize = true;
                                echo set_input_form('textarea', 'user_agent', ucwords(lang('user agent', $translations)), $data, $errors, false, $config);
                                echo set_input_form('textarea', 'remarks', ucwords(lang('remarks', $translations)), $data, $errors, false, $config);

                                $config = new \stdClass();
                                $config->default = 'checked';
                                echo set_input_form('switch', 'status', ucwords(lang('solved status', $translations)), $data, $errors, false, $config);

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
                                    <a href="{{ route('admin.error_logs') }}" class="btn btn-default"><i class="fa fa-times"></i>&nbsp; 
                                        @if (isset($data))
                                            {{ ucwords(lang('close', $translations)) }}
                                        @else
                                            {{ ucwords(lang('cancel', $translations)) }}
                                        @endif
                                    </a>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
    <!-- Switchery -->
    @include('_vendors.switchery.css')
@endsection

@section('script')
    <!-- Switchery -->
    @include('_vendors.switchery.script')
    <!-- Textarea Autosize -->
    @include('_vendors.autosize.script')
@endsection