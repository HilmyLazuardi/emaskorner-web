@extends('_template_adm.master')

@php
    $pagetitle = ucwords(lang('global configuration', $translations));
@endphp

@section('title', $pagetitle)

@section('content')
    <div class="">
        {{-- display response message --}}
        @include('_template_adm.message')

        <div class="page-title">
            <div class="title_left">
                <h3>{{ $pagetitle }}</h3>
            </div>
        </div>

        <div class="clearfix"></div>

        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <form class="form-horizontal form-label-left" action="{{ route('admin.global.config') }}" method="POST" enctype="multipart/form-data">
                    <div class="x_panel">
                        <div class="x_title">
                            <h2>{{ ucwords(lang('configuration', $translations)) }}</h2>
                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content">
                            @php
                                // set_input_form($type, $input_name, $label_name, $data, $errors, $required = false, $config = null)
                                // echo set_input_form('text', 'name', ucwords(lang('name', $translations)), $data, $errors, true);

                                $config = new \stdClass();
                                //$config->info_text = '<i class="fa fa-info-circle"></i> &nbsp;Enter a value content.';
                                $config->autosize = true;
                                echo set_input_form('number', 'percentage_fee', ucwords(lang('percentage fee (%)', $translations)), $data, $errors, true, $config);
                            @endphp
                            
                            <div class="ln_solid"></div>

                            <div class="form-group">
                                <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i>&nbsp; 
                                        @if (isset($data))
                                            {{ ucwords(lang('save', $translations)) }}
                                        @else
                                            {{ ucwords(lang('submit', $translations)) }}
                                        @endif
                                    </button>
                                    <a href="{{ route('admin.global.config') }}" class="btn btn-danger"><i class="fa fa-times"></i>&nbsp; {{ ucwords(lang('cancel', $translations)) }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
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
    <!-- jQuery Tags Input -->
    @include('_vendors.tagsinput.script')
    <!-- PhotoSwipe -->
    @include('_vendors.photoswipe.script')
    <!-- Textarea Autosize -->
    @include('_vendors.autosize.script')
@endsection