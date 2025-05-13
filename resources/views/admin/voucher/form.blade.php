@extends('_template_adm.master')

@php
    use App\Libraries\Helper;

    $pagetitle = ucwords(lang('voucher', $translations));
    if (isset($data)) {
        $pagetitle .= ' ('.ucwords(lang('edit', $translations)).')';
        $link       = route('admin.voucher.update', $raw_id);
        $update     = 'true';
    } else {
        $pagetitle .= ' ('.ucwords(lang('new', $translations)).')';
        $link       = route('admin.voucher.store');
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
                                $config->limit_chars    = 50;
                                echo set_input_form('text', 'unique_code', ucwords(lang('unique code', $translations)), $data, $errors, true, $config);
                                
                                $config                 = new \stdClass();
                                $config->placeholder    = lang('voucher name', $translations);
                                echo set_input_form('text', 'name', ucwords(lang('name', $translations)), $data, $errors, false, $config);

                                // DEINFED DATA VOUCHER TYPE START
                                $opt_voucher_type_1       = new \stdClass();
                                $opt_voucher_type_1->id   = 'shipping';
                                $opt_voucher_type_1->name = ucwords($opt_voucher_type_1->id);
                                $defined_data_voucher_type[] = $opt_voucher_type_1;

                                $opt_voucher_type_2          = new \stdClass();
                                $opt_voucher_type_2->id      = 'transaction';
                                $opt_voucher_type_2->name    = ucwords($opt_voucher_type_2->id);
                                $defined_data_voucher_type[] = $opt_voucher_type_2;
                                // DEINFED DATA VOUCHER TYPE END
                                
                                $config               = new \stdClass();
                                $config->defined_data = $defined_data_voucher_type;
                                $config->field_text   = 'name';
                                $config->placeholder  = '- '.ucwords(lang('please choose one', $translations)).' -';
                                echo set_input_form('select2', 'voucher_type', ucfirst(lang('voucher type', $translations)), $data, $errors, true, $config);

                                // DEINFED DATA DISCOUNT TYPE START
                                $opt_discount_type_1          = new \stdClass();
                                $opt_discount_type_1->id      = 'amount';
                                $opt_discount_type_1->name    = ucwords($opt_discount_type_1->id);
                                $defined_data_discount_type[] = $opt_discount_type_1;

                                $opt_discount_type_2          = new \stdClass();
                                $opt_discount_type_2->id      = 'percentage';
                                $opt_discount_type_2->name    = ucwords($opt_discount_type_2->id);
                                $defined_data_discount_type[] = $opt_discount_type_2;
                                // DEINFED DATA DISCOUNT TYPE END

                                $config               = new \stdClass();
                                $config->defined_data = $defined_data_discount_type;
                                $config->field_text   = 'name';
                                $config->placeholder  = '- '.ucwords(lang('please choose one', $translations)).' -';
                                echo set_input_form('select2', 'discount_type', ucfirst(lang('discount type', $translations)), $data, $errors, true, $config);

                                $config                 = new \stdClass();
                                echo set_input_form('decimal_format', 'discount_value', ucwords(lang('discount value', $translations)), $data, $errors, true, $config);

                                $config                 = new \stdClass();
                                $config->input_addon    = 'Rp';
                                $config->info_text      = '<br><i class="fa fa-info-circle"></i>&nbsp; '.lang("max nominal value of discount for percentage type / if it is not filled then it is not limited", $translations);
                                echo set_input_form('number_format', 'discount_max_amount', ucwords(lang('discount max amount', $translations)), $data, $errors, false, $config);

                                $config                 = new \stdClass();
                                $config->input_addon    = 'Rp';
                                $config->info_text      = '<br><i class="fa fa-info-circle"></i>&nbsp; '.lang("if it is filled with 0, then there is no minimum transaction", $translations);
                                echo set_input_form('number_format', 'min_transaction', ucwords(lang('minimum transaction', $translations)), $data, $errors, true, $config);

                                echo set_input_form('text_editor', 'description', ucwords(lang('description', $translations)), $data, $errors, true);

                                $config                 = new \stdClass();
                                $config->placeholder    = 'min 1';
                                $config->info_text      = '<br><i class="fa fa-info-circle"></i>&nbsp; '.lang("global quota for voucher usage", $translations);
                                echo set_input_form('number', 'qty', ucwords(lang('quantity', $translations)), $data, $errors, true, $config);

                                $config                 = new \stdClass();
                                $config->placeholder    = 'min 1';
                                $config->info_text      = '<br><i class="fa fa-info-circle"></i>&nbsp; '.lang("quota per user for voucher usage", $translations);
                                echo set_input_form('number', 'qty_per_user', ucwords(lang('quantity per user', $translations)), $data, $errors, true, $config);

                                // $config                 = new \stdClass();
                                // $config->attributes     = 'readonly';
                                // $config->placeholder    = 'dd/mm/yyyy';
                                // echo set_input_form('datepicker', 'period_begin', ucwords(lang('period begin', $translations)), $data, $errors, true, $config);

                                // $config                 = new \stdClass();
                                // $config->attributes     = 'readonly';
                                // $config->placeholder    = 'dd/mm/yyyy';
                                // echo set_input_form('datepicker', 'period_end', ucwords(lang('period end', $translations)), $data, $errors, true, $config);

                                $config = new \stdClass();
                                $config->attributes = 'readonly';
                                $config->placeholder = 'dd/mm/yyyy hh:mm';
                                echo set_input_form('datetimepicker', 'period_begin', ucwords(lang('period begin', $translations)), $data, $errors, true, $config);

                                $config = new \stdClass();
                                $config->attributes = 'readonly';
                                $config->placeholder = 'dd/mm/yyyy hh:mm';
                                echo set_input_form('datetimepicker', 'period_end', ucwords(lang('period end', $translations)), $data, $errors, true, $config);

                                $config             = new \stdClass();
                                $config->default    = 'checked';
                                echo set_input_form('switch', 'is_active', ucwords(lang('is active', $translations)), $data, $errors, false, $config);

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
                                    <a href="{{ route('admin.voucher') }}" class="btn btn-default"><i class="fa fa-times"></i>&nbsp; 
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
                            <form id="form_delete" action="{{ route('admin.voucher.delete') }}" method="POST" onsubmit="return confirm('{!! lang('Are you sure to delete this #item?', $translations, ['#item' => lang('product item', $translations)]) !!}');" style="display: none">
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
@endsection

@section('script')
    <!-- Textarea Autosize -->
    @include('_vendors.autosize.script')
    <!-- Switchery -->
    @include('_vendors.switchery.script')
    <!-- Select2 -->
    @include('_vendors.select2.script')
    <!-- Rich Text Editor (WYSIWYG) using TinyMCE -->
    @include('_vendors.tinymce.script')
    <!-- bootstrap-datetimepicker -->
    @include('_vendors.datetimepicker.script')

    <script>
        $(document).ready(function() {
            check_discount_type();
            $('#discount_max_amount').removeAttr('onblur');
        });

        $("form#form_data").submit(function(e) {
            show_loading();
        });

        // UPPERCASE UNIQUE CODE
        $("#unique_code").change(function() {
            var unique_code = $(this).val();

            if (unique_code != null) {
                $(this).val(unique_code.toUpperCase())
            }
        });

        $("#discount_type").change(function() {
            check_discount_type();
        });

        // VALIDATION DISCOUNT TYPE PERCENTAGE >= 100%
        $("#discount_value").change(function() {
            var discount_type = $("#discount_type").val().toLowerCase();
            var discount_value = parseFloat($(this).val());

            if (discount_type == 'percentage') {
                check_percentage_discount(discount_value);
            }
        });

        $('#discount_max_amount').on('blur',function() {
            value = $(this).val();
            if (value > 0) {
                $(this).val(number_formatting(value.toString(), ','));
            }
        });

        // CHECK DISCOUNT TYPE FOR SHOWING INPUT DISCOUNT VALUE & DISCOUNT MAX FIELD
        function check_discount_type(){
            var discount_type = $("#discount_type").val();

            if (discount_type == null) {
                $(".vinput_discount_value").hide();
                $(".vinput_discount_max_amount").hide();   
            } else {
                if (discount_type == 'percentage') {
                    var discount_value = parseFloat($("#discount_value").val());

                    $(".vinput_discount_value").show();
                    $(".vinput_discount_max_amount").show();
                    check_percentage_discount(discount_value);
                } else {
                    $(".vinput_discount_value").show();
                    $(".vinput_discount_max_amount").hide();
                }
            }
        }

        // VALIDATION DISCOUNT TYPE PERCENTAGE >= 100%
        function check_percentage_discount(discount_value) {
            if (discount_value > 100) {
                alert('Invalid discount value. This cannot be greater than 100%');
                $("#discount_value").val('');
            }

            return true;
        }
    </script>
@endsection