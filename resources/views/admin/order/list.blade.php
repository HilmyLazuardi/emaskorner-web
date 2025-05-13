@extends('_template_adm.master')

@php
    $module             = ucwords(lang('order', $translations));
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
                        <a href="{{ route('admin.order') }}" class="btn btn-round btn-primary" style="float: right;">
                            <i class="fa fa-check-circle"></i>&nbsp; {{ ucwords(lang('active items', $translations)) }}
                        </a>
                    </div>
                </div>
            @endif
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
                                <option value="1">Menunggu Pembayaran</option>
                                <option value="2">Siap Dikirim (Terbayar)</option>
                                <option value="3">Telah Dikirim</option>
                                <option value="4">BATAL</option>
                                <option value="5">Refunded</option>
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
                                        <th>{{ ucwords(lang('no transaksi', $translations)) }}</th>
                                        <th>{{ ucwords(lang('nama pemilik', $translations)) }}</th>
                                        <th>{{ ucwords(lang('nama toko', $translations)) }}</th>
                                        <th>{{ ucwords(lang('telp toko', $translations)) }}</th>
                                        <th>{{ ucwords(lang('nama pembeli', $translations)) }}</th>
                                        <th>{{ ucwords(lang('telp pembeli', $translations)) }}</th>
                                        {{-- <th>{{ ucwords(lang('nama produk', $translations)) }}</th>
                                        <th>{{ ucwords(lang('SKU', $translations)) }}</th>
                                        <th>{{ ucwords(lang('qty', $translations)) }}</th>
                                        <th>{{ ucwords(lang('harga satuan', $translations)) }}</th> --}}
                                        <th>{{ ucwords(lang('subtotal', $translations)) }}</th>
                                        <th>{{ ucwords(lang('biaya pengiriman', $translations)) }}</th>
                                        <th>{{ ucwords(lang('asuransi pengiriman', $translations)) }}</th>
                                        <th>{{ ucwords(lang('admin fee', $translations)) }}</th>
                                        <th>{{ ucwords(lang('total', $translations)) }}</th>
                                        <th>{{ ucwords(lang('total diterima seller', $translations)) }}</th>
                                        <th>{{ ucwords(lang('status', $translations)) }}</th>
                                        <th>{{ ucwords(lang('action', $translations)) }}</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>

                            <table id="datatables-deleted" class="table table-striped table-bordered dt-responsive" style="display:none">
                                <thead>
                                    <tr>
                                        <th>{{ ucwords(lang('tanggal pemesanan', $translations)) }}</th>
                                        <th>{{ ucwords(lang('no transaksi', $translations)) }}</th>
                                        <th>{{ ucwords(lang('nama pemilik', $translations)) }}</th>
                                        <th>{{ ucwords(lang('nama toko', $translations)) }}</th>
                                        <th>{{ ucwords(lang('telp toko', $translations)) }}</th>
                                        <th>{{ ucwords(lang('nama pembeli', $translations)) }}</th>
                                        <th>{{ ucwords(lang('telp pembeli', $translations)) }}</th>
                                        {{-- <th>{{ ucwords(lang('nama produk', $translations)) }}</th>
                                        <th>{{ ucwords(lang('SKU', $translations)) }}</th>
                                        <th>{{ ucwords(lang('qty', $translations)) }}</th>
                                        <th>{{ ucwords(lang('harga satuan', $translations)) }}</th> --}}
                                        <th>{{ ucwords(lang('subtotal', $translations)) }}</th>
                                        <th>{{ ucwords(lang('biaya pengiriman', $translations)) }}</th>
                                        <th>{{ ucwords(lang('asuransi pengiriman', $translations)) }}</th>
                                        <th>{{ ucwords(lang('admin fee', $translations)) }}</th>
                                        <th>{{ ucwords(lang('total', $translations)) }}</th>
                                        <th>{{ ucwords(lang('total diterima seller', $translations)) }}</th>
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
                ajax: "{{ route('admin.order.get_data') }}?&daterange="+daterange+"&status="+status,
                order: [[ 0, 'desc' ]],
                columns: [
                    { data: 'created_at', name: 'order.created_at' },
                    { data: 'transaction_id', name: 'order.transaction_id' },
                    { data: 'seller_name', name: 'seller.fullname' },
                    { data: 'store_name', name: 'seller.store_name' },
                    { data: 'seller_phone', name: 'seller.phone_number' },
                    { data: 'buyer_name', name: 'buyer.fullname' },
                    { data: 'phone_number', name: 'buyer.phone_number' },
                    // { data: 'product_name', name: 'product_item.name' },
                    // { data: 'sku_id', name: 'product_item_variant.sku_id' },
                    // { data: 'qty', name: 'order_details.qty' },
                    // { data: 'price_per_item', name: 'order_details.price_per_item' },
                    { data: 'price_subtotal', name: 'order.price_subtotal' },
                    { data: 'price_shipping', name: 'order.price_shipping' },
                    { data: 'insurance_shipping_fee', name: 'order.insurance_shipping_fee' },
                    { data: 'amount_fee', name: 'order.amount_fee' },
                    { data: 'price_total', name: 'order.price_total' },
                    { data: 'total_net', name: 'total_net' },
                    { data: 'progress_status_label', name: 'progress_status_label' },
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
                ajax: "{{ route('admin.order.get_deleted_data') }}",
                order: [[ 0, 'asc' ]],
                columns: [
                    { data: 'created_at', name: 'order.created_at' },
                    { data: 'transaction_id', name: 'order.transaction_id' },
                    { data: 'seller_name', name: 'seller.fullname' },
                    { data: 'store_name', name: 'seller.store_name' },
                    { data: 'seller_phone', name: 'seller.phone_number' },
                    { data: 'buyer_name', name: 'buyer.fullname' },
                    { data: 'phone_number', name: 'buyer.phone_number' },
                    // { data: 'product_name', name: 'product_item.name' },
                    // { data: 'sku_id', name: 'product_item_variant.sku_id' },
                    // { data: 'qty', name: 'order_details.qty' },
                    // { data: 'price_per_item', name: 'order_details.price_per_item' },
                    { data: 'price_subtotal', name: 'order.price_subtotal' },
                    { data: 'price_shipping', name: 'order.price_shipping' },
                    { data: 'insurance_shipping_fee', name: 'order.insurance_shipping_fee' },
                    { data: 'amount_fee', name: 'order.amount_fee' },
                    { data: 'price_total', name: 'order.price_total' },
                    { data: 'total_net', name: 'total_net' },
                    { data: 'progress_status_label', name: 'progress_status_label' },
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
                window.location.href = "{{ route('admin.order.export') }}?&daterange="+daterange+"&status="+status;
            }
        }
    </script>
@endsection