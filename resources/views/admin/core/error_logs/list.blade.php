@extends('_template_adm.master')

@php
    $module = ucwords(lang('error logs', $translations));
    $pagetitle = $module;

    // get table system name
    $table_error_log = (new \App\Models\error_log())->getTable();
    $table_module = (new \App\Models\module())->getTable();
    $table_admin = (new \App\Models\admin())->getTable();
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
                                        <th>{{ ucwords(lang('error ID', $translations)) }}</th>
                                        <th>{{ ucwords(lang('error message', $translations)) }}</th>
                                        <th>{{ ucwords(lang('username', $translations)) }}</th>
                                        <th>{{ ucwords(lang('module', $translations)) }}</th>
                                        <th>{{ ucwords(lang('remarks', $translations)) }}</th>
                                        <th>{{ ucwords(lang('URL error', $translations)) }}</th>
                                        <th>{{ ucwords(lang('status', $translations)) }}</th>
                                        <th>{{ ucwords(lang('created', $translations)) }}</th>
                                        <th>{{ ucwords(lang('last updated', $translations)) }}</th>
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
@endsection

@section('script')
    <!-- DataTables -->
    @include('_vendors.datatables.script')

    <script>
        $(document).ready(function() {
            refresh_data();
        });

        function refresh_data() {
            $('#datatables').show();
            $('#datatables').dataTable().fnDestroy();
            var table = $('#datatables').DataTable({
                orderCellsTop: true,
                fixedHeader: false,
                serverSide: true,
                processing: true,
                searching: true,
                ajax: "{{ route('admin.error_logs.get_data') }}",
                order: [[ 0, 'desc' ]],
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'err_message', name: 'err_message'},
                    {data: 'username', name: '{{ $table_admin }}.username'},
                    {data: 'module_name', name: '{{ $table_module }}.name'},
                    {data: 'remarks', name: 'remarks'},
                    {data: 'url_get_error', name: 'url_get_error'},
                    {data: 'status_label', name: 'status_label'},
                    {data: 'created_at', name: 'created_at'},
                    {data: 'updated_at', name: 'updated_at'},
                    {data: 'action', name: 'action'},
                ]
            });
        }
    </script>
@endsection