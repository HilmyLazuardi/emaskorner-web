@extends('_template_adm.master')

@php
    use App\Libraries\Helper;

    $pagetitle = ucwords(lang('system logs', $translations));
    if(isset($data)){
        $pagetitle .= ' ('.ucwords(lang('view', $translations)).')';
        $link = '';
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
                        <h2>{{ ucwords(lang('log details', $translations)) }}</h2>
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
                                echo set_input_form('text', 'id', ucwords(lang('log ID', $translations)), $data, $errors, false, $config);

                                $time_ago = Helper::time_ago(strtotime($data->created_at), lang('ago', $translations), Helper::get_periods($translations));
                                $config = new \stdClass();
                                $config->attributes = 'readonly';
                                $config->value = Helper::locale_timestamp($data->created_at) . ' - ' . $time_ago;
                                echo set_input_form('text', 'created_at', ucwords(lang('timestamp', $translations)), $data, $errors, false, $config);

                                $config = new \stdClass();
                                $config->attributes = 'readonly';
                                echo set_input_form('text', 'username', ucwords(lang('username', $translations)), $data, $errors, false, $config);
                                echo set_input_form('text', 'activity', ucwords(lang('activity', $translations)), $data, $errors, false, $config);
                                echo set_input_form('text', 'url', strtoupper(lang('url', $translations)), $data, $errors, false, $config);
                                echo set_input_form('text', 'ip_address', ucwords(lang('IP address', $translations)), $data, $errors, false, $config);
                                
                                $config->autosize = true;
                                echo set_input_form('textarea', 'user_agent', ucwords(lang('user agent', $translations)), $data, $errors, false, $config);

                                if (!$data->value_before) {
                                    $config->value = '{{-- NO DATA AVAILABLE  --}}';
                                }
                                echo set_input_form('textarea', 'value_before', ucwords(lang('value before', $translations)).' (Old)', $data, $errors, false, $config);
                                
                                $config = new \stdClass();
                                $config->attributes = 'readonly';
                                $config->autosize = true;
                                if (!$data->value_after) {
                                    $config->value = '{{-- NO DATA AVAILABLE  --}}';
                                }
                                echo set_input_form('textarea', 'value_after', ucwords(lang('value after', $translations)).' (New)', $data, $errors, false, $config);
                            @endphp

                            {{-- DISPLAY THE DIFF OF VALUES (BEFORE VS AFTER) --}}
                            {!! $result !!}

                            <div class="ln_solid"></div>

                            <div class="form-group">
                                <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                    <a href="{{ route('admin.system_logs') }}" class="btn btn-default"><i class="fa fa-times"></i>&nbsp; 
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
    <style>
        {!! $diff_css !!}
    </style>
@endsection

@section('script')
    <!-- Textarea Autosize -->
    @include('_vendors.autosize.script')
@endsection