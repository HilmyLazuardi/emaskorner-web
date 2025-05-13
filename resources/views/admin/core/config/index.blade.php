{{-- ADD HTML SMALL MODAL - BEGIN --}}
@extends('_template_adm.modal_small')
{{-- SMALL MODAL CONFIG --}}
@section('small_modal_id', 'modal_import')
@section('small_modal_title', ucwords(lang('import', $translations)))
@section('small_modal_content')
  <label>{{ lang('Browse the file', $translations) }}</label>
  <div class="form-group">
    <input type="file" name="file" required="required" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
  </div>
@endsection
@section('small_modal_btn_label', ucwords(lang('import', $translations)))
@section('small_modal_form', true)
@section('small_modal_method', 'POST')
@section('small_modal_url', route('admin.config.import'))
@section('small_modal_form_validation', 'return validate_import_file()')
@section('small_modal_script')
    <script>
        function validate_import_file() {
            if (confirm("{{ lang('Are you sure to import this file?', $translations) }}")) {
                $('#modal_import').modal('hide');
                setTimeout(function(){ show_loading(); }, 500);
                return true;
            }
            return false;
        }
    </script>
@endsection
{{-- ADD HTML SMALL MODAL - END --}}

@extends('_template_adm.master')

@php
    $pagetitle = ucwords(lang('application configuration', $translations));
@endphp

@section('title', $pagetitle)

