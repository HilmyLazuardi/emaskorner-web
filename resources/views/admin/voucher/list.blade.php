@extends('_template_adm.master')

@php
    $module             = ucwords(lang('voucher', $translations));
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
            
            @if (isset($deleted_data))
                <div class="title_right">
                    <div class="col-md-5 col-sm-5 col-xs-12 form-group pull-right">
                        <a href="{{ route('admin.voucher') }}" class="btn btn-round btn-primary" style="float: right;">
                            <i class="fa fa-check-circle"></i>&nbsp; {{ ucwords(lang('active items', $translations)) }}
                        </a>
                    </div>
                </div>
            @else
                <div class="title_right">
                    <div class="col-md-5 col-sm-5 col-xs-12 form-group pull-right">
                        <a href="{{ route('admin.voucher.deleted_data') }}" class="btn btn-round btn-danger" style="float: right; margin-bottom: 5px;" data-toggle="tooltip" title="{{ ucwords(lang('view deleted items', $translations)) }}">
                            <i class="fa fa-trash"></i>
                        </a>
                        <a href="{{ route('admin.voucher.create') }}" class="btn btn-round btn-success" style="float: right;">
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
                            <table id="datatables" class="table table-striped table-bordered" style="display:none">
                                <thead>
                                    <tr>
                                        <th>{{ ucwords(lang('unique code', $translations)) }}</th>
                                        <th>{{ ucwords(lang('name', $translations)) }}</th>
                                        <th>{{ ucwords(lang('voucher type', $translations)) }}</th>
                                        <th>{{ ucwords(lang('discount type', $translations)) }}</th>
                                        <th>{{ ucwords(lang('discount value', $translations)) }}</th>
                                        <th>{{ ucwords(lang('discount max amount', $translations)) }}</th>
                                        <th>{{ ucwords(lang('period begin', $translations)) }}</th>
                                        <th>{{ ucwords(lang('period end', $translations)) }}</th>
                                        <th>{{ ucwords(lang('is active', $translations)) }}</th>
                                        <th>{{ ucwords(lang('created', $translations)) }}</th>
                                        <th>{{ ucwords(lang('last updated', $translations)) }}</th>
                                        <th>{{ ucwords(lang('action', $translations)) }}</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>

                            <table id="datatables-deleted" class="table table-striped table-bordered" style="display:none">
                                <thead>
                                    <tr>
                                        <th>{{ ucwords(lang('unique code', $translations)) }}</th>
                                        <th>{{ ucwords(lang('name', $translations)) }}</th>
                                        <th>{{ ucwords(lang('voucher type', $translations)) }}</th>
                                        <th>{{ ucwords(lang('discount type', $translations)) }}</th>
                                        <th>{{ ucwords(lang('discount value', $translations)) }}</th>
                                        <th>{{ ucwords(lang('discount max amount', $translations)) }}</th>
                                        <th>{{ ucwords(lang('period begin', $translations)) }}</th>
                                        <th>{{ ucwords(lang('period end', $translations)) }}</th>
                                        <th>{{ ucwords(lang('deleted at', $translations)) }}</th>
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
            var status = $('#status').val();
            if (typeof status == 'undefined') {
                status = '';
            }

            $('#datatables').show();
            $('#datatables').dataTable().fnDestroy();
            var table = $('#datatables').DataTable({
                orderCellsTop: true,
                fixedHeader: false,
                serverSide: true,
                processing: true,
                ajax: "{{ route('admin.voucher.get_data') }}?status="+status,
                order: [[ 9, 'desc' ]],
                columns: [
                    { data: 'unique_code', name: 'unique_code' },
                    { data: 'name', name: 'name' },
                    { data: 'voucher_type', name: 'voucher_type' },
                    { data: 'discount_type', name: 'discount_type' },
                    { data: 'discount_value', name: 'discount value' },
                    { data: 'discount_max_amount', name: 'discount max amount' },
                    { data: 'period_begin', name: 'period_begin' },
                    { data: 'period_end', name: 'period_end' },
                    { data: 'is_active', name: 'is_active_label' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'updated_at', name: 'updated_at' },
                    { data: 'action', name: 'action' }
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
                ajax: "{{ route('admin.voucher.get_deleted_data') }}",
                order: [[ 8, 'desc' ]],
                columns: [
                    { data: 'unique_code', name: 'unique_code' },
                    { data: 'name', name: 'name' },
                    { data: 'voucher_type', name: 'voucher_type' },
                    { data: 'discount_type', name: 'discount_type' },
                    { data: 'discount_value', name: 'discount value' },
                    { data: 'discount_max_amount', name: 'discount max amount' },
                    { data: 'period_begin', name: 'period_begin' },
                    { data: 'period_end', name: 'period_end' },
                    { data: 'deleted_at', name: 'deleted_at' },
                    { data: 'action', name: 'action' }
                ]
            });
        }
    </script>
@endsection