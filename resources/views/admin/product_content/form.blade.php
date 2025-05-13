@extends('_template_adm.master')

@php
    $pagetitle  = ucwords(lang('product content', $translations)) . ' - ' . $data->name; 
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
                        <h2>{{ ucwords(lang('content element', $translations)) }}</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <br />
                        <form data-parsley-validate class="form-horizontal form-label-left" action="{{ $link }}" method="POST" enctype="multipart/form-data">
                            {{ csrf_field() }}

                            <div class="row">
                                <div class="col-lg-2 custome-lg-2">
                                    <span class="btn btn-primary btn-block" onclick="add_content_element('text')"><i class="fa fa-font"></i>&nbsp; Add Text</span>
                                </div>
                                <div class="col-lg-2 custome-lg-2">
                                    <span class="btn btn-primary btn-block" onclick="add_content_element('image')"><i class="fa fa-image"></i>&nbsp; Add Image</span>
                                </div>
                                <div class="col-lg-2 custome-lg-2">
                                    <span class="btn btn-primary btn-block" onclick="add_content_element('image & text')"><i class="fa fa-image"></i>&nbsp; Add Image & Text</span>
                                </div>
                                <div class="col-lg-2 custome-lg-2">
                                    <span class="btn btn-primary btn-block" onclick="add_content_element('video')"><i class="fa fa-youtube-play"></i>&nbsp; Add Video</span>
                                </div>
                                <div class="col-lg-2 custome-lg-2">
                                    <span class="btn btn-primary btn-block" onclick="add_content_element('video & text')"><i class="fa fa-youtube-play"></i>&nbsp; Add Video & Text</span>
                                </div>
                                {{-- <div class="col-lg-2 custome-lg-2">
                                    <span class="btn btn-primary btn-block" onclick="add_content_element('plain text')"><i class="fa fa-font"></i>&nbsp; Add Plain Text</span><br>
                                </div> --}}

                                <div class="col-lg-12 custome-lg-12">
                                    <div id="content-pagebuilder" role="tablist" aria-multiselectable="true" class="accordion">
                                        {{-- ONLY DISPLAY WHEN EDIT --}}
                                        @if (!empty($data->details))
                                            @php
                                                $content = json_decode($data->details, true);
                                            @endphp

                                            @foreach ($content as $key => $item)
                                                @php
                                                    $uniqid = $key;
                                                    $section_value = $item['section'];
                                                @endphp
                                                @if ($item['type'] == 'text')
                                                    @php
                                                        $text_value = $item['text'];
                                                    @endphp
                                                    <div class="panel" id="pagebuilder_elm_{{ $uniqid }}" style="">
                                                        <input type="hidden" name="v_element_type[{{ $uniqid }}]" value="text">
                                                        <a class="panel-heading collapsed" role="tab" data-toggle="collapse" data-parent="#content-pagebuilder" href="#collapse{{ $uniqid }}" aria-expanded="false">
                                                            <h4 class="panel-title">
                                                                Text - Section <i id="section{{ $uniqid }}">{{ $section_value }}</i>
                                                                <span class="pull-right">
                                                                    <i class="fa fa-sort"></i><i class="fa fa-trash" style="color:red; margin-left: 20px;" onclick="delete_content_element({{ $uniqid }})"></i>
                                                                </span>
                                                            </h4>
                                                        </a>
                                                        <div id="collapse{{ $uniqid }}" class="panel-collapse collapse" role="tabpanel" aria-expanded="false" style="height: 0px;">
                                                            <div class="panel-body">
                                                                <div class="form-group">
                                                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Section <span class="required">*</span></label>
                                                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                                                        <input type="text" autocomplete="off" value="{{ $section_value }}" name="v_element_section[{{ $uniqid }}]" required="required" class="form-control col-md-7 col-xs-12" onblur="set_section_name({{ $uniqid }}, this.value)">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Text <span class="required">*</span></label>
                                                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                                                        <textarea name="v_element_content_text[{{ $uniqid }}]" id="v_element_content_text_{{ $uniqid }}" required="required" class="form-control col-md-7 col-xs-12 text-editor">{{ $text_value }}</textarea>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @elseif ($item['type'] == 'image')
                                                    @php
                                                        // $image_value = asset('uploads/article/content/'.$item['image']);
                                                        $image_value = asset('uploads/page/'.$item['image']);
                                                    @endphp
                                                    <div class="panel" id="pagebuilder_elm_{{ $uniqid }}">
                                                        <input type="hidden" name="v_element_type[{{ $uniqid }}]" value="image">
                                                        <a class="panel-heading collapsed" role="tab" data-toggle="collapse" data-parent="#content-pagebuilder" href="#collapse{{ $uniqid }}" aria-expanded="false">
                                                            <h4 class="panel-title">
                                                                Image - Section <i id="section{{ $uniqid }}">{{ $section_value }}</i>
                                                                <span class="pull-right">
                                                                    <i class="fa fa-sort"></i><i class="fa fa-trash" style="color:red; margin-left: 20px;" onclick="delete_content_element({{ $uniqid }})"></i>
                                                                </span>
                                                            </h4>
                                                        </a>
                                                        <div id="collapse{{ $uniqid }}" class="panel-collapse collapse" role="tabpanel" aria-expanded="false" style="height: 0px;">
                                                            <div class="panel-body">
                                                                <div class="form-group">
                                                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Section <span class="required">*</span></label>
                                                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                                                        <input type="text" autocomplete="off" value="{{ $section_value }}" name="v_element_section[{{ $uniqid }}]" required="required" class="form-control col-md-7 col-xs-12" onblur="set_section_name({{ $uniqid }}, this.value)">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Image</label>
                                                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                                                        <img src="{{ $image_value }}" style="max-width: 200px;max-height: 200px;display: block;margin-left: auto;margin-right: auto;">
                                                                        <input type="file" name="v_element_content_image[{{ $uniqid }}]"  class="form-control col-md-7 col-xs-12" accept=".jpg, .jpeg, .png" onchange="readURL(this, 'before');" style="margin-top:5px">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @elseif ($item['type'] == 'image & text')
                                                    @php
                                                        // $image_value = asset('uploads/article/content/'.$item['image']);
                                                        $image_value = asset('uploads/page/'.$item['image']);
                                                        $text_value = $item['text'];
                                                        $text_position_value = $item['text_position'];
                                                    @endphp
                                                    <div class="panel" id="pagebuilder_elm_{{ $uniqid }}">
                                                        <input type="hidden" name="v_element_type[{{ $uniqid }}]" value="image & text">
                                                        <a class="panel-heading collapsed" role="tab" data-toggle="collapse" data-parent="#content-pagebuilder" href="#collapse{{ $uniqid }}" aria-expanded="false">
                                                            <h4 class="panel-title">
                                                                Image &amp; Text - Section <i id="section{{ $uniqid }}">{{ $section_value }}</i>
                                                                <span class="pull-right">
                                                                    <i class="fa fa-sort"></i><i class="fa fa-trash" style="color:red; margin-left: 20px;" onclick="delete_content_element({{ $uniqid }})"></i>
                                                                </span>
                                                            </h4>
                                                        </a>
                                                        <div id="collapse{{ $uniqid }}" class="panel-collapse collapse" role="tabpanel" aria-expanded="false" style="height: 0px;">
                                                            <div class="panel-body">
                                                                <div class="form-group">
                                                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Section <span class="required">*</span></label>
                                                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                                                        <input type="text" autocomplete="off" value="{{ $section_value }}" name="v_element_section[{{ $uniqid }}]" required="required" class="form-control col-md-7 col-xs-12" onblur="set_section_name({{ $uniqid }}, this.value)">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Image</label>
                                                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                                                        <img src="{{ $image_value }}" style="max-width: 200px;max-height: 200px;display: block;margin-left: auto;margin-right: auto;">
                                                                        <input type="file" name="v_element_content_image[{{ $uniqid }}]" class="form-control col-md-7 col-xs-12" accept=".jpg, .jpeg, .png" onchange="readURL(this, 'before');" style="margin-top:5px">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Text <span class="required">*</span></label>
                                                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                                                        <textarea name="v_element_content_text[{{ $uniqid }}]" id="v_element_content_text_{{ $uniqid }}" required="required" class="form-control col-md-7 col-xs-12 text-editor">{{ $text_value }}</textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Text Position on Image <span class="required">*</span></label>
                                                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                                                        <select name="v_text_position[{{ $uniqid }}]" required="required" class="form-control col-md-7 col-xs-12">
                                                                            <option value="">- Please Choose One -</option>
                                                                            <option value="right" @if ($text_position_value == 'right') selected @endif>Right</option>
                                                                            <option value="left" @if ($text_position_value == 'left') selected @endif>Left</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @elseif ($item['type'] == 'video')
                                                    @php
                                                        $video_value = $item['video'];
                                                    @endphp
                                                    <div class="panel" id="pagebuilder_elm_{{ $uniqid }}">
                                                        <input type="hidden" name="v_element_type[{{ $uniqid }}]" value="video">
                                                        <a class="panel-heading collapsed" role="tab" data-toggle="collapse" data-parent="#content-pagebuilder" href="#collapse{{ $uniqid }}" aria-expanded="false">
                                                            <h4 class="panel-title">
                                                                Video - Section <i id="section{{ $uniqid }}">{{ $section_value }}</i>
                                                                <span class="pull-right">
                                                                    <i class="fa fa-sort"></i>
                                                                    <i class="fa fa-trash" style="color:red; margin-left: 20px;" onclick="delete_content_element({{ $uniqid }})"></i>
                                                                </span>
                                                            </h4>
                                                        </a>
                                                        <div id="collapse{{ $uniqid }}" class="panel-collapse collapse" role="tabpanel" aria-expanded="false" style="height: 0px;">
                                                            <div class="panel-body">
                                                                <div class="form-group">
                                                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Section <span class="required">*</span></label>
                                                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                                                        <input type="text" autocomplete="off" value="{{ $section_value }}" name="v_element_section[{{ $uniqid }}]" required="required" class="form-control col-md-7 col-xs-12" onblur="set_section_name({{ $uniqid }}, this.value)">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Video (YouTube URL) <span class="required">*</span></label>
                                                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                                                        <input type="text" autocomplete="off" name="v_element_content_video[{{ $uniqid }}]" required="required" placeholder="https://www.youtube.com/watch?v=XXXX" class="form-control col-md-7 col-xs-12" value="{{ $video_value }}">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @elseif ($item['type'] == 'video & text')
                                                    @php
                                                        $video_value = $item['video'];
                                                        $text_value = $item['text'];
                                                        $text_position_value = $item['text_position'];
                                                    @endphp
                                                    <div class="panel" id="pagebuilder_elm_{{ $uniqid }}">
                                                        <input type="hidden" name="v_element_type[{{ $uniqid }}]" value="video & text">
                                                        <a class="panel-heading collapsed" role="tab" data-toggle="collapse" data-parent="#content-pagebuilder" href="#collapse{{ $uniqid }}" aria-expanded="false">
                                                            <h4 class="panel-title">
                                                                Video &amp; Text - Section <i id="section{{ $uniqid }}">{{ $section_value }}</i>
                                                                <span class="pull-right">
                                                                    <i class="fa fa-sort"></i><i class="fa fa-trash" style="color:red; margin-left: 20px;" onclick="delete_content_element({{ $uniqid }})"></i>
                                                                </span>
                                                            </h4>
                                                        </a>
                                                        <div id="collapse{{ $uniqid }}" class="panel-collapse collapse" role="tabpanel" aria-expanded="false" style="height: 0px;">
                                                            <div class="panel-body">
                                                                <div class="form-group">
                                                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Section <span class="required">*</span></label>
                                                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                                                        <input type="text" autocomplete="off" value="{{ $section_value }}" name="v_element_section[{{ $uniqid }}]" required="required" class="form-control col-md-7 col-xs-12" onblur="set_section_name({{ $uniqid }}, this.value)">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Video (YouTube URL) <span class="required">*</span></label>
                                                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                                                        <input type="text" autocomplete="off" name="v_element_content_video[{{ $uniqid }}]" required="required" placeholder="https://www.youtube.com/watch?v=XXXX" class="form-control col-md-7 col-xs-12" value="{{ $video_value }}">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Text <span class="required">*</span></label>
                                                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                                                        <textarea name="v_element_content_text[{{ $uniqid }}]" id="v_element_content_text_{{ $uniqid }}" required="required" class="form-control col-md-7 col-xs-12 text-editor">{{ $text_value }}</textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Text Position on Video <span class="required">*</span></label>
                                                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                                                        <select name="v_text_position[{{ $uniqid }}]" required="required" class="form-control col-md-7 col-xs-12">
                                                                            <option value="">- Please Choose One -</option>
                                                                            <option value="right" @if ($text_position_value == 'right') selected @endif>Right</option>
                                                                            <option value="left" @if ($text_position_value == 'left') selected @endif>Left</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @elseif ($item['type'] == 'plain text')
                                                    @php
                                                        $text_value = $item['text'];
                                                    @endphp
                                                    <div class="panel" id="pagebuilder_elm_{{ $uniqid }}" style="">
                                                        <input type="hidden" name="v_element_type[{{ $uniqid }}]" value="text">
                                                        <a class="panel-heading collapsed" role="tab" data-toggle="collapse" data-parent="#content-pagebuilder" href="#collapse{{ $uniqid }}" aria-expanded="false">
                                                            <h4 class="panel-title">
                                                                Plain Text - Section <i id="section{{ $uniqid }}">{{ $section_value }}</i>
                                                                <span class="pull-right">
                                                                    <i class="fa fa-sort"></i><i class="fa fa-trash" style="color:red; margin-left: 20px;" onclick="delete_content_element({{ $uniqid }})"></i>
                                                                </span>
                                                            </h4>
                                                        </a>
                                                        <div id="collapse{{ $uniqid }}" class="panel-collapse collapse" role="tabpanel" aria-expanded="false" style="height: 0px;">
                                                            <div class="panel-body">
                                                                <div class="form-group">
                                                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Section <span class="required">*</span></label>
                                                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                                                        <input type="text" autocomplete="off" value="{{ $section_value }}" name="v_element_section[{{ $uniqid }}]" required="required" class="form-control col-md-7 col-xs-12" onblur="set_section_name({{ $uniqid }}, this.value)">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Plain Text <span class="required">*</span></label>
                                                                    <div class="col-md-9 col-sm-9 col-xs-12">
                                                                        <textarea name="v_element_content_text[{{ $uniqid }}]" required="required" rows="10" class="form-control col-md-7 col-xs-12">{{ $text_value }}</textarea>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        @endif
                                    </div>
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
    <!-- <div class="modal fade modal-content-element-loading" tabindex="-1" role="dialog" aria-hidden="true">
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
    </div> -->
