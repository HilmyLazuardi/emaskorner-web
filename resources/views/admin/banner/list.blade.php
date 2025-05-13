@extends('_template_adm.master')

@php
    $module = ucwords($position) . ' ' . ucwords(lang('banner', $translations));
    $pagetitle = $module;
    $function_get_data = 'refresh_data();';

    if (isset($deleted_data)) {
        $pagetitle = ucwords(lang('deleted #item', $translations, ['#item' => $module]));
        $function_get_data = 'refresh_deleted_data();';
    }
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
            
            @if (isset($deleted_data))
                <div class="title_right">
                    <div class="col-md-5 col-sm-5 col-xs-12 form-group pull-right">
                        <a href="{{ route('admin.banner', $position) }}" class="btn btn-round btn-primary" style="float: right;">
                            <i class="fa fa-check-circle"></i>&nbsp; {{ ucwords(lang('active items', $translations)) }}
                        </a>
                    </div>
                </div>
            @else
                <div class="title_right">
                    <div class="col-md-5 col-sm-5 col-xs-12 form-group pull-right">
                        <a href="{{ route('admin.banner.deleted_data', $position) }}" class="btn btn-round btn-danger" style="float: right; margin-bottom: 5px;" data-toggle="tooltip" title="{{ ucwords(lang('view deleted items', $translations)) }}">
                            <i class="fa fa-trash"></i>
                        </a>
                        <a href="{{ route('admin.banner.create', $position) }}" class="btn btn-round btn-success" style="float: right;">
                            <i class="fa fa-plus-circle"></i>&nbsp; {{ ucwords(lang('add new', $translations)) }}
                        </a>
                    </div>
                </div>
            @endif
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
                                        <th>{{ ucwords(lang('image', $translations)) }}</th>
                                        <th>{{ ucwords(lang('image mobile', $translations)) }}</th>
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

                            <table id="datatables-deleted" class="table table-striped table-bordered dt-responsive" style="display:none">
                                <thead>
                                    <tr>
                                        <th>{{ ucwords(lang('name', $translations)) }}</th>
                                        <th>{{ ucwords(lang('image', $translations)) }}</th>
                                        <th>{{ ucwords(lang('image mobile', $translations)) }}</th>
                                        <th>{{ ucwords(lang('link type', $translations)) }}</th>
                                        <th>URL</th>
                                        <th>{{ ucwords(lang('link target', $translations)) }}</th>
                                        <th>{{ ucwords(lang('created', $translations)) }}</th>
                                        <th>{{ ucwords(lang('deleted', $translations)) }}</th>
                                        <th>{{ ucwords(lang('action', $translations)) }}</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
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
        var AjaxSortingURL = '{{ route("admin.banner.sorting", $position) }}';

        $(document).ready(function() {
            {{ $function_get_data }}
        });

        function refresh_data() {
            $('#datatables').show();
            $.ajax({
                type: 'GET',
                url: "{{ route('admin.banner.get_data', $position) }}",
                success: function(response){
                    if (typeof response.status != 'undefined') {
                        if (response.status == 'true') {
                            var html = '';
                            if (response.data == '') {
                                html += '<tr><td colspan="9"><h2 class="text-center">{{ strtoupper(lang("no data available", $translations)) }}</h2></td></tr>';
                            } else {
                                $.each(response.data, function (index, value) {
                                    html += '<tr role="row" id="row-'+value.id+'" title="{{ lang("Drag & drop to sorting", $translations) }}" data-toggle="tooltip">';
                                        html += '<td class="dragndrop">'+value.name+'</td>';
                                        html += '<td><img src="'+value.image_show+'"></td>';
                                        html += '<td><img src="'+value.image_mobile_show+'"></td>';
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

        function refresh_deleted_data() {
            $('#datatables-deleted').show();
            $('#datatables-deleted').dataTable().fnDestroy();
            var table = $('#datatables-deleted').DataTable({
                orderCellsTop: true,
                fixedHeader: false,
                serverSide: true,
                processing: true,
                ajax: "{{ route('admin.banner.get_deleted_data', $position) }}",
                order: [[ 0, 'asc' ]],
                columns: [
                    {data: 'name', name: 'name'},
                    {data: 'image_show', name: 'image_show'},
                    {data: 'image_mobile_show', name: 'image_mobile_show'},
                    {data: 'link_type', name: 'link_type'},
                    {data: 'link_url', name: 'link_url'},
                    {data: 'link_target', name: 'link_target'},
                    {data: 'created_at', name: 'created_at'},
                    {data: 'deleted_at', name: 'deleted_at'},
                    {data: 'action', name: 'action'},
                ]
            });
        }
    </script>
@endsection