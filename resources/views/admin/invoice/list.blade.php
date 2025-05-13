@extends('_template_adm.master')

@php
    $module             = ucwords(lang('invoice', $translations));
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

        {{-- FILTER --}}
        <div class="row">
            {{-- filter by: progress_status --}}
            <div class="col-md-4 col-sm-12 col-xs-12">
                <div class="control-group">
                    <div class="controls">
                        <div class="input-prepend input-group">
                            <span class="add-on input-group-addon"><i class="fa fa-check-circle"></i></span>
                            <select style="width: 200px" name="status" id="status" class="form-control select2">
                                <option value="" selected>- SEMUA STATUS -</option>
                                <option value="paid">Paid</option>
                                <option value="unpaid">Unpaid</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- filter by: daterange --}}
            <div class="col-md-4 col-sm-12 col-xs-12">
                <div class="control-group">
                    <div class="controls">
                        <div class="input-prepend input-group">
                            <span class="add-on input-group-addon"><i class="fa fa-calendar"></i></span>
                            <input type="text" style="width: 200px" name="reservation" id="reportrange_right" class="form-control" value="" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- EXPORT --}}
            <div class="col-md-4 col-sm-12 col-xs-12 title_right">
                <div class="control-group pull-right">
                    <div class="controls">
                        <a href="javascript:void(0)" class="btn btn-round btn-primary" onclick="confirm_export()" style="float: right;">
                            <i class="fa fa-download"></i>&nbsp; {{ ucwords(lang('export', $translations)) }}
                        </a>
                    </div>
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
                                        <th>{{ ucwords(lang('no invoice', $translations)) }}</th>
                                        <th>{{ ucwords(lang('subtotal', $translations)) }}</th>
                                        <th>{{ ucwords(lang('biaya pengiriman', $translations)) }}</th>
                                        <th>{{ ucwords(lang('asuransi pengiriman', $translations)) }}</th>
                                        <th>{{ ucwords(lang('discount', $translations)) }}</th>
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

            $('#status').on('change', function() {
                {{ $function_get_data }}
                $(this).blur();
            });

            $('#reportrange_right').on('change', function() {
                {{ $function_get_data }}
                $(this).blur();
            });
        });

        function refresh_data() {
            var daterange = $('#reportrange_right').val();
            if (typeof daterange == 'undefined') {
                daterange = '';
            }

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
                ajax: "{{ route('admin.invoice.get_data') }}?&daterange="+daterange+"&status="+status,
                order: [[ 0, 'desc' ]],
                columns: [
                    { data: 'created_at', name: 'invoices.created_at' },
                    { data: 'buyer_name', name: 'buyer.fullname' },
                    { data: 'invoice_no', name: 'invoices.invoice_no' },
                    { data: 'subtotal', name: 'invoices.subtotal' },
                    { data: 'shipping_fee', name: 'invoices.shipping_fee' },
                    { data: 'shipping_insurance_fee', name: 'invoices.shipping_insurance_fee' },
                    { data: 'discount_amount', name: 'invoices.discount_amount' },
                    { data: 'total_amount', name: 'invoices.total_amount' },
                    { data: 'status', name: 'status' },
                    { data: 'action', name: 'action' },
                ]
            });
        }

        function confirm_export() {
            var daterange = $('#reportrange_right').val();
            if (typeof daterange == 'undefined') {
                daterange = '';
            }

            var status = $('#status').val();
            if (typeof status == 'undefined') {
                status = '';
            }
            if (confirm("Apakah Anda yakin untuk mengekspor data ini?")) {
                window.location.href = "{{ route('admin.invoice.export') }}?&daterange="+daterange+"&status="+status;
            }
        }
    </script>
@endsection