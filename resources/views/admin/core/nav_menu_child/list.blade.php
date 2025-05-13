@extends('_template_adm.master')

@php
    use App\Libraries\Helper;

    $module = ucwords($position) . ' ' . ucwords(lang('navigation sub menu', $translations));
    $pagetitle = $module . ' (Lvl '.($parent_data->level + 1).') - ' . $parent_data->name;
    $function_get_data = 'refresh_data();';
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
            <div class="title_right">
                <div class="col-md-5 col-sm-5 col-xs-12 form-group pull-right">
                    <a href="{{ route('admin.nav_menu_child.create', [$position, $raw_parent]) }}" class="btn btn-round btn-success" style="float: right; margin-bottom: 5px;">
                        <i class="fa fa-plus-circle"></i>&nbsp; {{ ucwords(lang('add new', $translations)) }}
                    </a>
                    @php
                        $back_url = route('admin.nav_menu', $position);
                        if ($parent_data->level > 1) {
                            $grandparent_id = $parent_data->parent_id;
                            if (env('CRYPTOGRAPHY_MODE', false)) {
                                $grandparent_id = Helper::generate_token($grandparent_id);
                            }
                            $back_url = route('admin.nav_menu_child', [$position, $grandparent_id]);
                        }
                    @endphp
                    <a href="{{ $back_url }}" class="btn btn-round btn-warning" style="float: right;">
                        <i class="fa fa-chevron-circle-left"></i>&nbsp; {{ ucwords(lang('back', $translations)) }}
                    </a>
                </div>
            </div>
        </div>
        
        <div class="clearfix"></div>
        
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>{{ ucwords(lang('data list', $translations)) }}</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <div class="table-responsive">
                            <table id="datatables" class="table table-striped table-bordered dt-responsive" style="display:none">
                                <thead>
                                    <tr>
                                        <th>{{ ucwords(lang('name', $translations)) }}</th>
                                        <th>{{ ucwords(lang('link type', $translations)) }}</th>
                                        <th>URL</th>
                                        <th>{{ ucwords(lang('link target', $translations)) }}</th>
                                        <th>Status</th>
                                        <th>{{ ucwords(lang('created', $translations)) }}</th>
                                        <th>{{ ucwords(lang('last updated', $translations)) }}</th>
                                        <th>{{ ucwords(lang('action', $translations)) }}</th>
                                    </tr>
                                </thead>
                                <tbody class="sorted_table" id="sortable-data"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
    <!-- DataTables -->
    @include('_vendors.datatables.css')
    <!-- Sortable-Table -->
    @include('_vendors.sortable_table.css')
@endsection

@section('script')
    <!-- DataTables -->
    @include('_vendors.datatables.script')
    <!-- Sortable-Table -->
    @include('_vendors.sortable_table.script')

    <script>
        var AjaxSortingURL = '{{ route("admin.nav_menu_child.sorting", [$position, $raw_parent]) }}';

        $(document).ready(function() {
            {{ $function_get_data }}
        });

        function refresh_data() {
            $('#datatables').show();
            $.ajax({
                type: 'GET',
                url: "{{ route('admin.nav_menu_child.get_data', [$position, $raw_parent]) }}",
                success: function(response){
                    if (typeof response.status != 'undefined') {
                        if (response.status == 'true') {
                            var html = '';
                            if (response.data == '') {
                                html += '<tr><td colspan="8"><h2 class="text-center">{{ strtoupper(lang("no data available", $translations)) }}</h2></td></tr>';
                            } else {
                                $.each(response.data, function (index, value) {
                                    html += '<tr role="row" id="row-'+value.id+'" title="{{ lang("Drag & drop to sorting", $translations) }}" data-toggle="tooltip">';
                                        html += '<td class="dragndrop">'+value.name+'</td>';
                                        html += '<td>'+value.link_type+'</td>';
                                        switch (value.link_type) {
                                            case 'internal':
                                                html += '<td>'+value.link_internal+'</td>';
                                                html += '<td>'+value.link_target+'</td>';
                                                break;

                                            case 'external':
                                                html += '<td>'+value.link_external+'</td>';
                                                html += '<td>'+value.link_target+'</td>';
                                                break;
                                        
                                            default:
                                                html += '<td>-</td>';
                                                html += '<td>-</td>';
                                                break;
                                        }
                                        html += '<td>'+value.status_label+'</td>';
                                        html += '<td>'+value.created_at_edited+'</td>';
                                        html += '<td>'+value.updated_at_edited+'</td>';
                                        html += '<td>'+value.action+'</td>';
                                    html += '</tr>';
                                });
                            }
                            $('#sortable-data').html(html);
                        } else {
                            alert(response.message);
                        }
                    } else {
                        alert ("{!! lang('Server not respond, please refresh your page.', $translations); !!}");
                    }
                },
                error: function (data, textStatus, errorThrown) {
                    console.log(data);
                    console.log(textStatus);
                    console.log(errorThrown);
                    alert ("{!! lang('Oops, something went wrong please try again later.', $translations); !!}\n\n"+textStatus+': '+errorThrown);
                }
            });
        }
    </script>
@endsection