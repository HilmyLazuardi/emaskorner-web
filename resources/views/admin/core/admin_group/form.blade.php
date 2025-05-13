@extends('_template_adm.master')

@php
    use App\Libraries\Helper;

    $pagetitle = ucwords(lang('admin group', $translations));
    if(isset($data)){
        $pagetitle .= ' ('.ucwords(lang('edit', $translations)).')';
        $link = route('admin.group.update', $raw_id);
    }else {
        $pagetitle .= ' ('.ucwords(lang('new', $translations)).')';
        $link = route('admin.group.store');
        $data = null;

        // if add new, declare empty variables
        $access = []; 
        $office_allowed = []; 
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
                        <form id="form_data" class="form-horizontal form-label-left" action="{{ $link }}" method="POST">
                            @csrf

                            @php
                                // set_input_form($type, $input_name, $label_name, $data, $errors, $required = false, $config = null)
                                echo set_input_form('text', 'name', ucwords(lang('name', $translations)), $data, $errors, true);

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

                            {{-- Offices --}}
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="office">{{ ucwords(lang('office', $translations)) }} <span class="required" style="color:red">*</span></label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    @if (isset($offices) && !empty($offices))
                                        @php 
                                            $no = 1;
                                            $add_script_office = []; // sbg wadah simpan script utk centang check all per module
                                        @endphp
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="access_all_offices" value="ALL" id="master_check_all_office">
                                                <b>*{{ strtoupper(lang('check all', $translations)) }}*</b>
                                            </label>
                                        </div>
                                        @foreach ($offices as $key => $value)
                                            @if ($no == 1 || $no % 3 == 1)
                                                <div class="row">
                                            @endif
                                            <div class="col-md-4 col-sm-4 col-xs-12" style="margin-top:10px">
                                                <span class="label label-success">{{ $key }}</span>
                                                @if (count($value) > 0)
                                                    @php
                                                        $module_name = str_replace('-', '_', Helper::generate_slug($key));
                                                        $access_ids = []; // save access per module
                                                        $access_checked = []; // save checked access per module
                                                    @endphp
                                                    <div class="checkbox">
                                                        <label>
                                                            <input type="checkbox" value="" class="access_office" id="check_all_office_{{ $module_name }}" onclick="check_all_office('office_{{ $module_name }}')">
                                                            <i>*{{ ucwords(lang('check all', $translations)) }}</i>
                                                        </label>
                                                    </div>
                                                    @foreach ($value as $item)
                                                        @php 
                                                            $access_ids[] = $item->id;
                                                            $stat = '';
                                                            if(count($office_allowed) > 0){
                                                                if(in_array($item->id, $office_allowed)){
                                                                    $stat = 'checked';
                                                                    $access_checked[] = $item->id;
                                                                }
                                                            }
                                                        @endphp
                                                        <div class="checkbox">
                                                            <label>
                                                                <input type="checkbox" name="branch[]" value="{{ $item->id }}" class="access_office office_{{ $module_name }}" onclick="is_all_branch_office_checked('office_{{ $module_name }}')" {{ $stat }}>
                                                                {{ $item->name }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                    @php
                                                        if (count($access_checked) == count($access_ids)){
                                                            $add_script_office[] = '<script>$("#check_all_office_'.$module_name.'").attr("checked", true);</script>';
                                                        }
                                                    @endphp
                                                @endif
                                            </div>
                                            @php
                                                if($no % 3 == 0 || $no == count($offices)){
                                                    echo '</div><br>';
                                                }
                                                $no++;
                                            @endphp
                                        @endforeach
                                    @else
                                        {{ lang('NO OFFICE and/or BRANCH OFFICES ARE AVAILABLE, please create a new office', $translations) }} <a href="{{ route('admin.office.create') }}"><u>{{ lang('here', $translations) }}</u></a>
                                    @endif
                                </div>
                            </div>

                            {{-- Modules --}}
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="access">{{ ucwords(lang('access', $translations)) }} <span class="required" style="color:red">*</span></label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    @if (isset($modules) && !empty($modules))
                                        @php 
                                            $no = 1;
                                            $add_script = []; // sbg wadah simpan script utk centang check all per module
                                        @endphp
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="access_all" value="ALL" id="master_check_all">
                                                <b>*{{ strtoupper(lang('check all', $translations)) }}*</b>
                                            </label>
                                        </div>
                                        @foreach ($modules as $key => $value)
                                            @if ($no == 1 || $no % 3 == 1)
                                                <div class="row">
                                            @endif
                                            <div class="col-md-4 col-sm-4 col-xs-12" style="margin-top:10px">
                                                <span class="label label-primary">{{ $key }}</span>
                                                @if (count($value) > 0)
                                                    @php
                                                        $module_name = strtolower(str_replace(' ', '_', $key));
                                                        $access_ids = []; // save access per module
                                                        $access_checked = []; // save checked access per module
                                                    @endphp
                                                    <div class="checkbox">
                                                        <label>
                                                            <input type="checkbox" value="" class="access_module" id="check_all_module_{{ $module_name }}" onclick="check_all_modules('module_{{ $module_name }}')">
                                                            <i>*{{ ucwords(lang('check all', $translations)) }}</i>
                                                        </label>
                                                    </div>
                                                    @foreach ($value as $item)
                                                        @php 
                                                            $access_ids[] = $item->id;
                                                            $stat = '';
                                                            if(count($access) > 0){
                                                                if(in_array($item->id, $access)){
                                                                    $stat = 'checked';
                                                                    $access_checked[] = $item->id;
                                                                }
                                                            }
                                                        @endphp
                                                        <div class="checkbox">
                                                            <label>
                                                                <input type="checkbox" name="access[]" value="{{ $item->id }}" class="access_module module_{{ $module_name }}" onclick="is_all_rules_checked('module_{{ $module_name }}')" {{ $stat }}>
                                                                {{ $item->name }}
                                                                @if ($item->description)
                                                                    &nbsp;<i class="fa fa-info-circle" title="{{ $item->description }}" data-toggle="tooltip" data-original-title="{{ $item->description }}"></i>
                                                                @endif
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                    @php
                                                    if (count($access_checked) == count($access_ids)){
                                                        $add_script[] = '<script>$("#check_all_module_'.$module_name.'").attr("checked", true);</script>';
                                                    }
                                                    @endphp
                                                @endif
                                            </div>
                                            @php
                                                if($no % 3 == 0 || $no == count($modules)){
                                                    echo '</div><br>';
                                                }
                                                $no++;
                                            @endphp
                                        @endforeach
                                    @else
                                        {{ lang('NO RULES AVAILABLE, please make a new rule', $translations) }} <a href="{{ route('admin.module_rule.create') }}"><u>{{ lang('here', $translations) }}</u></a>
                                    @endif
                                </div>
                            </div>
                            
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
                                    <a href="{{ route('admin.group') }}" class="btn btn-default"><i class="fa fa-times"></i>&nbsp; 
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
                            <form id="form_delete" action="{{ route('admin.group.delete') }}" method="POST" onsubmit="return confirm('{!! lang('Are you sure to delete this #item?', $translations, ['#item' => 'data']) !!}');" style="display: none">
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
@endsection

@section('script')
    <!-- Switchery -->
    @include('_vendors.switchery.script')

    {{-- OFFICE - BEGIN --}}
    @php
        // "check_all" branch offices per office
        if (isset($add_script_office) && count($add_script_office) > 0){
            echo implode(' ', $add_script_office);
        }

        // "check all" offices
        if(isset($add_script_office) && count($offices) == count($add_script_office)){
            echo '<script>$("#master_check_all_office").attr("checked", true);</script>';
        }
    @endphp
    <script>
        $('#master_check_all_office').on("click", function() {
            is_all_office_checked(true);
        });

        function check_all_office(module_name) {
            var all = $('.'+module_name).length;
            var total = $('.'+module_name+':checked').length;

            if(total == all){
                $("."+module_name).removeAttr("checked");
                $("#master_check_all_office").removeAttr("checked");
            }else{
                $("."+module_name).prop("checked", "checked");
            }
        }

        function is_all_branch_office_checked(module_name) {
            var all = $('.'+module_name).length;
            var total = $('.'+module_name+':checked').length;

            if(total == all){
                $("#check_all_"+module_name).prop("checked", "checked");
                is_all_office_checked();
            }else{
                $("#check_all_"+module_name).removeAttr("checked");
                $("#master_check_all_office").removeAttr("checked");
            }
        }

        function is_all_office_checked(active = false) {
            var all = $('.access_office').length;
            var total = $('.access_office:checked').length;

            if (active) {
                if(total == all && $('#master_check_all_office:checked').length == 0){
                    $(".access_office").removeAttr("checked");
                }else{
                    $(".access_office").prop("checked", "checked");
                }
            } else {
                if(total == all && $('#master_check_all_office:checked').length == 0){
                    $("#master_check_all_office").prop("checked", "checked");
                }
            }
        }
    </script>
    {{-- OFFICE - END --}}

    {{-- MODULES - BEGIN --}}
    @php
        // "check_all" rules per module
        if (isset($add_script) && count($add_script) > 0){
            echo implode(' ', $add_script);
        }
        // "check all" modules
        if(isset($add_script) && count($modules) == count($add_script)){
            echo '<script>$("#master_check_all").attr("checked", true);</script>';
        }
    @endphp
    <script>
        $('#master_check_all').on("click", function() {
            is_all_modules_checked(true);
        });

        function check_all_modules(module_name) {
            var all = $('.'+module_name).length;
            var total = $('.'+module_name+':checked').length;

            if(total == all){
                $("."+module_name).removeAttr("checked");
                $("#master_check_all").removeAttr("checked");
            }else{
                $("."+module_name).prop("checked", "checked");
            }
        }

        function is_all_rules_checked(module_name) {
            var all = $('.'+module_name).length;
            var total = $('.'+module_name+':checked').length;

            if(total == all){
                $("#check_all_"+module_name).prop("checked", "checked");
                is_all_modules_checked();
            }else{
                $("#check_all_"+module_name).removeAttr("checked");
                $("#master_check_all").removeAttr("checked");
            }
        }

        function is_all_modules_checked(active = false) {
            var all = $('.access_module').length;
            var total = $('.access_module:checked').length;

            if (active) {
                if(total == all && $('#master_check_all:checked').length == 0){
                    $(".access_module").removeAttr("checked");
                }else{
                    $(".access_module").prop("checked", "checked");
                }
            } else {
                if(total == all && $('#master_check_all:checked').length == 0){
                    $("#master_check_all").prop("checked", "checked");
                }
            }
        }
    </script>
    {{-- MODULES - END --}}
@endsection