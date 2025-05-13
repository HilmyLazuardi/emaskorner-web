@extends('_template_adm.master')

@php
    $pagetitle  = ucwords(lang('product content', $translations)) . ' ' . $data->name; 
    $link       = route('admin.product_content.update', $raw_product_item_id);
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

            <!-- <div class="title_right">
                <div class="col-md-5 col-sm-5 col-xs-12 form-group pull-right">
                    <a href="{{ route('admin.product_item.edit', $raw_product_item_id) }}" class="btn btn-round btn-warning" style="float: right;">
                        <i class="fa fa-arrow-left"></i>&nbsp; {{ ucwords(lang('back', $translations)) }}
                    </a>
                </div>
            </div> -->
        </div>

        <div class="clearfix"></div>
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>{{ ucwords(lang('content', $translations)) }}</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <br />
                        <form class="form-horizontal form-label-left" action="{{ $link }}" method="POST" enctype="multipart/form-data">
                            {{ csrf_field() }}

                            <div class="row">
                                <div class="col-lg-12">
                                    <span class="btn btn-primary" onclick="add_section_element(true)"><i class="fa fa-bookmark"></i>&nbsp; {{ ucwords(lang('add section', $translations)) }}</span><br>
                                    <hr>
                                </div>
                                <div class="col-lg-12">
                                    <div id="content-pagebuilder" class="accordion"></div>
                                </div>
                            </div>
                            
                            <div class="ln_solid"></div>

                            <div class="form-group">
                                <div class="col-lg-12 text-center">
                                    <button type="submit" class="btn btn-success btn-lg"><i class="fa fa-save"></i>&nbsp; 
                                        {{ ucwords(lang('save', $translations)) }}
                                    </button>

                                    @if (!is_null($data->details))
                                        <a href="{{ route('admin.product_faq.create', $raw_product_item_id) }}" class="btn btn-primary btn-lg">Lanjut - Set FAQ</a>
                                    @endif
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- modal for add content element -->
    <!-- <div class="modal fade modal-add-content-element" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-title" id="myModalLabel2">Add Content Element</h4>
                </div>
                <div class="modal-body">
                    <span class="btn btn-primary btn-block" onclick="add_content_element_page('masthead', true)"><i class="fa fa-list-alt"></i>&nbsp; Add Masthead</span><br>
                    <span class="btn btn-primary btn-block" onclick="add_content_element_page('text', true)"><i class="fa fa-font"></i>&nbsp; Add Text</span><br>
                    <span class="btn btn-primary btn-block" onclick="add_content_element_page('image', true)"><i class="fa fa-image"></i>&nbsp; Add Image</span><br>
                    <span class="btn btn-primary btn-block" onclick="add_content_element_page('image + text + button', true)"><i class="fa fa-newspaper-o"></i>&nbsp; Add Image + Text + Button</span><br>
                    <span class="btn btn-primary btn-block" onclick="add_content_element_page('video', true)"><i class="fa fa-video-camera"></i>&nbsp; Add Video</span><br>
                    <span class="btn btn-primary btn-block" onclick="add_content_element_page('button', true)"><i class="fa fa-check-square"></i>&nbsp; Add Button</span><br>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div> -->

    <!-- modal for loading content element -->
    <div class="modal fade modal-content-element-loading" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">Content Element</h4>
                </div>
                <div class="modal-body">
                    <h2 class="text-center">
                        <i class="fa fa-spinner fa-spin"></i>&nbsp; PLEASE WAIT...
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <!-- KINIDI Tech - PageBuilder -->
    @include('_vendors.pagebuilder_v1.html')
@endsection

@section('css')
    <!-- Switchery -->
    @include('_vendors.switchery.css')
    
    <!-- jQuery UI -->
    <link href="{{ asset('/admin/vendors/jquery-ui/jquery-ui-git.css') }}" rel="stylesheet">
    <!-- KINIDI Tech - Page Builder -->
    @include('_vendors.pagebuilder_v1.css')
@endsection