@section('content')
    <div class="">
        {{-- display response message --}}
        @include('_template_adm.message')

        <div class="page-title">
            <div class="title_left">
                <h3>{{ $pagetitle }}</h3>
            </div>
            <div class="title_right">
                <div class="col-md-5 col-sm-5 col-xs-12 form-group pull-right">
                    <a href="javascript:void(0)" class="btn btn-round btn-warning" style="float: right; margin-bottom: 5px;" onclick="confirm_export()">
                        <i class="fa fa-download"></i>&nbsp; {{ ucwords(lang('export', $translations)) }}
                    </a>
                    <button type="button" class="btn btn-primary btn-round" data-toggle="modal" data-target="#modal_import" style="float: right;">
                        <i class="fa fa-upload"></i>&nbsp; {{ ucwords(lang('import', $translations)) }}
                    </button>
                </div>
            </div>
        </div>

        <div class="clearfix"></div>

        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <form class="form-horizontal form-label-left" action="{{ route('admin.config') }}" method="POST" enctype="multipart/form-data">
                    <div class="x_panel">
                        <div class="x_title">
                            <h2>{{ ucwords(lang('main configuration', $translations)) }}</h2>
                            <ul class="nav navbar-right panel_toolbox">
                                <li style="float: right !important;"><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
                            </ul>
                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content">
                            @php
                                // set_input_form($type, $input_name, $label_name, $data, $errors, $required = false, $config = null)
                                echo set_input_form('text', 'app_name', ucwords(lang('application name', $translations)), $data, $errors, true);
                                echo set_input_form('text', 'app_version', ucwords(lang('application version', $translations)), $data, $errors, true);
                                echo set_input_form('number', 'app_copyright_year', ucwords(lang('application copyright year', $translations)), $data, $errors, true);

                                $config = new \stdClass();
                                $config->info_text = '<i class="fa fa-info-circle"></i> &nbsp;Input base URL for this application/website that managed by this CMS.';
                                echo set_input_form('text', 'app_url_site', ucwords(lang('application URL', $translations)), $data, $errors, true, $config);

                                $config = new \stdClass();
                                $config->info_text = '<i class="fa fa-info-circle"></i> &nbsp;Input Main URL, if this application used for manage microsite. <br>(If this is used by "blog.your-domain.com" as microsite, then input "your-domain.com" as Main URL)';
                                echo set_input_form('text', 'app_url_main', ucwords(lang('main application URL', $translations)), $data, $errors, false, $config);
                                
                                $config = new \stdClass();
                                $config->info = '<i class="fa fa-info-circle"></i> &nbsp;Recommended to use the smallest image size.';
                                if (empty($data->app_favicon)) {
                                    echo set_input_form('image', 'app_favicon', 'Favicon', $data, $errors, true, $config);
                                } else {
                                    echo set_input_form('image', 'app_favicon', 'Favicon', $data, $errors, false, $config);
                                }

                                $config = new \stdClass();
                                $config->popup = true;
                                $config->info = '<i class="fa fa-info-circle"></i> &nbsp;Recommended to use the square image, PNG transparent, min. 72 x 72px.';
                                if (empty($data->app_logo)) {
                                    echo set_input_form('image', 'app_logo', ucwords(lang('application logo image', $translations)), $data, $errors, true, $config);
                                } else {
                                    echo set_input_form('image', 'app_logo', ucwords(lang('application logo image', $translations)), $data, $errors, false, $config);
                                }

                                $skins = ['default','festive_yellow', 'feminine_purple', 'racing_red', 'calm_blue', 'simple_black', 'manly_maroon', 'green_nature'];
                                $defined_data_skins = [];
                                foreach ($skins as $skin) {
                                    $obj_option = new \stdClass();
                                    $obj_option->id = $skin;
                                    $obj_option->name = ucwords(str_replace('_', ' ', $skin));
                                    $defined_data_skins[] = $obj_option;
                                }
                                $config = new \stdClass();
                                $config->defined_data = $defined_data_skins;
                                $config->placeholder = '- '.ucwords(lang('please choose one', $translations)).' -';
                                echo set_input_form('select2', 'app_skin', ucwords(lang('application theme', $translations)), $data, $errors, true, $config);

                                $config = new \stdClass();
                                $config->info_text = '<i class="fa fa-info-circle"></i> &nbsp;Enter a short description as Info CMS popup content.';
                                $config->autosize = true;
                                echo set_input_form('textarea', 'app_info', ucwords(lang('info', $translations)), $data, $errors, true, $config);

                                echo set_input_form('text', 'powered_by', ucwords(lang('powered by', $translations)), $data, $errors, false);
                                echo set_input_form('text', 'powered_by_url', ucwords(lang('powered by URL', $translations)), $data, $errors, false);
                            @endphp
                            
                            <div class="ln_solid"></div>

                            <div class="form-group">
                                <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i>&nbsp; 
                                        @if (isset($data))
                                            {{ ucwords(lang('save', $translations)) }}
                                        @else
                                            {{ ucwords(lang('submit', $translations)) }}
                                        @endif
                                    </button>
                                    <a href="{{ route('admin.config') }}" class="btn btn-danger"><i class="fa fa-times"></i>&nbsp; {{ ucwords(lang('cancel', $translations)) }}</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="x_panel">
                        <div class="x_title">
                            <h2>{!! ucwords(lang('SEO configuration', $translations)) !!}</h2>
                            <ul class="nav navbar-right panel_toolbox">
                                <li style="float: right !important;"><a class="collapse-link"><i class="fa fa-chevron-down"></i></a></li>
                            </ul>
                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content" style="display: none;">
                            @php
                                echo set_input_form('text', 'meta_title', 'meta title', $data, $errors, true);

                                $config = new \stdClass();
                                $config->autosize = true;
                                echo set_input_form('textarea', 'meta_description', 'meta description', $data, $errors, true, $config);
                                echo set_input_form('tags', 'meta_keywords', 'meta keywords', $data, $errors, true);
                                echo set_input_form('text', 'meta_author', 'meta author', $data, $errors, true);
                            @endphp

                            <div class="ln_solid"></div>

                            @php
                                $config = new \stdClass();
                                $config->info_text = '<i class="fa fa-info-circle"></i> &nbsp;Example: website/article/etc. Read more on <a href="https://ogp.me/#types" target="_blank" style="font-style:italic; text-decoration:underline;">ogp.me <i class="fa fa-external-link"></i></a>.';
                                echo set_input_form('text', 'og_type', 'open graph type', $data, $errors, true, $config);

                                $config = new \stdClass();
                                $config->info_text = '<i class="fa fa-info-circle"></i> &nbsp;If your object is part of a larger web site, the name which should be displayed for the overall site. e.g., "IMDb".';
                                echo set_input_form('text', 'og_site_name', 'open graph site name', $data, $errors, true, $config);

                                $config = new \stdClass();
                                $config->info_text = '<i class="fa fa-info-circle"></i> &nbsp;The title of your object as it should appear within the graph.';
                                echo set_input_form('text', 'og_title', 'open graph title', $data, $errors, true, $config);

                                $config = new \stdClass();
                                $config->popup = true;
                                $config->info = '<i class="fa fa-info-circle"></i> &nbsp;An image which should represent your object within the graph.';
                                if (empty($data->og_image)) {
                                    echo set_input_form('image', 'og_image', 'open graph image', $data, $errors, true, $config);
                                } else {
                                    echo set_input_form('image', 'og_image', 'open graph image', $data, $errors, false, $config);
                                }

                                $config = new \stdClass();
                                $config->info_text = '<i class="fa fa-info-circle"></i> &nbsp;A one to two sentence description of your object.';
                                $config->autosize = true;
                                echo set_input_form('textarea', 'og_description', 'open graph description', $data, $errors, true, $config);
                            @endphp

                            <div class="ln_solid"></div>

                            @php
                                $config = new \stdClass();
                                $config->defined_data = ["summary", "summary_large_image", "app", "player"];
                                $config->info_text = '<i class="fa fa-info-circle"></i> &nbsp;You can check <a href="https://developer.twitter.com/en/docs/twitter-for-websites/cards/overview/markup" target="_blank" style="font-style:italic; text-decoration:underline;">Twitter Dev Docs <i class="fa fa-external-link"></i></a> for more details. And test your Twitter Card on <a href="https://cards-dev.twitter.com/validator" target="_blank" style="font-style:italic; text-decoration:underline;">Card Validator <i class="fa fa-external-link"></i></a>.';
                                echo set_input_form('select', 'twitter_card', 'Twitter Card', $data, $errors, true, $config);

                                $config = new \stdClass();
                                $config->placeholder = '@username';
                                $config->info_text = '<i class="fa fa-info-circle"></i> &nbsp;@username for the website used in the card footer.';
                                echo set_input_form('text', 'twitter_site', 'Twitter Site', $data, $errors, false, $config);

                                $config = new \stdClass();
                                $config->placeholder = '1234567890';
                                $config->info_text = '<i class="fa fa-info-circle"></i> &nbsp;Same as Twitter Site, but the user’s Twitter ID. You can use <a href="https://tweeterid.com/" target="_blank" style="font-style:italic; text-decoration:underline;">tweeterid.com <i class="fa fa-external-link"></i></a> to get Twitter ID.';
                                echo set_input_form('text', 'twitter_site_id', 'Twitter Site ID', $data, $errors, false, $config);

                                $config = new \stdClass();
                                $config->placeholder = '@username';
                                $config->info_text = '<i class="fa fa-info-circle"></i> &nbsp;@username for the content creator/author.';
                                echo set_input_form('text', 'twitter_creator', 'Twitter Creator', $data, $errors, false, $config);

                                $config = new \stdClass();
                                $config->placeholder = '1234567890';
                                $config->info_text = '<i class="fa fa-info-circle"></i> &nbsp;Twitter user ID of content creator.';
                                echo set_input_form('text', 'twitter_creator_id', 'Twitter Creator ID', $data, $errors, false, $config);
                            @endphp

                            <div class="ln_solid"></div>

                            @php
                                $config = new \stdClass();
                                $config->placeholder = '1234567890';
                                $config->info_text = '<i class="fa fa-info-circle"></i> &nbsp;In order to use Facebook Insights you must add the app ID to your page. Insights lets you view analytics for traffic to your site from Facebook. Read more on <a href="https://developers.facebook.com/docs/sharing/webmasters/" target="_blank" style="font-style:italic; text-decoration:underline;">FB Dev Docs <i class="fa fa-external-link"></i></a>. And test your markup on <a href="https://developers.facebook.com/tools/debug/" target="_blank" style="font-style:italic; text-decoration:underline;">Sharing Debugger <i class="fa fa-external-link"></i></a>.';
                                echo set_input_form('text', 'fb_app_id', 'FB App ID', $data, $errors, false, $config);
                            @endphp
                        </div>
                    </div>

                    <div class="x_panel">
                        <div class="x_title">
                            <h2>{!! ucwords(lang('security settings', $translations)) !!}</h2>
                            <ul class="nav navbar-right panel_toolbox">
                                <li style="float: right !important;"><a class="collapse-link"><i class="fa fa-chevron-down"></i></a></li>
                            </ul>
                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content" style="display: none;">
                            <h5 class="text-center"><a href="https://www.google.com/recaptcha/about/" target="_blank">reCAPTCHA v2 Checkbox &nbsp;<i class="fa fa-external-link"></i></a></h5>
                            @php
                                $config = new \stdClass();
                                $config->autosize = true;
                                $config->placeholder = 'AbC1201ManTap15JkTbkXoKe_wKwkwk7jajksYal';
                                echo set_input_form('textarea', 'recaptcha_site_key_admin', 'Site Key (admin)', $data, $errors, false, $config);
                                echo set_input_form('textarea', 'recaptcha_secret_key_admin', 'Secret Key (admin)', $data, $errors, false, $config);
                                echo set_input_form('textarea', 'recaptcha_site_key_public', 'Site Key (public)', $data, $errors, false, $config);
                                echo set_input_form('textarea', 'recaptcha_secret_key_public', 'Secret Key (public)', $data, $errors, false, $config);
                            @endphp

                            <div class="ln_solid"></div>

                            @php
                                $config = new \stdClass();
                                $config->info_text = '<i class="fa fa-info-circle"></i> &nbsp;If enable "Secure Login" then you must set "Login Trial Limit".<br>If someone try to login more than "Login Trial Amount", then their IP adress will be blocked.';
                                echo set_input_form('switch', 'secure_login', 'Secure Login', $data, $errors, false, $config);
                                
                                $config = new \stdClass();
                                $config->attributes = 'min="0" step="1"';
                                echo set_input_form('number', 'login_trial', 'Login Trial Limit', $data, $errors, false, $config);
                            @endphp
                        </div>
                    </div>

                    <div class="x_panel">
                        <div class="x_title">
                            <h2>{!! ucwords(lang('background images', $translations)) !!}</h2>
                            <ul class="nav navbar-right panel_toolbox">
                                <li style="float: right !important;"><a class="collapse-link"><i class="fa fa-chevron-down"></i></a></li>
                            </ul>
                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content" style="display: none;">
                            @php
                                $config = new \stdClass();
                                $config->popup = true;

                                if (empty($data->bg_img_login)) {
                                    $config->value = 'images/background.jpeg';
                                }
                                $config->info = '<i class="fa fa-info-circle"></i> &nbsp;Recommended to use an image with a minimum resolution of 1600x900px with the smallest possible size with a maximum size of 2MB. You may use <a href="https://tinypng.com/" target="_blank" style="font-style:italic; text-decoration:underline;">TinyPNG <i class="fa fa-external-link"></i></a> for compressing the image.';
                                $config->info .= '<br>View the page by click <a href="'.route('dev.custom_pages', 'login').'" target="_blank" style="font-style:italic; text-decoration:underline;">here <i class="fa fa-external-link"></i></a>';
                                echo set_input_form('image', 'bg_img_login', ucwords(lang('login page', $translations)), $data, $errors, false, $config);

                                if (empty($data->bg_img_404)) {
                                    $config->value = 'images/background.jpeg';
                                }
                                $config->info = '<i class="fa fa-info-circle"></i> &nbsp;Recommended to use an image with a minimum resolution of 1600x900px with the smallest possible size with a maximum size of 2MB. You may use <a href="https://tinypng.com/" target="_blank" style="font-style:italic; text-decoration:underline;">TinyPNG <i class="fa fa-external-link"></i></a> for compressing the image.';
                                $config->info .= '<br>View the page by click <a href="'.route('dev.custom_pages', '404').'" target="_blank" style="font-style:italic; text-decoration:underline;">here <i class="fa fa-external-link"></i></a>';
                                echo set_input_form('image', 'bg_img_404', ucwords(lang('404 | not found page', $translations)), $data, $errors, false, $config);

                                if (empty($data->bg_img_405)) {
                                    $config->value = 'images/background.jpeg';
                                }
                                $config->info = '<i class="fa fa-info-circle"></i> &nbsp;Recommended to use an image with a minimum resolution of 1600x900px with the smallest possible size with a maximum size of 2MB. You may use <a href="https://tinypng.com/" target="_blank" style="font-style:italic; text-decoration:underline;">TinyPNG <i class="fa fa-external-link"></i></a> for compressing the image.';
                                $config->info .= '<br>View the page by click <a href="'.route('dev.custom_pages', '405').'" target="_blank" style="font-style:italic; text-decoration:underline;">here <i class="fa fa-external-link"></i></a>';
                                echo set_input_form('image', 'bg_img_405', ucwords(lang('405 | method not allowed', $translations)), $data, $errors, false, $config);
                                
                                if (empty($data->bg_img_419)) {
                                    $config->value = 'images/background.jpeg';
                                }
                                $config->info = '<i class="fa fa-info-circle"></i> &nbsp;Recommended to use an image with a minimum resolution of 1600x900px with the smallest possible size with a maximum size of 2MB. You may use <a href="https://tinypng.com/" target="_blank" style="font-style:italic; text-decoration:underline;">TinyPNG <i class="fa fa-external-link"></i></a> for compressing the image.';
                                $config->info .= '<br>View the page by click <a href="'.route('dev.custom_pages', '419').'" target="_blank" style="font-style:italic; text-decoration:underline;">here <i class="fa fa-external-link"></i></a>';
                                echo set_input_form('image', 'bg_img_419', ucwords(lang('419 | expired page', $translations)), $data, $errors, false, $config);
                            @endphp
                        </div>
                    </div>

                    <div class="x_panel">
                        <div class="x_title">
                            <h2>{!! ucwords(lang('add scripts', $translations)) !!}</h2>
                            <ul class="nav navbar-right panel_toolbox">
                                <li style="float: right !important;"><a class="collapse-link"><i class="fa fa-chevron-down"></i></a></li>
                            </ul>
                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content" style="display: none;">
                            @php
                                $config = new \stdClass();
                                $config->placeholder = lang('example: Google Analytics script, FB Pixel, etc', $translations);
                                $config->info_text = '<i class="fa fa-info-circle"></i>&nbsp; ' . lang('inserted before tag', $translations). ' ' . htmlentities('</head>') . ', ' . $config->placeholder;
                                $config->autosize = true;
                                echo set_input_form('textarea', 'header_script', ucwords(lang('header script', $translations)), $data, $errors, false, $config);
                                
                                $config = new \stdClass();
                                $config->info_text = '<i class="fa fa-info-circle"></i>&nbsp; ' . lang('inserted after tag', $translations). ' ' . htmlentities('<body>');
                                $config->autosize = true;
                                echo set_input_form('textarea', 'body_script', ucwords(lang('body script', $translations)), $data, $errors, false, $config);
                                
                                $config = new \stdClass();
                                $config->info_text = '<i class="fa fa-info-circle"></i>&nbsp; ' . lang('inserted before tag', $translations). ' ' . htmlentities('</body>');
                                $config->autosize = true;
                                echo set_input_form('textarea', 'footer_script', ucwords(lang('footer script', $translations)), $data, $errors, false, $config);
                            @endphp
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('css')
    <!-- Switchery -->
    @include('_vendors.switchery.css')
    <!-- PhotoSwipe -->
    @include('_vendors.photoswipe.css')
@endsection

@section('script')
    <!-- Switchery -->
    @include('_vendors.switchery.script')
    <!-- jQuery Tags Input -->
    @include('_vendors.tagsinput.script')
    <!-- PhotoSwipe -->
    @include('_vendors.photoswipe.script')
    <!-- Textarea Autosize -->
    @include('_vendors.autosize.script')

    <script>
        function confirm_export() {
            if (confirm("{{ lang('Are you sure to export this #item?', $translations, ['#item' => $pagetitle]) }}")) {
                window.location.href = "{{ route('admin.config.export') }}";
            }
        }
    </script>
@endsection