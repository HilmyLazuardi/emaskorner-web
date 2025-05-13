@extends('_template_adm.master')

@php
    use App\Libraries\Helper;

    $pagetitle = ucwords(lang('dictionary', $translations)) . ' - ' . $parent_data->country_name;
    $link = route('admin.language.dictionary.save', [$parent_raw_id, $raw_id]);
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
                                if(isset($phrases[0])){
                                    foreach ($phrases as $item) {
                                        $value = null;
                                        $empty = '<span class="label label-warning"><i class="fa fa-warning"></i></span>&nbsp; ';
                                        if(isset($translation_data[$item->content])){
                                            $value = $translation_data[$item->content];
                                            $empty = '';
                                        }
                                        // set_input_form($type, $input_name, $label_name, $data, $errors, $required = false, $config = null)
                                        $config = new \stdClass();
                                        $config->id_name = 'translate_'.$item->id;
                                        $config->class_form_element = 'vinput_translate_'.$item->id;
                                        $config->value = $value;
                                        $config->placeholder = lang('input translation here', $translations);
                                        $config->autosize = true;
                                        echo set_input_form('textarea', 'translate['.$item->content.']', $empty.$item->content, $data, $errors, false, $config);
                                    }
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
                                    <a href="{{ route('admin.language', $parent_raw_id) }}" class="btn btn-default"><i class="fa fa-times"></i>&nbsp; 
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
@endsection

@section('script')
    <!-- Switchery -->
    @include('_vendors.switchery.script')
    <!-- Textarea Autosize -->
    @include('_vendors.autosize.script')
@endsection