@section('script')
    <!-- Switchery -->
    @include('_vendors.switchery.script')
    <!-- Textarea Autosize -->
    @include('_vendors.autosize.script')
    <!-- jQuery Tags Input -->
    @include('_vendors.tagsinput.script')
    <!-- TinyMCE -->
    @include('_vendors.tinymce.script')
    <!-- jQuery UI -->
    <script src="{{ asset('/admin/vendors/jquery-ui/jquery-ui-git.js') }}"></script>
    <!-- KINIDI Tech - Page Builder -->
    @include('_vendors.pagebuilder_v1.script')

    <script>
        // SET DEFAULT IMAGE FOR "NO IMAGE" IN PAGEBUILDER
        var pagebuilder_no_img = "{{ asset('admin/vendors/kiniditech-pagebuilder/no-image.png') }}";
        // SET URL INTERNAL IN PAGEBUILDER
        var pagebuilder_url = "{{ url('/') }}";
        // SET OPTIONS FOR SECTION STYLE IN PAGEBUILDER
        var options_style_section = [ 'White', 'Light Gray', 'Dark Gray' ];
        // SET OPTIONS FOR BUTTON STYLE IN PAGEBUILDER
        var options_style_button = [ 'Red', 'Gray' ];
        // SET OPTIONS FOR MASTHEAD (TITLE & SUBTITLE) IN PAGEBUILDER
        var options_style_masthead = [ 'Black', 'White', 'Red' ];
        // SET PAGEBUILDER MODE
        var pagebuilder_mode = 'landing page';

        $('#master_check_all').on("click", function() {
            var all = $('.check_topic').length;
            var total = $('.check_topic:checked').length;

            if(total == all && $('#master_check_all:checked').length == 0){
                $(".check_topic").removeAttr("checked");
            }else{
                $(".check_topic").prop("checked", "checked");
            }
        });

        function is_all_checked() {
            var all = $('.check_topic').length;
            var total = $('.check_topic:checked').length;

            if(total == all){
                $("#master_check_all").prop("checked", "checked");
            }else{
                $("#master_check_all").removeAttr("checked");
            }
        }

        $( document ).ready(function() {
            @if (isset($data))
                is_all_checked();
            @endif

            // KINIDI Tech - PageBuilder - Content Loader (BEGIN)
            {{-- GENERATE PAGEBUILDER CONTENT ELEMENT FROM DATA --}}
            @if (!empty($data->details))
                // SHOW MODAL LOADING
                // $('.modal-content-element-loading').modal('show');

                @php
                    $show_loading = false;
                    $content = json_decode($data->details);
                @endphp
                @foreach ($content as $key => $value)
                    // Button "Add Section" clicked
                    var v_page_section_id = '{{ $key }}';
                    var v_page_section_data = {
                        v_page_section : encodeURI("{{ $value->v_page_section }}"), 
                        v_page_section_style : "{{ $value->v_page_section_style }}"
                    };
                    add_section_element(true, v_page_section_id, v_page_section_data);

                    // Button "Add Content Element" clicked
                    set_content_container('#list-section-' + v_page_section_id);
                    set_section_page_id(v_page_section_id);

                    // Add Content Elements
                    @foreach ($value->contents as $key2 => $value2)
                        var uniqid_element = '{{ $key2 }}';

                        @if ($value2->v_page_element_type == 'masthead')
                            var v_page_element_data = {
                                v_page_element_section : encodeURI("{{ $value2->v_page_element_section }}"), 
                                v_page_element_status : "{{ $value2->v_page_element_status }}"
                            };
                            // Add Element "Masthead"
                            add_content_element_page('masthead', true, uniqid_element, v_page_element_data);

                            @if (!empty($value2->items))
                                @foreach ($value2->items as $key3 => $value3)
                                    @php
                                        $pb_text_title = '';
                                        if (!empty($value3->v_page_element_title)) {
                                            $pb_text = json_encode($value3->v_page_element_title);
                                            $pb_text_title = substr($pb_text, 1, strlen($pb_text)-2);
                                        }

                                        $pb_text_subtitle = '';
                                        if (!empty($value3->v_page_element_subtitle)) {
                                            $pb_text = json_encode($value3->v_page_element_subtitle);
                                            $pb_text_subtitle = substr($pb_text, 1, strlen($pb_text)-2);
                                        }

                                        $title_style = 'White';
                                        if (!empty($value3->v_page_element_title_style)) {
                                            $title_style = $value3->v_page_element_title_style;
                                        }

                                        $subtitle_style = 'White';
                                        if (!empty($value3->v_page_element_subtitle_style)) {
                                            $subtitle_style = $value3->v_page_element_subtitle_style;
                                        }
                                    @endphp
                                    var uniqid_item = '{{ $key3 }}';
                                    var v_page_element_item_data = {
                                        v_page_element_image_title : encodeURI("{{ $value3->v_page_element_image_title }}"), 
                                        v_page_element_title : encodeURI("{{ $pb_text_title }}"), 
                                        v_page_element_title_style : "{{ $title_style }}",
                                        v_page_element_subtitle : encodeURI("{{ $pb_text_subtitle }}"), 
                                        v_page_element_subtitle_style : "{{ $subtitle_style }}",
                                        v_page_element_alignment : "{{ $value3->v_page_element_alignment }}",
                                        v_page_element_image : "{{ $value3->v_page_element_image }}",
                                        v_page_element_image_mobile : "{{ $value3->v_page_element_image_mobile }}",
                                        v_page_element_status_item : "{{ $value3->v_page_element_status_item }}"
                                    };
                                    var v_page_element_sub_item_data = [];
                                    @if (!empty($value3->sub_items))
                                        @foreach ($value3->sub_items as $value4)
                                            v_page_sub_item = {
                                                v_page_element_button_link_type : "{{ $value4->v_page_element_button_link_type }}",
                                                v_page_element_button_link_internal : "{{ $value4->v_page_element_button_link_internal }}",
                                                v_page_element_button_link_external : "{{ $value4->v_page_element_button_link_external }}",
                                                v_page_element_button_link_target : "{{ $value4->v_page_element_button_link_target }}",
                                                v_page_element_button_label : "{{ $value4->v_page_element_button_label }}",
                                                v_page_element_button_style : "{{ $value4->v_page_element_button_style }}"
                                            };
                                            v_page_element_sub_item_data.push(v_page_sub_item);
                                        @endforeach
                                    @endif
                                    // Button "Add Item" clicked
                                    add_masthead_item(v_page_section_id, uniqid_element, uniqid_item, v_page_element_item_data, v_page_element_sub_item_data);
                                @endforeach
                            @endif
                        @elseif ($value2->v_page_element_type == 'text')
                            @php
                                $show_loading = true;
                            @endphp
                            var v_page_element_data = {
                                v_page_element_section : encodeURI("{{ $value2->v_page_element_section }}"), 
                                v_page_element_status : "{{ $value2->v_page_element_status }}",
                                v_page_element_width : "{{ $value2->v_page_element_width }}",
                                v_page_element_alignment : "{{ $value2->v_page_element_alignment }}",
                                v_page_element_total_item : "{{ $value2->v_page_element_total_item }}"
                            };
                            @if (!empty($value2->items))
                                var v_page_element_item_data = [];
                                @foreach ($value2->items as $key3 => $value3)
                                    @php
                                        $pb_text = '';
                                        if (!empty($value3)) {
                                            $pb_text = json_encode($value3);
                                            $pb_text = substr($pb_text, 1, strlen($pb_text)-2);
                                        }
                                    @endphp
                                    v_page_element_item_data.push(encodeURI("{{ $pb_text }}"));
                                @endforeach
                                v_page_element_data.items = v_page_element_item_data;
                            @endif
                            // Add Element "Text"
                            add_content_element_page('text', true, uniqid_element, v_page_element_data);
                        @elseif ($value2->v_page_element_type == 'image')
                            var v_page_element_data = {
                                v_page_element_section : encodeURI("{{ $value2->v_page_element_section }}"), 
                                v_page_element_status : "{{ $value2->v_page_element_status }}",
                                v_page_element_image_type : "{{ $value2->v_page_element_image_type }}"
                            };
                            // Add Element "Image"
                            add_content_element_page('image', true, uniqid_element, v_page_element_data);

                            @if (!empty($value2->items))
                                @foreach ($value2->items as $key3 => $value3)
                                    var uniqid_item = '{{ $key3 }}';
                                    var v_page_element_item_data = {
                                        v_page_element_image_title : encodeURI("{{ $value3->v_page_element_image_title }}"), 
                                        v_page_element_link_type : "{{ $value3->v_page_element_link_type }}",
                                        v_page_element_link_internal : encodeURI("{{ $value3->v_page_element_link_internal }}"), 
                                        v_page_element_link_external : encodeURI("{{ $value3->v_page_element_link_external }}"), 
                                        v_page_element_link_target : "{{ $value3->v_page_element_link_target }}",
                                        v_page_element_status_item : "{{ $value3->v_page_element_status_item }}",
                                        v_page_element_image : "{{ $value3->v_page_element_image }}"
                                    };
                                    // Button "Add Item" clicked
                                    add_image_item(v_page_section_id, uniqid_element, uniqid_item, v_page_element_item_data);
                                    // Show Link Properties
                                    show_input_link('', uniqid_item);
                                @endforeach
                            @endif
                        @elseif ($value2->v_page_element_type == 'image + text + button')
                            @php
                                $show_loading = true;
                            @endphp
                            var v_page_element_data = {
                                v_page_element_section : encodeURI("{{ $value2->v_page_element_section }}"), 
                                v_page_element_status : "{{ $value2->v_page_element_status }}",
                                v_page_element_alignment : "{{ $value2->v_page_element_alignment }}",
                                v_page_element_total_item : "{{ $value2->v_page_element_total_item }}"
                            };
                            @if (!empty($value2->items))
                                var v_page_element_item_data = [];
                                @foreach ($value2->items as $key3 => $value3)
                                    @php
                                        $pb_text = '';
                                        if (!empty($value3->v_page_element_text)) {
                                            $pb_text = json_encode($value3->v_page_element_text);
                                            $pb_text = substr($pb_text, 1, strlen($pb_text)-2);
                                        }
                                    @endphp
                                    var v_page_item = {
                                        v_page_element_image_title : encodeURI("{{ $value3->v_page_element_image_title }}"), 
                                        v_page_element_image : "{{ $value3->v_page_element_image }}",
                                        v_page_element_text : encodeURI("{{ $pb_text }}"),
                                        v_page_element_button_link_type : "{{ $value3->v_page_element_button_link_type }}",
                                        v_page_element_button_link_internal : encodeURI("{{ $value3->v_page_element_button_link_internal }}"),
                                        v_page_element_button_link_external : encodeURI("{{ $value3->v_page_element_button_link_external }}"),
                                        v_page_element_button_link_target : "{{ $value3->v_page_element_button_link_target }}",
                                        v_page_element_button_label : encodeURI("{{ $value3->v_page_element_button_label }}"),
                                        v_page_element_button_style : "{{ $value3->v_page_element_button_style }}"
                                    };
                                    v_page_element_item_data.push(v_page_item);
                                @endforeach
                                v_page_element_data.items = v_page_element_item_data;
                            @endif
                            // Add Element "Image + Text + Button"
                            add_content_element_page('image + text + button', true, uniqid_element, v_page_element_data);
                        @elseif ($value2->v_page_element_type == 'video')
                            @php
                                $pb_text = '';
                                if (!empty($value2->v_page_element_text)) {
                                    $pb_text = json_encode($value2->v_page_element_text);
                                    $pb_text = substr($pb_text, 1, strlen($pb_text)-2);
                                }
                            @endphp
                            var v_page_element_data = {
                                v_page_element_section : encodeURI("{{ $value2->v_page_element_section }}"), 
                                v_page_element_status : "{{ $value2->v_page_element_status }}",
                                v_page_element_alignment : "{{ $value2->v_page_element_alignment }}",
                                v_page_element_video_type : "{{ $value2->v_page_element_video_type }}",
                                v_page_element_video_title : "{{ $value2->v_page_element_video_title }}",
                                v_page_element_video : encodeURI("{{ $value2->v_page_element_video }}"),
                                v_page_element_text : encodeURI("{{ $pb_text }}"),
                                v_page_element_button_link_type : "{{ $value2->v_page_element_button_link_type }}",
                                v_page_element_button_link_internal : encodeURI("{{ $value2->v_page_element_button_link_internal }}"),
                                v_page_element_button_link_external : encodeURI("{{ $value2->v_page_element_button_link_external }}"),
                                v_page_element_button_link_target : "{{ $value2->v_page_element_button_link_target }}",
                                v_page_element_button_label : encodeURI("{{ $value2->v_page_element_button_label }}"),
                                v_page_element_button_style : "{{ $value2->v_page_element_button_style }}"
                            };
                            // Add Element "Button"
                            add_content_element_page('video', true, uniqid_element, v_page_element_data);

                            show_input_video_text('', uniqid_element);
                            show_input_link('', uniqid_element);
                        @elseif ($value2->v_page_element_type == 'button')
                            var v_page_element_data = {
                                v_page_element_section : encodeURI("{{ $value2->v_page_element_section }}"), 
                                v_page_element_status : "{{ $value2->v_page_element_status }}",
                                v_page_element_alignment : "{{ $value2->v_page_element_alignment }}"
                            };
                            // Add Element "Button"
                            add_content_element_page('button', true, uniqid_element, v_page_element_data);

                            @if (!empty($value2->items))
                                @foreach ($value2->items as $key3 => $value3)
                                    var uniqid_item = '{{ $key3 }}';
                                    var v_page_element_item_data = {
                                        v_page_element_button_label : encodeURI("{{ $value3->v_page_element_button_label }}"), 
                                        v_page_element_button_style : "{{ $value3->v_page_element_button_style }}",
                                        v_page_element_button_link_type : "{{ $value3->v_page_element_button_link_type }}",
                                        v_page_element_button_link_internal : encodeURI("{{ $value3->v_page_element_button_link_internal }}"), 
                                        v_page_element_button_link_external : encodeURI("{{ $value3->v_page_element_button_link_external }}"), 
                                        v_page_element_button_link_target : "{{ $value3->v_page_element_button_link_target }}",
                                        v_page_element_status_item : "{{ $value3->v_page_element_status_item }}"
                                    };
                                    // Button "Add Item" clicked
                                    add_button_item(v_page_section_id, uniqid_element, uniqid_item, v_page_element_item_data);
                                    // Show Link Properties
                                    show_input_link('', uniqid_item);
                                @endforeach
                            @endif
                        @elseif ($value2->v_page_element_type == 'plain')
                            @php
                                $pb_text = '';
                                if (!empty($value2->v_page_element_text)) {
                                    $pb_text = json_encode($value2->v_page_element_text);
                                    $pb_text = substr($pb_text, 1, strlen($pb_text)-2);
                                }
                            @endphp
                            var v_page_element_data = {
                                v_page_element_section : encodeURI("{{ $value2->v_page_element_section }}"), 
                                v_page_element_status : "{{ $value2->v_page_element_status }}",
                                v_page_element_text : encodeURI("{{ $pb_text }}")
                            };
                            // Add Element "Plain"
                            add_content_element_page('plain', true, uniqid_element, v_page_element_data);
                        @endif
                    @endforeach
                @endforeach

                @if ($show_loading)
                    show_loading_pagebuilder();
                @endif
            @endif
            // KINIDI Tech - PageBuilder - Content Loader (END)
        });
    </script>
@endsection