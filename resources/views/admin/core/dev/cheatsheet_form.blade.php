@extends('_template_adm.master')

@php
    $pagetitle = ucwords(lang('cheatsheet form', $translations));
    $link = '#';
    $data = null;
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
                            This CMS adds a custom PHP function "set_input_form" in "app\Libraries\CustomFunction.php" which is automatically called in the web load as it is set in composer.json.<br>
                            This custom function is used to generate form elements.
                        </div>
                        <div style="width: 100%;overflow: hidden;">
                            <div class="form-group vinput_text1">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="text1">Image Multiple <span class="required" style="color:red">*</span></label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <form action="/file-upload" class="dropzone" id="my-awesome-dropzone">
                                        <div class="fallback">
                                            <input name="file" type="file" multiple />
                                        </div>
                                    </form>
                                    <div class="upload_bg">
                                        <div class="upload_one"></div>
                                        <div class="upload_add_image"></div>
                                        <div class="upload_add_image"></div>
                                        <div class="upload_add_image"></div>
                                        <div class="upload_add_image"></div>
                                    </div>
                                    <span class="upload_info">
                                        Rekomendasi size 1024 px x 1024 px (kotak)
                                    </span>
                                </div>
                            </div>
                        </div>
                        <form id="form_data" class="form-horizontal form-label-left" action="{{ $link }}" method="POST" enctype="multipart/form-data">
                            
                            @csrf

                            {{-- TEXT --}}
                            @php
                                // set_input_form($type, $input_name, $label_name, $data, $errors, $required = false, $config = null)
                                $config = new \stdClass();
                                $config->placeholder = 'You can set placeholder using config: placeholder';
                                $config->info_text = '<i class="fa fa-info-circle"></i> You can set some general config (placeholder, id_name, value, attributes, input_addon, info_text).<br>(This is sample of using info_text)';
                                echo set_input_form('text', 'text1', ucwords(lang('text', $translations)), $data, $errors, true, $config);
                                
                                $config = new \stdClass();
                                $config->id_name = 'this_is_text_id';
                                $config->placeholder = 'Set element id for this input text: '.$config->id_name;
                                echo set_input_form('text', 'text2', ucwords(lang('text (Custom element ID)', $translations)), $data, $errors, true, $config);
                                
                                $config = new \stdClass();
                                $config->value = 'this is custom value';
                                echo set_input_form('text', 'text3', ucwords(lang('text (Custom value)', $translations)), $data, $errors, true, $config);

                                $config = new \stdClass();
                                $config->attributes = 'readonly';
                                $config->placeholder = 'Set custom attributes: '.$config->attributes;
                                echo set_input_form('text', 'text4', ucwords(lang('text (Custom attributes)', $translations)), $data, $errors, true, $config);
                                
                                $config = new \stdClass();
                                $config->input_addon = '<i class="fa fa-envelope"></i>';
                                $config->placeholder = 'with input_addon';
                                echo set_input_form('text', 'text5', ucwords(lang('text (with add-on)', $translations)), $data, $errors, true, $config);
                                
                                $config = new \stdClass();
                                $config->placeholder = 'with limit_chars';
                                $config->limit_chars = 10;
                                echo set_input_form('text', 'text6', ucwords(lang('text (with limited chars)', $translations)), $data, $errors, true, $config);
                                
                                $config = new \stdClass();
                                $config->placeholder = 'all text input will be capitalized';
                                echo set_input_form('capital', 'capital', ucwords(lang('capital', $translations)), $data, $errors, true, $config);
                                
                                $config = new \stdClass();
                                $config->placeholder = 'only single word, usually for username';
                                echo set_input_form('word', 'word', ucwords(lang('word', $translations)), $data, $errors, true, $config);

                                $config = new \stdClass();
                                $config->placeholder = 'username@mail.com';
                                echo set_input_form('email', 'email', ucwords(lang('email', $translations)), $data, $errors, true, $config);

                                echo set_input_form('password', 'password', ucwords(lang('password', $translations)), $data, $errors, false);

                                $config = new \stdClass();
                                $config->viewable = true;
                                $config->info_text = '<i class="fa fa-info-circle"></i> You can show/hide password using config: viewable=true';
                                echo set_input_form('password', 'viewable_password', ucwords(lang('viewable password', $translations)), $data, $errors, false, $config);
                                
                                $config = new \stdClass();
                                $config->viewable = true;
                                echo set_input_form('password', 'viewable_password_confirmation', ucwords(lang('viewable password confirmation', $translations)), $data, $errors, false, $config);
                            @endphp

                            <div class="ln_solid"></div>

                            {{-- NUMBER --}}
                            @php
                                // set_input_form($type, $input_name, $label_name, $data, $errors, $required = false, $config = null)
                                $config = new \stdClass();
                                $config->placeholder = '120114';
                                echo set_input_form('number', 'number', ucwords(lang('number', $translations)), $data, $errors, true, $config);

                                $config = new \stdClass();
                                $config->placeholder = 'text input but numbers only';
                                echo set_input_form('number_only', 'number_only', ucwords(lang('number only', $translations)), $data, $errors, true, $config);

                                $config = new \stdClass();
                                $config->placeholder = '8123456789';
                                $config->input_addon = env('COUNTRY_CODE');
                                $config->info_text = '<i class="fa fa-info-circle"></i> number_only usually used for input phone';
                                echo set_input_form('number_only', 'number_only2', ucwords(lang('number only', $translations)), $data, $errors, true, $config);
                                
                                echo set_input_form('number_format', 'number_format', ucwords(lang('formatted numbers', $translations)), $data, $errors, true);
                                
                                $config = new \stdClass();
                                $config->input_addon = 'Rp';
                                echo set_input_form('number_format', 'number_format2', ucwords(lang('formatted numbers + add-on', $translations)), $data, $errors, true, $config);
                            @endphp

                            <div class="ln_solid"></div>

                            {{-- TEXTAREA --}}
                            @php
                                // set_input_form($type, $input_name, $label_name, $data, $errors, $required = false, $config = null)
                                $config = new \stdClass();
                                $config->placeholder = 'simple textarea';
                                echo set_input_form('textarea', 'textarea1', ucwords(lang('textarea', $translations)), $data, $errors, true, $config);

                                $config = new \stdClass();
                                $config->placeholder = 'textarea with set rows=10';
                                $config->rows = 10;
                                echo set_input_form('textarea', 'textarea2', ucwords(lang('textarea', $translations)), $data, $errors, true, $config);
                                
                                $config = new \stdClass();
                                $config->placeholder = 'textarea autosize, try to typing 1 <enter newline> 2 <enter newline> and so on';
                                $config->autosize = true;
                                echo set_input_form('textarea', 'textarea3', ucwords(lang('textarea', $translations)), $data, $errors, true, $config);
                                
                                echo set_input_form('text_editor', 'textarea4', ucwords(lang('rich text editor (WYSIWYG)', $translations)), $data, $errors, true);
                            @endphp

                            <div class="ln_solid"></div>

                            {{-- SELECT --}}
                            @php
                                // set_input_form($type, $input_name, $label_name, $data, $errors, $required = false, $config = null)
                                $config = new \stdClass();
                                $config->defined_data = ['Male', 'Female'];
                                $config->placeholder = '- '.ucwords(lang('please choose one', $translations)).' -';
                                $config->info_text = '<i class="fa fa-info-circle"></i> You must set config: defined_data in array';
                                echo set_input_form('select', 'select', ucfirst(lang('select', $translations)), $data, $errors, false, $config);
                                
                                $config = new \stdClass();
                                $config->placeholder = '- '.ucwords(lang('please choose one', $translations)).' -';
                                $config->info_text = '<i class="fa fa-info-circle"></i> Not set config: defined_data';
                                echo set_input_form('select', 'select_no_defined_data', ucfirst(lang('select', $translations)), $data, $errors, false, $config);

                                // set select2 defined_data
                                $defined_data_select2 = [];

                                $opt_select2_1 = new \stdClass();
                                $opt_select2_1->id = 1;
                                $opt_select2_1->codename = 'BDMN-25';
                                $opt_select2_1->name = "Vicky";
                                $opt_select2_1->location = "Jakarta";
                                $defined_data_select2[] = $opt_select2_1;

                                $opt_select2_2 = new \stdClass();
                                $opt_select2_2->id = 2;
                                $opt_select2_2->codename = "TYO-15";
                                $opt_select2_2->name = "Metta";
                                $opt_select2_2->location = "Sungai Duri";
                                $defined_data_select2[] = $opt_select2_2;

                                echo '<pre>';
                                    echo '*** Sample Array Object ***<br>';
                                    var_dump($defined_data_select2);
                                echo '</pre>';

                                $config = new \stdClass();
                                $config->defined_data = $defined_data_select2;
                                $config->placeholder = '- '.ucwords(lang('please choose one', $translations)).' -';
                                $config->info_text = '<i class="fa fa-info-circle"></i> You must set config: defined_data in array object';
                                echo set_input_form('select2', 'select2', ucfirst(lang('select2', $translations)), $data, $errors, false, $config);
                                
                                $config = new \stdClass();
                                $config->defined_data = $defined_data_select2;
                                $config->field_text = 'codename';
                                $config->placeholder = '- '.ucwords(lang('please choose one', $translations)).' -';
                                $config->info_text = '<i class="fa fa-info-circle"></i> You can set custom option text data using config: field_text';
                                echo set_input_form('select2', 'select2_2', ucfirst(lang('select2', $translations)), $data, $errors, false, $config);
                                
                                $config = new \stdClass();
                                $config->defined_data = $defined_data_select2;
                                $config->field_value = 'codename';
                                $config->field_text = 'name - location';
                                $config->separator = ' - ';
                                $config->placeholder = '- '.ucwords(lang('please choose one', $translations)).' -';
                                $config->info_text = '<i class="fa fa-info-circle"></i> You also can set custom option value data using config: field_value<br>You can set combination value for option text using config: separator';
                                echo set_input_form('select2', 'select2_3', ucfirst(lang('select2', $translations)), $data, $errors, false, $config);
                            @endphp

                            <div class="ln_solid"></div>

                            {{-- FILE --}}
                            @php
                                // set_input_form($type, $input_name, $label_name, $data, $errors, $required = false, $config = null)
                                $config = new \stdClass();
                                $config->info = '<i class="fa fa-info-circle"></i> Add info text for image, please using config: info (don\'t use config: info_text)';
                                echo set_input_form('image', 'image', ucwords(lang('image', $translations)), $data, $errors, false, $config);

                                $config = new \stdClass();
                                $config->info = '<i class="fa fa-info-circle"></i> You can set custom path in local path using config: path';
                                $config->path = 'images/';
                                $config->value = 'avatar.png';
                                echo set_input_form('image', 'image2', ucwords(lang('image', $translations)), $data, $errors, false, $config);
                                
                                $config = new \stdClass();
                                $config->info = '<i class="fa fa-info-circle"></i> You can still use this for images uploaded on other servers (Amazon S3, etc)';
                                $config->value = 'https://hosting.kiniditech.com/lara-s-cms_logo.png';
                                echo set_input_form('image', 'image3', ucwords(lang('image', $translations)), $data, $errors, false, $config);

                                $config = new \stdClass();
                                $config->info = '<i class="fa fa-info-circle"></i> If you want to delete existing image, use config: delete';
                                $config->value = 'https://hosting.kiniditech.com/lara-s-cms_logo.png';
                                $config->delete = true;
                                echo set_input_form('image', 'image4', ucwords(lang('image', $translations)), $data, $errors, false, $config);

                                $config = new \stdClass();
                                $config->info = '<i class="fa fa-info-circle"></i> If you want to view image in full size, use config: popup';
                                $config->value = 'https://hosting.kiniditech.com/lara-s-cms_logo.png';
                                $config->popup = true;
                                echo set_input_form('image', 'image5', ucwords(lang('image', $translations)), $data, $errors, false, $config);

                                $config = new \stdClass();
                                $config->info_text = '<i class="fa fa-info-circle"></i> Add info text for file using config: info_text';
                                echo set_input_form('file', 'file', ucwords(lang('file', $translations)), $data, $errors, false, $config);
                                
                                $config = new \stdClass();
                                $config->info_text = '<i class="fa fa-info-circle"></i> If file exists';
                                $config->value = asset('images/avatar.png');
                                echo set_input_form('file', 'file2', ucwords(lang('file', $translations)), $data, $errors, false, $config);
                                
                                $config = new \stdClass();
                                $config->info_text = '<i class="fa fa-info-circle"></i> Add feature delete the exisiting file';
                                $config->value = asset('images/avatar.png');
                                $config->delete = true;
                                echo set_input_form('file', 'file3', ucwords(lang('file', $translations)), $data, $errors, false, $config);
                            @endphp

                            <div class="ln_solid"></div>

                            {{-- ETC --}}
                            @php
                                // set_input_form($type, $input_name, $label_name, $data, $errors, $required = false, $config = null)
                                $config = new \stdClass();
                                $config->info_text = '<i class="fa fa-info-circle"></i> used for boolean (true/false)';
                                echo set_input_form('switch', 'switch', ucfirst(lang('switch', $translations)), $data, $errors, false, $config);

                                $config = new \stdClass();
                                $config->default = 'checked';
                                $config->info_text = '<i class="fa fa-info-circle"></i> You can set checked as default';
                                echo set_input_form('switch', 'switch2', ucfirst(lang('switch (with default)', $translations)), $data, $errors, false, $config);

                                $config = new \stdClass();
                                $config->info_text = '<i class="fa fa-info-circle"></i> separate with commas';
                                echo set_input_form('tags', 'tags', ucwords(lang('tags', $translations)), $data, $errors, false, $config);

                                $config = new \stdClass();
                                $config->info_text = '<i class="fa fa-info-circle"></i> values separated by commas';
                                $config->value = 'test,cms,siorensys';
                                echo set_input_form('tags', 'tags2', ucwords(lang('tags', $translations)), $data, $errors, false, $config);

                                $config = new \stdClass();
                                $config->attributes = 'readonly';
                                $config->placeholder = 'dd/mm/yyyy';
                                echo set_input_form('datepicker', 'datepicker', ucwords(lang('datepicker', $translations)), $data, $errors, false, $config);
                                
                                $config = new \stdClass();
                                $config->attributes = 'readonly';
                                $config->placeholder = 'dd/mm/yyyy hh:mm';
                                echo set_input_form('datetimepicker', 'datetimepicker', ucwords(lang('datetimepicker', $translations)), $data, $errors, false, $config);
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
                                    <a href="{{ route('admin.home') }}" class="btn btn-default"><i class="fa fa-times"></i>&nbsp; 
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
    <!-- PhotoSwipe -->
    @include('_vendors.photoswipe.css')
    <link rel="stylesheet" type="text/css" href="https://laravel3.isysedge.com/lokalkorner-web/public/vendors/dropzone/dropzone.css">
    <style type="text/css">
        .upload_bg {
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
            display: block;
            padding: 10px 10px;
            z-index: 0; }
          .upload_one {
            width: 100px;
            height: 100px;
            display: inline-block;
            float: left;
            margin-left: 5px;
            background: url('https://laravel3.isysedge.com/lokalkorner-web/public/images/no-image.png') no-repeat top center/contain; }
          .upload_add_image {
            width: 100px;
            height: 100px;
            display: block;
            margin-left: 7px;
            float: left;
            background: url('https://laravel3.isysedge.com/lokalkorner-web/public/images/no-image.png') no-repeat top center/contain; }
          .upload_info {
            display: block;
            text-align: center;
            margin-top: 5px;
            margin-bottom: 5px;
            font-size: 14px; }
            @media only screen and (max-width: 1420px) {
                .dropzone{
                    min-height: 230px;
                }
            }
    </style>
@endsection

@section('script')
    <!-- Textarea Autosize -->
    @include('_vendors.autosize.script')
    <!-- Switchery -->
    @include('_vendors.switchery.script')
    <!-- Select2 -->
    @include('_vendors.select2.script')
    <!-- jQuery Tags Input -->
    @include('_vendors.tagsinput.script')
    <!-- bootstrap-datetimepicker -->
    @include('_vendors.datetimepicker.script')
    <!-- PhotoSwipe -->
    @include('_vendors.photoswipe.script')
    <!-- Rich Text Editor (WYSIWYG) using TinyMCE -->
    @include('_vendors.tinymce.script')
    <!-- MULTIPLE IMAGE -->
    <script src="https://laravel3.isysedge.com/lokalkorner-web/public/vendors/dropzone/moment.min.js"></script>
    <script src="https://laravel3.isysedge.com/lokalkorner-web/public/vendors/dropzone/dropzone.js"></script>
@endsection