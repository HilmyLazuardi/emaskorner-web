@extends('_template_adm.master')

@php
    $module = ucwords(lang('system logs', $translations));
    $pagetitle = $module;
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
                            <table id="datatables" class="table table-striped table-bordered" style="display:none">
                                <thead>
                                    <tr>
                                        <th>{{ ucwords(lang('log ID', $translations)) }}</th>
                                        <th>{{ ucwords(lang('user', $translations)) }}</th>
                                        <th>{{ ucwords(lang('activity', $translations)) }}</th>
                                        <th>{{ lang('IP Address', $translations) }}</th>
                                        <th>{{ strtoupper(lang('url', $translations)) }}</th>
                                        <th>{{ ucwords(lang('timestamp', $translations)) }}</th>
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
                searching: false,
                ajax: "{{ route('admin.system_logs.get_data') }}",
                order: [[ 0, 'desc' ]],
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'username', name: 'username'},
                    {data: 'activity', name: 'activity'},
                    {data: 'ip_address', name: 'ip_address'},
                    {data: 'url', name: 'url'},
                    {data: 'timestamp', name: 'timestamp'},
                    {data: 'action', name: 'action'},
                ]
            });
        }
    </script>
@endsection