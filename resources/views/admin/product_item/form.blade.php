@extends('_template_adm.master')

@php
    use App\Libraries\Helper;

    $pagetitle = ucwords(lang('product item', $translations));
    if (isset($data)) {
        $pagetitle .= ' ('.ucwords(lang('edit', $translations)).')';
        $link       = route('admin.product_item.update', $raw_id);
        $update     = 'true';
    } else {
        $pagetitle .= ' ('.ucwords(lang('new', $translations)).')';
        $link       = route('admin.product_item.store');
        $data       = null;
        $update     = 'false';
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
                                
                                $config                 = new \stdClass();
                                $config->defined_data   = $defined_data_categories;
                                $config->placeholder    = '- '.ucwords(lang('please choose one', $translations)).' -';
                                echo set_input_form('select2', 'category_id', ucwords(lang('category', $translations)), $data, $errors, true, $config);

                                // $config                 = new \stdClass();
                                // $config->defined_data   = $defined_data_seller;
                                // $config->placeholder    = '- '.ucwords(lang('please choose one', $translations)).' -';
                                // $config->attributes     = 'disabled="disabled"';
                                // echo set_input_form('select2', 'seller_id', ucwords(lang('seller', $translations)), $data, $errors, true, $config);
                                // echo set_input_form('text', 'seller_id', ucwords(lang('name', $translations)), $data, $errors, true, $config);
                                
                                $config                 = new \stdClass();
                                $config->limit_chars    = 40;
                                echo set_input_form('text', 'name', ucwords(lang('name', $translations)), $data, $errors, true, $config);
                                
                                // $config                 = new \stdClass();
                                // $config->placeholder    = lang('must be unique. if left empty, system will auto-generate this', $translations);
                                // $config->info_text      = '<br><i class="fa fa-info-circle"></i>&nbsp; '.lang("Slug is the part of the URL that explains the page’s content, example: domain.com/slug", $translations);
                                // echo set_input_form('text', 'slug', ucwords(lang('slug', $translations)), $data, $errors, false, $config);

                                $config = new \stdClass();
                                $config->autosize = true;
                                $config->limit_chars = 300;
                                echo set_input_form('textarea', 'summary', ucwords(lang('summary', $translations)), $data, $errors, false, $config);
                            @endphp

                            <!-- MULTIPLE IMAGES -->
                            @php
                                $no_image = asset('images/no-image.png');
                            @endphp
                            <div class="form-group vinput_images">
                                {{-- <label class="control-label col-md-3 col-sm-3 col-xs-12" style="margin-bottom: 10px;">
                                    {{ ucwords(lang('images', $translations)) }}
                                </label> --}}
                                <div class="row" id="sortable">
                                    @php
                                        $disabled = '';
                                        if ((isset($data) && ($data->published_status != 0 && $data->approval_status != 0)) || (isset($data) && ($data->published_status == 1 && $data->approval_status == 0))) {
                                            // $disabled = 'disabled';
                                        }

                                        $img_src = $no_image;
                                        if (isset($data)) {
                                            if ($data->image) {
                                                $img_src = asset($data->image);
                                            }
                                        }
                                    @endphp
                                    <div class="col-md-2 ui-state-default input1" id="input_1" number=1 value="" mime="" base64="">
                                        <label class="cabinet center-block">
                                            <figure>
                                                <img src="" class="imageSelected img-responsive img-thumbnail" 
                                                    id="item-img-output1" />
                                            </figure>
                                            <input type="file" class="item-img1 file center-block" name="file_photo[]"
                                                data-id="item-img1" accept="image/png, image/jpg, image/jpeg" {{ $disabled }}/>
                                            <div id="hiddenDiv1" class="hiddenDiv">
                                                <button type="button" onClick="editAgain(1)">
                                                    <i class="fa fa-crop"></i>
                                                </button>
                                                <button type="button" onClick="removeFile(1)">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </label>
                                    </div>
                                    @php
                                        $img_src2 = $no_image;
                                        if (isset($data)) {
                                            if ($data->image_2) {
                                                $img_src2 = env('MAIN_URL') . '/' . $data->image_2;
                                            }
                                        }
                                    @endphp
                                    <div class="col-md-2 ui-state-default input2" id="input_2" number=2 value="" mime="" base64="">
                                        <label class="cabinet center-block">
                                            <figure>
                                                <img src="" class="imageSelected2 img-responsive img-thumbnail"
                                                    id="item-img-output2" />
                                            </figure>
                                            <input type="file" class="item-img2 file center-block" name="file_photo[]"
                                                data-id="item-img2" accept="image/png, image/jpg, image/jpeg" {{ $disabled }}/>
                                            <div id="hiddenDiv2" class="hiddenDiv">
                                                <button type="button" onClick="editAgain(2)">
                                                    <i class="fa fa-crop"></i>
                                                </button>
                                                <button type="button" onClick="removeFile(2)">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </label>
                                    </div>
                                    @php
                                        $img_src3 = $no_image;
                                        if (isset($data)) {
                                            if ($data->image_3) {
                                                $img_src3 = env('MAIN_URL') . '/' . $data->image_3;
                                            }
                                        }
                                    @endphp
                                    <div class="col-md-2 ui-state-default input3" id="input_3" number=3 value="" mime="" base64="">
                                        <label class="cabinet center-block">
                                            <figure>
                                                <img src="" class="imageSelected3 img-responsive img-thumbnail"
                                                    id="item-img-output3" />
                                            </figure>
                                            <input type="file" class="item-img3 file center-block" name="file_photo[]"
                                                data-id="item-img3" accept="image/png, image/jpg, image/jpeg" {{ $disabled }}/>
                                            <div id="hiddenDiv3" class="hiddenDiv">
                                                <button type="button" onClick="editAgain(3)">
                                                    <i class="fa fa-crop"></i>
                                                </button>
                                                <button type="button" onClick="removeFile(3)">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </label>
                                    </div>
                                    @php
                                        $img_src4 = $no_image;
                                        if (isset($data)) {
                                            if ($data->image_4) {
                                                $img_src4 = env('MAIN_URL') . '/' . $data->image_4;
                                            }
                                        }
                                    @endphp
                                    <div class="col-md-2 ui-state-default input4" id="input_4" number=4 value="" mime="" base64="">
                                        <label class="cabinet center-block">
                                            <figure>
                                                <img src="" class="imageSelected4 img-responsive img-thumbnail"
                                                    id="item-img-output4" />
                                            </figure>
                                            <input type="file" class="item-img4 file center-block" name="file_photo[]"
                                                data-id="item-img4" accept="image/png, image/jpg, image/jpeg" {{ $disabled }}/>
                                            <div id="hiddenDiv4" class="hiddenDiv">
                                                <button type="button" onClick="editAgain(4)">
                                                    <i class="fa fa-crop"></i>
                                                </button>
                                                <button type="button" onClick="removeFile(4)">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </label>
                                    </div>
                                    @php
                                        $img_src5 = $no_image;
                                        if (isset($data)) {
                                            if ($data->image_5) {
                                                $img_src5 = env('MAIN_URL') . '/' . $data->image_5;
                                            }
                                        }
                                    @endphp
                                    <div class="col-md-2 ui-state-default input5" id="input_5" number=5 value="" mime="" base64="">
                                        <label class="cabinet center-block">
                                            <figure>
                                                <img src="" class="imageSelected5 img-responsive img-thumbnail"
                                                    id="item-img-output5" />
                                            </figure>
                                            <input type="file" class="item-img5 file center-block" name="file_photo[]"
                                                data-id="item-img5" accept="image/png, image/jpg, image/jpeg" {{ $disabled }}/>
                                            <div id="hiddenDiv5" class="hiddenDiv">
                                                <button type="button" onClick="editAgain(5)">
                                                    <i class="fa fa-crop"></i>
                                                </button>
                                                <button type="button" onClick="removeFile(5)">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <input type="hidden" id="attachmentImage" name="attachments[]">
                                <input type="hidden" id="tempBase64ImageDump" name="">
                                {{-- <input type="file" name="attachments[]"> --}}

                                <div class="modal fade" id="cropImagePop" tabindex="-1" role="dialog"
                                    aria-labelledby="myModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                                <h4 class="modal-title" id="myModalLabel">Edit Photo</h4>
                                            </div>
                                            <div class="modal-body">
                                                <div id="upload-demo" class="center-block"></div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-default" data-dismiss="modal">
                                                    Close
                                                </button>
                                                <button type="button" id="cropImageBtn" class="btn btn-primary">
                                                    Crop
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @php
                                // $config                 = new \stdClass();
                                // $config->limit_chars    = 40;
                                // echo set_input_form('text', 'name', ucwords(lang('name', $translations)), $data, $errors, true, $config);
                                
                                // $config                 = new \stdClass();
                                // $config->placeholder    = lang('must be unique. if left empty, system will auto-generate this', $translations);
                                // $config->info_text      = '<br><i class="fa fa-info-circle"></i>&nbsp; '.lang("Slug is the part of the URL that explains the page’s content, example: domain.com/slug", $translations);
                                // echo set_input_form('text', 'slug', ucwords(lang('slug', $translations)), $data, $errors, false, $config);

                                // $config = new \stdClass();
                                // $config->autosize = true;
                                // $config->limit_chars = 300;
                                // echo set_input_form('textarea', 'summary', ucwords(lang('summary', $translations)), $data, $errors, false, $config);

                                // $config                 = new \stdClass();
                                // $config->placeholder    = 'min 1';
                                // echo set_input_form('number', 'qty', ucwords(lang('quantity', $translations)), $data, $errors, true, $config);

                                // if (isset($data)) {
                                //     $config                 = new \stdClass();
                                //     $config->attributes     = 'readonly';
                                //     echo set_input_form('number', 'qty_booked', ucwords(lang('Barang Terbooking', $translations)), $data, $errors, false, $config);

                                //     $config                 = new \stdClass();
                                //     $config->attributes     = 'readonly';
                                //     echo set_input_form('number', 'qty_sold', ucwords(lang('Barang Terjual', $translations)), $data, $errors, false, $config);
                                // }

                                // $config                 = new \stdClass();
                                // $config->input_addon    = 'Rp';
                                // echo set_input_form('number_format', 'price', ucwords(lang('price', $translations)), $data, $errors, true, $config);

                                $config             = new \stdClass();
                                $config->default    = '';
                                $config->info_text = ' <i class="fa fa-info-circle"></i> Aktifkan fitur ini untuk mewajibkan pembeli menggunakan asurasi pengiriman.';
                                echo set_input_form('switch', 'need_insurance', ucwords(lang('wajib asuransi', $translations)), $data, $errors, false, $config);

                                // $config                 = new \stdClass();
                                // $config->attributes     = 'readonly';
                                // $config->placeholder    = 'dd/mm/yyyy';
                                // echo set_input_form('datepicker', 'campaign_start', ucwords(lang('campaign start', $translations)), $data, $errors, true, $config);

                                // $config                 = new \stdClass();
                                // $config->attributes     = 'readonly';
                                // $config->placeholder    = 'dd/mm/yyyy';
                                // echo set_input_form('datepicker', 'campaign_end', ucwords(lang('campaign end', $translations)), $data, $errors, true, $config);

                                // $config             = new \stdClass();
                                // $config->default    = 'checked';
                                // echo set_input_form('switch', 'approval_status', ucwords(lang('approval status', $translations)), $data, $errors, false, $config);

                                $config             = new \stdClass();
                                $config->default    = 'checked';
                                echo set_input_form('switch', 'published_status', ucwords(lang('published status', $translations)), $data, $errors, false, $config);

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

                            <div class="form-group">
                                @if($data)
                                <div class="ln_solid"></div>
                                    <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                        <span class="btn btn-default" style="background:#D3A381; color:#FFFFFF;" onclick="preview('{{ $raw_id }}');"><i class="fa fa-eye"></i>&nbsp; 
                                            {{ ucwords(lang('preview', $translations)) }}
                                        </span>
                                        <a href="{{ route('admin.product_variant', $raw_id) }}" class="btn btn-primary"><i class="fa fa-th-large"></i>&nbsp; 
                                            {{ ucwords(lang('set', $translations)) }} {{ ucwords(lang('variant', $translations)) }}
                                        </a>
                                        <a href="{{ route('admin.product_content', $raw_id) }}" class="btn btn-warning"><i class="fa fa-info-circle"></i>&nbsp; 
                                            {{ ucwords(lang('set', $translations)) }} {{ ucwords(lang('description', $translations)) }}
                                        </a>
                                        <a href="{{ route('admin.product_faq', $raw_id) }}" class="btn btn-info"><i class="fa fa-question-circle"></i>&nbsp; 
                                            {{ ucwords(lang('set', $translations)) }} FAQ
                                        </a>
                                    </div>
                                @endif
                            </div>

                            <div class="ln_solid"></div>

                            <div class="form-group">
                                @php
                                    if (isset($data)) {
                                        echo set_input_form('switch', 'stay_on_page', ucfirst(lang('stay on this page after submitting', $translations)), $data, $errors, false);
                                    }
                                @endphp
                                <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i>&nbsp; 
                                        @if (isset($data))
                                            {{ ucwords(lang('save', $translations)) }}
                                        @else
                                            {{ ucwords(lang('submit', $translations)) }}
                                        @endif
                                    </button>
                                    <a href="{{ route('admin.product_item') }}" class="btn btn-default"><i class="fa fa-times"></i>&nbsp; 
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
                            <form id="form_delete" action="{{ route('admin.product_item.delete') }}" method="POST" onsubmit="return confirm('{!! lang('Are you sure to delete this #item?', $translations, ['#item' => lang('product item', $translations)]) !!}');" style="display: none">
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
    <!-- croppie -->
    @include('_vendors.croppie.css')
    <style>
        .mp_img {
            width: 120px;
        }

        .cr-boundary {
            background: black;
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
    <!-- bootstrap-datetimepicker -->
    @include('_vendors.datetimepicker.script')
    <!-- croppie -->
    @include('_vendors.croppie.script')

    <script>
        show_loading();

        var update = JSON.parse('{{ $update }}');
        const toDataURL = url => fetch(url)
            .then(response => response.blob())
            .then(blob => new Promise((resolve, reject) => {
                const reader = new FileReader()
                reader.onloadend = () => resolve(reader.result)
                reader.onerror = reject
                reader.readAsDataURL(blob)
        }))

        function preview(id) {
            $.ajax({
                type: "POST",
                url: "{{ route('web.product.ajax_validate_preview_item') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id
                },
                beforeSend: function () {
                    // do something before send the data
                },
            })
            .done(function (response) {
                // Callback handler that will be called on success
                if (typeof response != 'undefined') {
                    if (response.status == true) {
                        var new_tab = window.open(response.data, '_blank');
                    } else {
                        // FAILED RESPONSE
                        alert(response.message);
                    }
                } else {
                    alert('Server not respond, please try again.');
                }
            })
            .fail(function (jqXHR, textStatus, errorThrown) {
                // Callback handler that will be called on failure

                // Log the error to the console
                console.error("The following error occurred: " + textStatus, errorThrown);

                alert("Gagal preview produk, silahkan coba lagi atau hubungi admin.");
            })
            .always(function () {
                // Callback handler that will be called regardless
                // if the request failed or succeeded
            });
        }

        $(document).ready(function() {
            if (update) {
                var existing_images_raw = '{{ $multiple_images }}';
                var existing_image_tmp = $("<div/>").html(existing_images_raw).text();
                var existing_image = JSON.parse(existing_image_tmp);
                ImagesSrc=[];
                for(let i=0; i<existing_image.length; i++) {
                    var tempID = i + 1;
                    var tempImgLink = existing_image[i].src_url
                    var tempImgMime = existing_image[i].type
                    var tempImgBase64 = existing_image[i].src_base64
                    $("#input_"+tempID).attr("value",tempImgLink)
                    $("#input_"+tempID).attr("base64",tempImgBase64)
                    $("#input_"+tempID).attr("mime",tempImgMime)
                    ImagesSrc.push({ id: tempID, base64:tempImgBase64, src: tempImgLink,type:tempImgMime});
                };
            }

            hide_loading();
        });

        $("form#form_data").submit(function(e) {
            show_loading();
        });
    </script>

    {{-- CROPPIE CUSTOM SCRIPT --}}
    <script>
        let ImagesSrc = [
            {id:1,base64:"",src:"",type:""},
            {id:2,base64:"",src:"",type:""},
            {id:3,base64:"",src:"",type:""},
            {id:4,base64:"",src:"",type:""},
            {id:5,base64:"",src:"",type:""},
        ];

        $(function() {
            var disabled = '{{ $disabled }}';

            if (disabled != 'disabled') {
                $('#sortable').sortable({
                    start: function(event, ui) {
                        tempStart = ui.item.index();
                    },
                    stop: function(event, ui) {
                        tempEnd = ui.item.index();
                        $('#attachmentImage').val(JSON.stringify(ImagesSrc));

                        var sortedVal = $( "#sortable" ).sortable('toArray',{attribute:'value'});
                        var sortedID = $( "#sortable" ).sortable('toArray',{attribute:'number'});
                        var sortedIDs = $( "#sortable" ).sortable( "toArray" );
                        var sortedMime = $( "#sortable" ).sortable('toArray',{attribute:'mime'});
                        var sortedBase64 = $( "#sortable" ).sortable('toArray',{attribute:'base64'});

                        ImagesSrc = [];
                        for(let i=0; i<sortedVal.length; i++) {
                            ImagesSrc.push({ id: sortedID[i],base64:sortedBase64[i] , src: sortedVal[i],type:sortedMime[i]});
                        };
                        $('#attachmentImage').val(JSON.stringify(ImagesSrc));
                    }
                });
            }
        });

        // GET EXISTING IMAGE DATA
        var no_image = "{{ $no_image }}"
        var img_src = "{{ $img_src }}"
        var img_src2 = "{{ $img_src2 }}"
        var img_src3 = "{{ $img_src3 }}"
        var img_src4 = "{{ $img_src4 }}"
        var img_src5 = "{{ $img_src5 }}"

        // LOAD THE PREVIEW
        $("#item-img-output1").attr("src", img_src);
        $("#item-img-output2").attr("src", img_src2);
        $("#item-img-output3").attr("src", img_src3);
        $("#item-img-output4").attr("src", img_src4);
        $("#item-img-output5").attr("src", img_src5);

        // SETUP BUTTON CROP AND REMOVE
        if ($("#item-img-output1").attr("src") != no_image) {
            $("#hiddenDiv1").css("display", "block");
        }
        if ($("#item-img-output2").attr("src") != no_image) {
            $("#hiddenDiv2").css("display", "block");
        }
        if ($("#item-img-output3").attr("src") != no_image) {
            $("#hiddenDiv3").css("display", "block");
        }
        if ($("#item-img-output4").attr("src") != no_image) {
            $("#hiddenDiv4").css("display", "block");
        }
        if ($("#item-img-output5").attr("src") != no_image) {
            $("#hiddenDiv5").css("display", "block");
        }

        // GLOBAL FUNC
        // Start upload preview image
        var $uploadCrop,
            tempFilename,
            rawImg,
            imageId,
            tempImgEdit,
            tempInputEdit,
            tempIDholder,
            tempMime,
            tempBase64,
            tempEditedImage64;
        

        function readFile(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $(".upload-demo").addClass("ready");
                    $("#cropImagePop").modal("show");
                    rawImg = e.target.result;
                    tempBase64 = e.target.result
                };
                tempMime = input.files[0].type;
                reader.readAsDataURL(input.files[0]);
            } else if (input[0].files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $(".upload-demo").addClass("ready");
                    $("#cropImagePop").modal("show");
                    rawImg = e.target.result;
                    tempBase64 = e.target.result
                };
                tempMime= input[0].files[0].type;
                reader.readAsDataURL(input[0].files[0]);
            }
            else {
                // alert("Sorry - you're browser doesn't support the FileReader API");
                $("#upload-demo").croppie('destroy')
                $("#upload-demo").croppie({url:tempEditedImage64,
                    viewport: {
                        width: 400,
                        height: 400,
                    },
                    enforceBoundary: false,
                    enableExif: true,
                })
                tempBase64 = tempEditedImage64;
                $(".upload-demo").addClass("ready");
                $("#cropImagePop").modal("show");
                rawImg = tempEditedImage64;
            }
        }

        $uploadCrop = $("#upload-demo").croppie({
            viewport: {
                width: 400,
                height: 400,
            },
            enforceBoundary: false,
            enableExif: true,
        });
        $("#cropImagePop").on("shown.bs.modal", function() {
            $uploadCrop
                .croppie("bind", {
                    url: rawImg,
                })
                .then(function() {
                    // console.log("jQuery bind complete");
                });
        });
        // END GLOBAL FUNC

        $(".item-img1").on("change", function() {
            imageId = $(this).data("id");
            tempFilename = $(this).val();
            tempIDholder = 1;
            $("#cancelCropBtn").data("id", imageId);
            readFile(this);
        });
        $(".item-img2").on("change", function() {
            imageId = $(this).data("id");
            tempFilename = $(this).val();
            tempIDholder = 2;
            $("#cancelCropBtn").data("id", imageId);
            readFile(this);
        });
        $(".item-img3").on("change", function() {
            imageId = $(this).data("id");
            tempFilename = $(this).val();
            tempIDholder = 3;
            $("#cancelCropBtn").data("id", imageId);
            readFile(this);
        });
        $(".item-img4").on("change", function() {
            imageId = $(this).data("id");
            tempFilename = $(this).val();
            tempIDholder = 4;
            $("#cancelCropBtn").data("id", imageId);
            readFile(this);
        });
        $(".item-img5").on("change", function() {
            imageId = $(this).data("id");
            tempFilename = $(this).val();
            tempIDholder = 5;
            $("#cancelCropBtn").data("id", imageId);
            readFile(this);
        });

        $("#cropImageBtn").on("click", function(ev) {
            $uploadCrop
                .croppie("result", {
                    type: "base64",
                    format: "jpeg",
                    size: {
                        width: 400,
                        height: 400
                    },
                })
                .then(function(resp) {
                    $("#item-img-output" + tempIDholder).attr("src", resp);
                    $("#cropImagePop").modal("hide");

                    objIndex = ImagesSrc.findIndex((obj => obj.id == tempIDholder));
                    ImagesSrc[objIndex].src = $(`#item-img-output${tempIDholder}`).attr("src")
                    ImagesSrc[objIndex].type = tempMime
                    ImagesSrc[objIndex].base64 = tempBase64
                    $("#input_"+tempIDholder).attr("value",$(`#item-img-output${tempIDholder}`).attr("src"))
                    $("#input_"+tempIDholder).attr("mime",tempMime)
                    $("#input_"+tempIDholder).attr("base64",tempBase64)
                    $('#attachmentImage').val(JSON.stringify(ImagesSrc));



                    if ($("#item-img-output1").attr("src") != no_image) {
                        $("#hiddenDiv1").css("display", "block");
                    }
                    if ($("#item-img-output2").attr("src") != no_image) {
                        $("#hiddenDiv2").css("display", "block");
                    }
                    if ($("#item-img-output3").attr("src") != no_image) {
                        $("#hiddenDiv3").css("display", "block");
                    }
                    if ($("#item-img-output4").attr("src") != no_image) {
                        $("#hiddenDiv4").css("display", "block");
                    }
                    if ($("#item-img-output5").attr("src") != no_image) {
                        $("#hiddenDiv5").css("display", "block");
                    }
                });
        });
        // End upload preview image

        function editAgain(id) {
            tempImgEdit = $("#item-img-output" + id).attr("src");
            tempInputEdit = $(".item-img" + id);
            tempIDholder = id;
            tempEditedImage64 = $("#input_" + id).attr("base64");
            $("#cancelCropBtn").data("id", "item-img" + id);
            readFile(tempInputEdit);
            objIndex = ImagesSrc.findIndex((obj => obj.id == id));
            tempMime = ImagesSrc[objIndex].type;
        }

        function removeFile(id) {
            $("#input_"+id).attr("value","")
            $("#input_"+id).attr("mime","")
            $("#input_"+id).attr("base64","")
            objIndex = ImagesSrc.findIndex((obj => obj.id == id));
            ImagesSrc[objIndex].src = null;
            ImagesSrc[objIndex].type = null;
            ImagesSrc[objIndex].base64 = null;
            $("#hiddenDiv" + id).css("display", "none");
            $("#item-img-output" + id).attr("src", no_image);
            $('#attachmentImage').val(JSON.stringify(ImagesSrc));
        }
    </script>
@endsection