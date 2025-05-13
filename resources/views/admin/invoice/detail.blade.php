@extends('_template_adm.master')

@php
    $module             = ucwords(lang('invoice - ', $translations)) . $data[0]->invoice_no;
    $pagetitle          = $module;
    $function_get_data  = 'refresh_data();';
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

        {{-- BACK BTN --}}
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12 title_right">
                <div class="control-group pull-right">
                    <a href="{{ route('admin.invoice') }}" class="btn btn-round btn-warning" style="float: right;">
                        <i class="fa fa-arrow-left"></i>&nbsp; {{ ucwords(lang('kembali', $translations)) }}
                    </a>
                </div>
            </div>
        </div>
        
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
                                        <th>{{ ucwords(lang('tanggal pemesanan', $translations)) }}</th>
                                        <th>{{ ucwords(lang('nama pembeli', $translations)) }}</th>
                                        <th>{{ ucwords(lang('no transaksi', $translations)) }}</th>
                                        <th>{{ ucwords(lang('subtotal', $translations)) }}</th>
                                        <th>{{ ucwords(lang('biaya pengiriman', $translations)) }}</th>
                                        <th>{{ ucwords(lang('asuransi pengiriman', $translations)) }}</th>
                                        <th>{{ ucwords(lang('total', $translations)) }}</th>
                                        <th>{{ ucwords(lang('status', $translations)) }}</th>
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
    <!-- Select2 -->
    @include('_vendors.select2.css')
    <!-- bootstrap-daterangepicker -->
    @include('_vendors.daterangepicker.css')
@endsection

@section('script')
    <!-- DataTables -->
    @include('_vendors.datatables.script')
    <!-- Select2 -->
    @include('_vendors.select2.script')
    <!-- bootstrap-daterangepicker -->
    @include('_vendors.daterangepicker.script')

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
                ajax: "{{ route('admin.invoice.get_data_detail', $raw_id) }}",
                order: [[ 0, 'desc' ]],
                columns: [
                    { data: 'created_at', name: 'order.created_at' },
                    { data: 'buyer_name', name: 'buyer.fullname' },
                    { data: 'transaction_id', name: 'order.transaction_id' },
                    { data: 'price_subtotal', name: 'order.price_subtotal' },
                    { data: 'price_shipping', name: 'order.price_shipping' },
                    { data: 'insurance_shipping_fee', name: 'order.insurance_shipping_fee' },
                    { data: 'price_total', name: 'order.price_total' },
                    { data: 'status', name: 'status' },
                    { data: 'action', name: 'action' },
                ]
            });
        }
    </script>
@endsection