@endsection

@section('css')
    <!-- Switchery -->
    @include('_vendors.switchery.css')
    
    <!-- jQuery UI -->
    <link href="{{ asset('/admin/vendors/jquery-ui/jquery-ui-git.css') }}" rel="stylesheet">
    
    <!-- Page Builder by Vickzkater -->
    @include('_form_element.pagebuilder.css')

    <style>
        .custome-lg-2{
            width: 20%;
            margin-bottom: 20px;
        }
        .custome-lg-12{
            width: 100%;
        }
        @media only screen and (max-width: 1170px){
            .custome-lg-2{
                padding:0 2px;
            }
            .custome-lg-2 .btn{
                font-size: 12px;
            }
        }
    </style>
@endsection

@section('script')
    <!-- Switchery -->
    @include('_vendors.switchery.script')

    <!-- Textarea Autosize -->
    @include('_vendors.autosize.script')
    
    <!-- jQuery Tags Input -->
    @include('_vendors.tagsinput.script')
    
    <!-- TinyMCE -->
    <!-- @include('_vendors.tinymce.script') -->
    
    <!-- jQuery UI -->
    <script src="{{ asset('/admin/vendors/jquery-ui/jquery-ui-git.js') }}"></script>
    
    <!-- TinyMCE -->
    <script src="{{ asset('/admin/vendors/tinymce/js/tinymce/tinymce.min.js') }}"></script>

    <!-- Page Builder by Vickzkater -->
    @include('_form_element.pagebuilder.script')
    <script>
        $(document).ready(function () {
            init_tinymce();
        });
    </script>
@endsection