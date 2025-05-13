@extends('_template_adm.master')

@php
    use App\Libraries\Helper;

    $pagetitle = ucwords(lang('page', $translations));
    if(isset($data)){
        $pagetitle .= ' ('.ucwords(lang('edit', $translations)).')';
        $link = route('admin.page.update', $raw_id);
    }else {
        $pagetitle .= ' ('.ucwords(lang('new', $translations)).')';
        $link = route('admin.page.store');
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
                <form id="form_data" class="form-horizontal form-label-left" action="{{ $link }}" method="POST" enctype="multipart/form-data">
                    <div class="x_panel">
                        <div class="x_title">
                            <h2>{{ ucwords(lang('form details', $translations)) }}</h2>
                            <ul class="nav navbar-right panel_toolbox">
                                <li style="float: right !important;"><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
                            </ul>
                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content">
                            <br />

                            @php
                                // set_input_form($type, $input_name, $label_name, $data, $errors, $required = false, $config = null)
                                $config = new \stdClass();
                                $config->attributes = 'autocomplete="off"';
                                $config->limit_chars = 100;
                                $config->placeholder = lang('A good title is one that represents its content', $translations);
                                echo set_input_form('text', 'title', ucwords(lang('title', $translations)), $data, $errors, true, $config);
                                
                                $config->placeholder = lang('must be unique. if left empty, system will auto-generate this', $translations);
                                $config->info_text = '<br><i class="fa fa-info-circle"></i>&nbsp; '.lang("Slug is the part of the URL that explains the page’s content, example: domain.com/slug", $translations);
                                echo set_input_form('text', 'slug', ucwords(lang('slug', $translations)), $data, $errors, false, $config);
                                
                                $config = new \stdClass();
                                $config->popup = true;
                                $config->info = '<i class="fa fa-info-circle"></i>&nbsp; '.lang('Only support #allowed_ext, MAX #size', $translations, ['#allowed_ext'=>'jpg/jpeg/png', '#size'=>'2MB']);
                                $config->path = 'uploads/page/';
                                echo set_input_form('image', 'thumbnail', ucwords(lang('thumbnail', $translations)), $data, $errors, false, $config);

                                $config = new \stdClass();
                                $config->autosize = true;
                                echo set_input_form('textarea', 'summary', ucwords(lang('summary', $translations)), $data, $errors, true, $config);
                            @endphp
                            
                            <div class="ln_solid"></div>

                            <h2>{{ ucwords(lang('content', $translations)) }}</h2>
                            <div class="row">
                                <div class="col-lg-12">
                                    <span class="btn btn-primary" onclick="add_section_element(true)">
                                        <i class="fa fa-plus-circle"></i>&nbsp; {{ ucwords(lang('add section', $translations)) }}
                                    </span>
                                </div>
                                <div class="col-lg-12" style="margin: 5px 0;">
                                    <div id="content-pagebuilder" class="accordion"></div>
                                </div>
                            </div>

                            <div class="ln_solid"></div>

                            @php
                                $config = new \stdClass();
                                $config->default = 'checked';
                                echo set_input_form('switch', 'status', ucwords(lang('published status', $translations)), $data, $errors, false, $config);

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
                                    <a href="{{ route('admin.page') }}" class="btn btn-default"><i class="fa fa-times"></i>&nbsp; 
                                        @if (isset($data))
                                            {{ ucwords(lang('close', $translations)) }}
                                        @else
                                            {{ ucwords(lang('cancel', $translations)) }}
                                        @endif
                                    </a>

                                    @if (isset($raw_id))
                                        |&nbsp; <a href="{{ route('web.page', $data->slug) }}?preview={{ date('YmdHis') }}" target="_blank" class="btn btn-warning" onclick="return confirm_preview()"><i class="fa fa-external-link"></i>&nbsp; {{ ucwords(lang('preview', $translations)) }}</a>
                                        |&nbsp; <span class="btn btn-danger" onclick="$('#form_delete').submit()"><i class="fa fa-trash"></i></span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="x_panel">
                        <div class="x_title">
                            <h2>{!! lang('SEO Configuration', $translations) !!}</h2>
                            <ul class="nav navbar-right panel_toolbox">
                                <li style="float: right !important;"><a class="collapse-link"><i class="fa fa-chevron-down"></i></a></li>
                            </ul>
                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content" style="display: none;">
                            @php
                                $config = new \stdClass();
                                $config->placeholder = lang(
                                    'If left empty, system will set this using #item', 
                                    $translations,
                                    ['#item' => ucwords(lang('title', $translations))]
                                );
                                echo set_input_form('text', 'meta_title', 'meta title', $data, $errors, false, $config);

                                $config->autosize = true;
                                $config->placeholder = lang(
                                    'If left empty, system will set this using #item', 
                                    $translations,
                                    ['#item' => ucwords(lang('summary', $translations))]
                                );
                                echo set_input_form('textarea', 'meta_description', 'meta description', $data, $errors, false, $config);
                                
                                $config = new \stdClass();
                                if (!$data) {
                                    $config->info_text = '<i class="fa fa-warning"></i> &nbsp;' . lang(
                                        'If left empty, system will set this using #item from App Config', 
                                        $translations,
                                        ['#item' => ucwords(lang('meta keywords', $translations))]
                                    );
                                    if ($global_config->meta_keywords) {
                                        $config->info_text .= '<br>('.str_replace(',', ', ', $global_config->meta_keywords).')';
                                    }
                                }
                                echo set_input_form('tags', 'meta_keywords', 'meta keywords', $data, $errors, false, $config);
                                
                                $config = new \stdClass();
                                if (!$data) {
                                    $config->value = $global_config->meta_author;
                                }
                                echo set_input_form('text', 'meta_author', 'meta author', $data, $errors, false, $config);

                                $config = new \stdClass();
                                $config->info_text = '<i class="fa fa-info-circle"></i> &nbsp;Example: website/article/etc. Read more on <a href="https://ogp.me/#types" target="_blank" style="font-style:italic; text-decoration:underline;">ogp.me <i class="fa fa-external-link"></i></a>.';
                                if (!$data) {
                                    $config->value = 'article';
                                }
                                echo set_input_form('text', 'og_type', 'open graph type', $data, $errors, false, $config);

                                $config = new \stdClass();
                                $config->info_text = '<i class="fa fa-info-circle"></i> &nbsp;If your object is part of a larger web site, the name which should be displayed for the overall site. e.g., "IMDb".';
                                if (!$data) {
                                    $config->value = $global_config->og_site_name;
                                }
                                echo set_input_form('text', 'og_site_name', 'open graph site name', $data, $errors, false, $config);

                                $config = new \stdClass();
                                $config->info_text = '<i class="fa fa-info-circle"></i> &nbsp;The title of your object as it should appear within the graph.';
                                $config->placeholder = lang(
                                    'If left empty, system will set this using #item', 
                                    $translations,
                                    ['#item' => ucwords(lang('title', $translations))]
                                );
                                echo set_input_form('text', 'og_title', 'open graph title', $data, $errors, false, $config);

                                $config = new \stdClass();
                                $config->popup = true;
                                $config->info = '<i class="fa fa-info-circle"></i> &nbsp;An image which should represent your object within the graph.';
                                if (!$data) {
                                    $config->info .= '<br><i class="fa fa-warning"></i> &nbsp;';
                                    $config->info .= lang(
                                        'If left empty, system will set this using #item', 
                                        $translations,
                                        ['#item' => ucwords(lang('thumbnail', $translations))]
                                    );
                                    $config->info .= ' / ';
                                    $config->info .= lang(
                                        '#item from App Config', 
                                        $translations,
                                        ['#item' => ucwords(lang('open graph image', $translations))]
                                    );
                                }
                                if (empty($data->og_image)) {
                                    echo set_input_form('image', 'og_image', 'open graph image', $data, $errors, false, $config);
                                } else {
                                    echo set_input_form('image', 'og_image', 'open graph image', $data, $errors, false, $config);
                                }

                                $config = new \stdClass();
                                $config->info_text = '<i class="fa fa-info-circle"></i> &nbsp;A one to two sentence description of your object.';
                                $config->placeholder = lang(
                                    'If left empty, system will set this using #item', 
                                    $translations,
                                    ['#item' => ucwords(lang('summary', $translations))]
                                );
                                $config->autosize = true;
                                echo set_input_form('textarea', 'og_description', 'open graph description', $data, $errors, false, $config);

                                $config = new \stdClass();
                                $config->defined_data = ["summary", "summary_large_image", "app", "player"];
                                $config->info_text = '<i class="fa fa-info-circle"></i> &nbsp;You can check <a href="https://developer.twitter.com/en/docs/twitter-for-websites/cards/overview/markup" target="_blank" style="font-style:italic; text-decoration:underline;">Twitter Dev Docs <i class="fa fa-external-link"></i></a> for more details. And test your Twitter Card on <a href="https://cards-dev.twitter.com/validator" target="_blank" style="font-style:italic; text-decoration:underline;">Card Validator <i class="fa fa-external-link"></i></a>.';
                                echo set_input_form('select', 'twitter_card', 'Twitter Card', $data, $errors, false, $config);

                                $config = new \stdClass();
                                $config->placeholder = '@username';
                                $config->info_text = '<i class="fa fa-info-circle"></i> &nbsp;@username for the website used in the card footer.';
                                if (!$data) {
                                    $config->value = $global_config->twitter_site;
                                }
                                echo set_input_form('text', 'twitter_site', 'Twitter Site', $data, $errors, false, $config);
                                
                                $config = new \stdClass();
                                $config->placeholder = '1234567890';
                                $config->info_text = '<i class="fa fa-info-circle"></i> &nbsp;Same as Twitter Site, but the user’s Twitter ID. You can use <a href="https://tweeterid.com/" target="_blank" style="font-style:italic; text-decoration:underline;">tweeterid.com <i class="fa fa-external-link"></i></a> to get Twitter ID.';
                                if (!$data) {
                                    $config->value = $global_config->twitter_site_id;
                                }
                                echo set_input_form('text', 'twitter_site_id', 'Twitter Site ID', $data, $errors, false, $config);

                                $config = new \stdClass();
                                $config->placeholder = '@username';
                                $config->info_text = '<i class="fa fa-info-circle"></i> &nbsp;@username for the content creator/author.';
                                if (!$data) {
                                    $config->value = $global_config->twitter_creator;
                                }
                                echo set_input_form('text', 'twitter_creator', 'Twitter Creator', $data, $errors, false, $config);

                                $config = new \stdClass();
                                $config->placeholder = '1234567890';
                                $config->info_text = '<i class="fa fa-info-circle"></i> &nbsp;Twitter user ID of content creator.';
                                if (!$data) {
                                    $config->value = $global_config->twitter_creator_id;
                                }
                                echo set_input_form('text', 'twitter_creator_id', 'Twitter Creator ID', $data, $errors, false, $config);

                                $config = new \stdClass();
                                $config->placeholder = '1234567890';
                                $config->info_text = '<i class="fa fa-info-circle"></i> &nbsp;In order to use Facebook Insights you must add the app ID to your page. Insights lets you view analytics for traffic to your site from Facebook. Read more on <a href="https://developers.facebook.com/docs/sharing/webmasters/" target="_blank" style="font-style:italic; text-decoration:underline;">FB Dev Docs <i class="fa fa-external-link"></i></a>. And test your markup on <a href="https://developers.facebook.com/tools/debug/" target="_blank" style="font-style:italic; text-decoration:underline;">Sharing Debugger <i class="fa fa-external-link"></i></a>.';
                                if (!$data) {
                                    $config->value = $global_config->fb_app_id;
                                }
                                echo set_input_form('text', 'fb_app_id', 'FB App ID', $data, $errors, false, $config);
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

    @if (isset($raw_id))
        <form id="form_delete" action="{{ route('admin.page.delete') }}" method="POST" onsubmit="return confirm('{!! lang('Are you sure to delete this #item?', $translations, ['#item' => 'data']) !!}');" style="display: none">
            @csrf
            <input type="hidden" name="id" value="{{ $raw_id }}">
        </form>
    @endif

    <!-- KINIDI Tech - PageBuilder -->
    @include('_vendors.pagebuilder.html')
@endsection

@section('css')
    <!-- Switchery -->
    @include('_vendors.switchery.css')
    <!-- PhotoSwipe -->
    @include('_vendors.photoswipe.css')
    <!-- bootstrap-datetimepicker -->
    @include('_vendors.datetimepicker.css')
    <!-- KINIDI Tech - PageBuilder -->
    @include('_vendors.pagebuilder.css')
@endsection

@section('script')
    <!-- Switchery -->
    @include('_vendors.switchery.script')
    <!-- PhotoSwipe -->
    @include('_vendors.photoswipe.script')
    <!-- bootstrap-datetimepicker -->
    @include('_vendors.datetimepicker.script')
    <!-- Textarea Autosize -->
    @include('_vendors.autosize.script')
    <!-- jQuery Tags Input -->
    @include('_vendors.tagsinput.script')
    <!-- Rich Text Editor (WYSIWYG) using TinyMCE -->
    @include('_vendors.tinymce.script')
    <!-- KINIDI Tech - PageBuilder -->
    @include('_vendors.pagebuilder.script')
    <!-- KINIDI Tech - PageBuilder (Page) -->
    @include('_vendors.pagebuilder.script_page')

    <script>
        // KINIDI Tech - PageBuilder - Config (BEGIN)
        // SET PAGEBUILDER MODE
        var pagebuilder_mode = 'landing page';
        // SET CONTENT CONTAINER IN PAGEBUILDER
        var content_container = "#content-pagebuilder";
        // SET DEFAULT IMAGE FOR "NO IMAGE" IN PAGEBUILDER
        var pagebuilder_no_img = "{{ asset('images/no-image.png') }}";
        // SET URL INTERNAL IN PAGEBUILDER
        var pagebuilder_url = "{{ url('/') }}";
        
        // SET OPTIONS FOR SECTION STYLE IN PAGEBUILDER
        var options_style_section = [ 'Blue Stone', 'White Smoke', 'Cameo' ];
        // SET OPTIONS FOR BUTTON STYLE IN PAGEBUILDER
        var options_style_button = [ 'Mandy', 'Cameo', 'Blue Stone' ];
        // SET OPTIONS FOR MASTHEAD (TITLE & SUBTITLE) IN PAGEBUILDER
        var options_style_masthead = [ 'Blue Stone', 'White Smoke', 'Cameo', 'Mandy' ];
        // KINIDI Tech - PageBuilder - Config (END)

        $( document ).ready(function() {
            // KINIDI Tech - PageBuilder (Page) - Content Loader (BEGIN)
            @include('_vendors.pagebuilder.script_page_helper')
            // KINIDI Tech - PageBuilder (Page) - Content Loader (END)
        });

        function confirm_preview() {
            if (confirm("You will open preview page in new window, but make sure to save all changes first to see the changes applied.")) {
                return true;
            }
            return false;
        }
    </script>
@endsection