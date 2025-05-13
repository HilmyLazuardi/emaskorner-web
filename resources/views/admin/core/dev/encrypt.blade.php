@extends('_template_adm.master')

@php
    $pagetitle = ucwords(lang('encrypt tool', $translations));
    $link = route('dev.encrypt');
    if (!isset($data)) {
        $data = null;
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
                        <div class="alert alert-info">
                            <h4><i class="fa fa-info-circle"></i> Info</h4>
                            This tool for encrypt string using <a href="https://github.com/defuse/php-encryption" target="_blank" style="color:white !important; text-decoration: underline !important;">defuse/php-encryption &nbsp;<i class="fa fa-external-link"></i></a>
                        </div>
                        <form id="form_data" class="form-horizontal form-label-left" action="{{ $link }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            {{-- TEXTAREA --}}
                            @php
                                // set_input_form($type, $input_name, $label_name, $data, $errors, $required = false, $config = null)
                                $config = new \stdClass();
                                $config->info_text = '<i class="fa fa-info-circle"></i> Upload secret key file, example: app-secret-key.txt';
                                echo set_input_form('file', 'key', ucwords(lang('key', $translations)), $data, $errors, true, $config);
                                
                                $config = new \stdClass();
                                $config->autosize = true;
                                echo set_input_form('textarea', 'string', ucwords(lang('string', $translations)), $data, $errors, true, $config);
                                
                                $config->attributes = 'readonly';
                                echo set_input_form('textarea', 'result', ucwords(lang('encrypted string', $translations)), $data, $errors, false, $config);
                            @endphp
                            
                            <div class="ln_solid"></div>

                            <div class="form-group">
                                <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i>&nbsp; 
                                        {{ ucwords(lang('submit', $translations)) }}
                                    </button>
                                    <a href="{{ route('dev.encrypt') }}" class="btn btn-warning">
                                        <i class="fa fa-refresh"></i>&nbsp; Reset
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
    
@endsection

@section('script')
    <!-- Textarea Autosize -->
    @include('_vendors.autosize.script')
@endsection