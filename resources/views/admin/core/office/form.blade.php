@extends('_template_adm.master')

@php
    use App\Libraries\Helper;

    $pagetitle = ucwords(lang('office', $translations));
    if(isset($data)){
        $pagetitle .= ' ('.ucwords(lang('edit', $translations)).')';
        $link = route('admin.office.update', $raw_id);
    }else {
        $pagetitle .= ' ('.ucwords(lang('new', $translations)).')';
        $link = route('admin.office.store');
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
                        <form id="form_data" class="form-horizontal form-label-left" action="{{ $link }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            @php
                                // set_input_form($type, $input_name, $label_name, $data, $errors, $required = false, $config = null)
                                $config = new \stdClass();
                                $config->attributes = 'autocomplete="off"';
                                echo set_input_form('text', 'name', ucwords(lang('name', $translations)), $data, $errors, true, $config);

                                $config = new \stdClass();
                                $config->info = '<i class="fa fa-info-circle"></i>&nbsp; '.lang('Only support #allowed_ext, MAX #size', $translations, ['#allowed_ext'=>'jpg/png', '#size'=>'2MB']);
                                $config->delete = true;
                                $config->popup = true;
                                echo set_input_form('image', 'logo', ucwords(lang('logo', $translations)), $data, $errors, false, $config);

                                $config = new \stdClass();
                                $config->autosize = true;
                                $config->placeholder = lang('describe your company here', $translations);
                                echo set_input_form('textarea', 'description', ucwords(lang('description', $translations)), $data, $errors, false, $config);
                                
                                $config = new \stdClass();
                                $config->attributes = 'autocomplete="off"';
                                $config->placeholder = '(021) 1234567';
                                echo set_input_form('text', 'phone', ucwords(lang('phone', $translations)), $data, $errors, false, $config);
                                $config->placeholder = '(021) 1234567';
                                echo set_input_form('text', 'fax', ucwords(lang('fax', $translations)), $data, $errors, false, $config);
                                $config->placeholder = 'sales@company.com';
                                echo set_input_form('email', 'email_office', ucwords(lang('email', $translations)), $data, $errors, false, $config);
                                $config->placeholder = 'contact@company.com';
                                echo set_input_form('email', 'email_contact', ucwords(lang('email - contact us', $translations)), $data, $errors, false, $config);
                                
                                $config->placeholder = '8123456789';
                                $config->input_addon = env('COUNTRY_CODE');
                                echo set_input_form('number', 'wa_phone', ucwords(lang('WhatsApp number', $translations)), $data, $errors, false, $config);

                                $config = new \stdClass();
                                $config->autosize = true;
                                echo set_input_form('textarea', 'address', ucwords(lang('address', $translations)), $data, $errors, false, $config);

                                $config->info_text = '<i class="fa fa-info-circle"></i>&nbsp; ' . lang('you may input Gmaps link or Gmaps iframe', $translations) . ' - <a href="https://support.google.com/maps/answer/144361" target="_blank"><u>Read More <i class="fa fa-external-link"></i></u></a>';
                                echo set_input_form('textarea', 'gmaps', 'Gmaps', $data, $errors, false, $config);

                                $config = new \stdClass();
                                $config->default = 'checked';
                                echo set_input_form('switch', 'status', ucfirst(lang('status', $translations)), $data, $errors, false, $config);

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
                                    <a href="{{ route('admin.office') }}" class="btn btn-default"><i class="fa fa-times"></i>&nbsp; 
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
                            <form id="form_delete" action="{{ route('admin.office.delete') }}" method="POST" onsubmit="return confirm('{!! lang('Are you sure to delete this #item?', $translations, ['#item' => 'data']) !!}');" style="display: none">
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
@endsection