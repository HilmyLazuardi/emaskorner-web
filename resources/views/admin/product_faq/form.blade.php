@extends('_template_adm.master')

@php
    use App\Libraries\Helper;

    $pagetitle = 'FAQ';
    if (isset($data)) {
        $pagetitle .= ' ('.ucwords(lang('edit', $translations)).')';
        $link       = route('admin.product_faq.update', ['product_item_id' => $raw_product_item_id, 'id' => $raw_id]);
    } else {
        $pagetitle .= ' ('.ucwords(lang('new', $translations)).')';
        $link       = route('admin.product_faq.store', $raw_product_item_id);
        $data       = null;
    }
    $pagetitle .=  ' - ' . $header->name;
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

                                $config             = new \stdClass();
                                $config->autosize   = true;
                                echo set_input_form('textarea', 'question', ucwords(lang('question', $translations)), $data, $errors, true, $config);

                                $config             = new \stdClass();
                                $config->autosize   = true;
                                echo set_input_form('textarea', 'answer', ucwords(lang('answer', $translations)), $data, $errors, true, $config);

                                // only show when edit
                                if ($data) {
                                    $time_ago           = Helper::time_ago(strtotime($data->created_at), lang('ago', $translations), Helper::get_periods($translations));
                                    $config             = new \stdClass();
                                    $config->attributes = 'readonly';
                                    $config->value      = Helper::locale_timestamp($data->created_at) . ' - ' . $time_ago;
                                    echo set_input_form('text', 'created_at', ucwords(lang('created at', $translations)), $data, $errors, false, $config);
                                    
                                    $time_ago           = Helper::time_ago(strtotime($data->updated_at), lang('ago', $translations), Helper::get_periods($translations));
                                    $config             = new \stdClass();
                                    $config->attributes = 'readonly';
                                    $config->value      = Helper::locale_timestamp($data->updated_at) . ' - ' . $time_ago;
                                    echo set_input_form('text', 'updated_at', ucwords(lang('last updated at', $translations)), $data, $errors, false, $config);
                                }
                            @endphp
                            
                            <div class="ln_solid"></div>

                            <div class="form-group">
                                @php
                                    if (Session::has('add_new_product') && Session::get('add_new_product') == urlencode($raw_product_item_id)) {
                                        $config         = new \stdClass();
                                        $config->value  = 1;
                                        echo set_input_form('hidden', 'stay_on_page', ucwords(lang('stay on this page after submitting', $translations)), $data, $errors, true, $config);
                                    } else {
                                        echo set_input_form('switch', 'stay_on_page', ucfirst(lang('stay on this page after submitting', $translations)), $data, $errors, false);
                                    }
                                @endphp
                                
                                <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                    @if (Session::has('add_new_product') && Session::get('add_new_product') == urlencode($raw_product_item_id))
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa fa-plus-circle"></i>&nbsp; Tambah FAQ
                                        </button>
                                        <span class="btn btn-default" style="background:#D3A381; color:#FFFFFF;" onclick="preview('{{ $raw_product_item_id }}');"><i class="fa fa-eye"></i>&nbsp; 
                                            {{ ucwords(lang('preview', $translations)) }}
                                        </span>
                                        <a href="{{ route('admin.product_item.finish', urlencode($raw_product_item_id)) }}" class="btn btn-success">
                                            <i class="fa fa-check-circle"></i>&nbsp; Selesai
                                        </a>
                                    @else
                                        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i>&nbsp; 
                                            @if (isset($data))
                                                {{ ucwords(lang('save', $translations)) }}
                                            @else
                                                {{ ucwords(lang('submit', $translations)) }}
                                            @endif
                                        </button>

                                        <a href="{{ route('admin.product_faq', $raw_product_item_id) }}" class="btn btn-default"><i class="fa fa-times"></i>&nbsp; 
                                            {{ ucwords(lang('close', $translations)) }}
                                        </a>

                                        @if (isset($raw_id))
                                            | <span class="btn btn-danger" onclick="$('#form_delete').submit()"><i class="fa fa-trash"></i></span>
                                        @endif
                                    @endif
                                </div>
                            </div>

                        </form>

                        @if (isset($raw_id))
                            <form id="form_delete" action="{{ route('admin.product_faq.delete', $raw_product_item_id) }}" method="POST" onsubmit="return confirm('{!! lang('Are you sure to delete this #item?', $translations, ['#item' => 'data']) !!}');" style="display: none">
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
    <!-- PhotoSwipe -->
    @include('_vendors.photoswipe.css')
@endsection

@section('script')
    <!-- Switchery -->
    @include('_vendors.switchery.script')
    <!-- Textarea Autosize -->
    @include('_vendors.autosize.script')
    <!-- PhotoSwipe -->
    @include('_vendors.photoswipe.script')

    <script>
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
    </script>
@endsection