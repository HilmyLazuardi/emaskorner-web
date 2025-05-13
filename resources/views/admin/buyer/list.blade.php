@extends('_template_adm.master')

@php
    $module             = ucwords(lang('buyer', $translations));
    $pagetitle          = $module;
    $function_get_data  = 'refresh_data();';

    if (isset($deleted_data)) {
        $pagetitle          = ucwords(lang('deleted #item', $translations, ['#item' => $module]));
        $function_get_data  = 'refresh_deleted_data();';
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
            
            <div class="title_right">
                <div class="col-md-5 col-sm-5 col-xs-12 form-group pull-right">
                    <a href="javascript:void(0)" class="btn btn-round btn-warning" style="float: right; margin-bottom: 5px;" onclick="confirm_export()">
                        <i class="fa fa-download"></i>&nbsp; {{ ucwords(lang('export', $translations)) }}
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
                                        <th>ID</th>
                                        <th>{{ ucwords(lang('Fullname', $translations)) }}</th>
                                        <th>{{ ucwords(lang('Phone Number', $translations)) }}</th>
                                        <th>{{ ucwords(lang('Email', $translations)) }}</th>
                                        <th>{{ ucwords(lang('Birth Date', $translations)) }}</th>
                                        <th>{{ ucwords(lang('status', $translations)) }}</th>
                                        <th>{{ ucwords(lang('created', $translations)) }}</th>
                                        <th>{{ ucwords(lang('last updated', $translations)) }}</th>
                                        <th>{{ ucwords(lang('action', $translations)) }}</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>

                            <table id="datatables-deleted" class="table table-striped table-bordered dt-responsive" style="display:none">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>{{ ucwords(lang('Fullname', $translations)) }}</th>
                                        <th>{{ ucwords(lang('Phone Number', $translations)) }}</th>
                                        <th>{{ ucwords(lang('Email', $translations)) }}</th>
                                        <th>{{ ucwords(lang('Birth Date', $translations)) }}</th>
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
@endsection

@section('script')
    <!-- DataTables -->
    @include('_vendors.datatables.script')

    <script>
        $(document).ready(function() {
            {{ $function_get_data }}
        });

        function refresh_data() {
            $('#datatables').show();
            $('#datatables').dataTable().fnDestroy();
            var table = $('#datatables').DataTable({
                orderCellsTop: true,
                fixedHeader: false,
                serverSide: true,
                processing: true,
                ajax: "{{ route('admin.buyer.get_data') }}",
                order: [[ 0, 'asc' ]],
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'fullname', name: 'fullname' },
                    { data: 'phone_number', name: 'phone_number' },
                    { data: 'email', name: 'email' },
                    { data: 'birth_date', name: 'birth_date' },
                    { data: 'status', name: 'status' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'updated_at', name: 'updated_at' },
                    { data: 'action', name: 'action' },
                ]
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
                ajax: "{{ route('admin.buyer.get_deleted_data') }}",
                order: [[ 0, 'asc' ]],
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'fullname', name: 'fullname' },
                    { data: 'phone_number', name: 'phone_number' },
                    { data: 'email', name: 'email' },
                    { data: 'birth_date', name: 'birth_date' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'deleted_at', name: 'deleted_at' },
                    { data: 'action', name: 'action' },
                ]
            });
        }

        function confirm_export() {
            if (confirm("{{ lang('Are you sure to export this #item?', $translations, ['#item' => $pagetitle]) }}")) {
                window.location.href = "{{ route('admin.buyer.export') }}";
            }
        }
    </script>
@endsection