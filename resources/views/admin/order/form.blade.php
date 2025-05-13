@extends('_template_adm.master')

@php
    use App\Libraries\Helper;

    $pagetitle = ucwords(lang('order', $translations));
    if (isset($data)) {
        $pagetitle .= ' ('.ucwords(lang('edit', $translations)).')';
    } else {
        $pagetitle .= ' ('.ucwords(lang('new', $translations)).')';
        $data       = null;
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
                        <form id="form_data" class="form-horizontal form-label-left">
                            @php
                                // set_input_form($type, $input_name, $label_name, $data, $errors, $required = false, $config = null)

                                $config             = new \stdClass();
                                $config->attributes = 'readonly';
                                
                                echo set_input_form('text', 'buyer_fullname', ucwords(lang('buyer name', $translations)), $data, $errors, false, $config);

                                echo set_input_form('text', 'buyer_phone_number', ucwords(lang('buyer phone', $translations)), $data, $errors, false, $config);

                                echo set_input_form('text', 'product_item_name', ucwords(lang('product item', $translations)), $data, $errors, false, $config);

                                echo set_input_form('text', 'product_item_variant_name', ucwords(lang('product variant item', $translations)), $data, $errors, false, $config);

                                echo set_input_form('text', 'price_per_item', ucwords(lang('price per item', $translations)), $data, $errors, false, $config);

                                echo set_input_form('text', 'price_discount', ucwords(lang('price discount', $translations)), $data, $errors, false, $config);

                                echo set_input_form('text', 'price_subtotal', ucwords(lang('price subtotal', $translations)), $data, $errors, false, $config);

                                echo set_input_form('text', 'qty', ucwords(lang('quantity', $translations)), $data, $errors, false, $config);

                                echo set_input_form('text', 'price_shipping', ucwords(lang('price shipping', $translations)), $data, $errors, false, $config);

                                echo set_input_form('text', 'price_total', ucwords(lang('price total', $translations)), $data, $errors, false, $config);

                                echo set_input_form('text', 'payment_status_label', ucwords(lang('payment status', $translations)), $data, $errors, false, $config);

                                echo set_input_form('text', 'progress_status_label', ucwords(lang('progress status', $translations)), $data, $errors, false, $config);

                                echo set_input_form('text', 'shipping_number', ucwords(lang('shipping number', $translations)), $data, $errors, false, $config);

                                echo set_input_form('text', 'shipped_at', ucwords(lang('shipped at', $translations)), $data, $errors, false, $config);

                                // ONLY SHOW WHEN EDIT
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
                                <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                    <a href="{{ route('admin.order') }}" class="btn btn-default"><i class="fa fa-times"></i>&nbsp; 
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
    <!-- Select2 -->
    @include('_vendors.select2.css')
    <!-- bootstrap-datetimepicker -->
    @include('_vendors.datetimepicker.css')
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
@endsection