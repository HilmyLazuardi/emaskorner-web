@extends('_template_adm.master')

@php
    use App\Libraries\Helper;

    $pagetitle = ucwords($position) . ' ' . ucwords(lang('banner', $translations));
    if(isset($data)){
        $pagetitle .= ' ('.ucwords(lang('edit', $translations)).')';
        $link = route('admin.banner.update', [$position, $raw_id]);
    }else {
        $pagetitle .= ' ('.ucwords(lang('new', $translations)).')';
        $link = route('admin.banner.store', $position);
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
                                $config->popup = true;
                                $config->info = '<i class="fa fa-info-circle"></i>&nbsp; '.lang('Only support #allowed_ext, MAX #size', $translations, ['#allowed_ext'=>'jpg/jpeg/png', '#size'=>'2MB']);
                                if (isset($data)) {
                                    echo set_input_form('image', 'image', ucwords(lang('image', $translations)), $data, $errors, false, $config);
                                } else {
                                    echo set_input_form('image', 'image', ucwords(lang('image', $translations)), $data, $errors, true, $config);
                                }

                                $config = new \stdClass();
                                $config->popup = true;
                                $config->info = '<i class="fa fa-info-circle"></i>&nbsp; '.lang('Only support #allowed_ext, MAX #size', $translations, ['#allowed_ext'=>'jpg/jpeg/png', '#size'=>'2MB']);
                                if (isset($data)) {
                                    echo set_input_form('image', 'image_mobile', ucwords(lang('image mobile', $translations)), $data, $errors, false, $config);
                                } else {
                                    echo set_input_form('image', 'image_mobile', ucwords(lang('image mobile', $translations)), $data, $errors, true, $config);
                                }

                                $config = new \stdClass();
                                $config->defined_data = ['none', 'internal', 'external'];
                                $config->placeholder = '- '.ucwords(lang('please choose one', $translations)).' -';
                                $config->attributes = 'onchange="setup_link()"';
                                echo set_input_form('select', 'link_type', ucwords(lang('link type', $translations)), $data, $errors, true, $config);

                                $config = new \stdClass();
                                $config->input_addon = url('/');
                                $config->attributes = 'autocomplete="off"';
                                $config->placeholder = 'input (/) for home or another page (/about)';
                                echo set_input_form('text', 'link_internal', 'Internal Link', $data, $errors, true, $config);
                                
                                $config = new \stdClass();
                                $config->placeholder = 'https://www.domain.com/';
                                $config->attributes = 'autocomplete="off"';
                                echo set_input_form('text', 'link_external', 'External Link', $data, $errors, true, $config);

                                $config = new \stdClass();
                                $config->defined_data = ['same window', 'new window'];
                                $config->placeholder = '- '.ucwords(lang('please choose one', $translations)).' -';
                                echo set_input_form('select', 'link_target', ucwords(lang('link target', $translations)), $data, $errors, true, $config);

                                $config = new \stdClass();
                                $config->default = 'checked';
                                echo set_input_form('switch', 'status', ucwords(lang('status', $translations)), $data, $errors, false, $config);

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
                                    <a href="{{ route('admin.banner', $position) }}" class="btn btn-default"><i class="fa fa-times"></i>&nbsp; 
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
                            <form id="form_delete" action="{{ route('admin.banner.delete', $position) }}" method="POST" onsubmit="return confirm('{!! lang('Are you sure to delete this #item?', $translations, ['#item' => 'data']) !!}');" style="display: none">
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
        setup_link();

        function setup_link() {
            var link_type = $('#link_type').val();

            $('#link_internal').prop('required', false);
            $('.vinput_link_internal').hide();
            $('#link_external').prop('required', false);
            $('.vinput_link_external').hide();
            $('#link_target').prop('required', false);
            $('.vinput_link_target').hide();

            switch (link_type) {
                case 'internal':
                    $('#link_internal').prop('required', true);
                    $('.vinput_link_internal').show();
                    $('#link_target').prop('required', true);
                    $('.vinput_link_target').show();
                    break;

                case 'external':
                    $('#link_external').prop('required', true);
                    $('.vinput_link_external').show();
                    $('#link_target').prop('required', true);
                    $('.vinput_link_target').show();
                    break;
            
                default:
                    break;
            }
        }
    </script>
@endsection