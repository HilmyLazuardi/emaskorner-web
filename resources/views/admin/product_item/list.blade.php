@extends('_template_adm.master')

@php
    $module             = ucwords(lang('product item', $translations));
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
                        <a href="{{ route('admin.product_item') }}" class="btn btn-round btn-primary" style="float: right;">
                            <i class="fa fa-check-circle"></i>&nbsp; {{ ucwords(lang('active items', $translations)) }}
                        </a>
                    </div>
                </div>
            @else
                <div class="title_right">
                    <div class="col-md-2 col-sm-5 col-xs-12 form-group pull-right">
                        <button class="btn btn-round btn-primary" style="float: right; margin-bottom: 5px;" data-toggle="tooltip" onclick="confirm_export()">
                            <i class="fa fa-download"></i>&nbsp; {{ ucwords(lang('export', $translations)) }}
                        </button>
                    </div>
                    <div class="col-md-5 col-sm-5 col-xs-12 form-group pull-right">
                        <a href="{{ route('admin.product_item.deleted_data') }}" class="btn btn-round btn-danger" style="float: right; margin-bottom: 5px;" data-toggle="tooltip" title="{{ ucwords(lang('view deleted items', $translations)) }}">
                            <i class="fa fa-trash"></i>
                        </a>
                        <a href="{{ route('admin.product_item.create') }}" class="btn btn-round btn-success" style="float: right;">
                            <i class="fa fa-plus-circle"></i>&nbsp; {{ ucwords(lang('add new', $translations)) }}
                        </a>
                    </div>
                </div>
            @endif
        </div>
        
        <div class="clearfix"></div>

        <div class="row">
            {{-- filter by: progress_status --}}
            <div class="col-md-4 col-sm-12 col-xs-12">
                <div class="control-group">
                    <div class="controls">
                        <div class="input-prepend input-group">
                            <span class="add-on input-group-addon"><i class="fa fa-check-circle"></i></span>
                            <select style="width: 200px" name="status" id="status" class="form-control select2">
                                <option value="approved" {{ (request()->status == "approved") ? 'selected' : '' }}>Disetujui</option>
                                <option value="draft" {{ (request()->status == "draft") ? 'selected' : '' }}>Draf</option>
                                <option value="on-review" {{ (request()->status == "on-review") ? 'selected' : '' }}>Req. Review</option>
                            </select>
                        </div>
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
                                        <th>ID</th>
                                        <th>{{ ucwords(lang('seller', $translations)) }}</th>
                                        <th>{{ ucwords(lang('category', $translations)) }}</th>
                                        <th>{{ ucwords(lang('SKU var.', $translations)) }}</th>
                                        <th>{{ ucwords(lang('product name', $translations)) }}</th>
                                        <th>{{ ucwords(lang('product image', $translations)) }}</th>
                                        {{-- <th>{{ ucwords(lang('QTY', $translations)) }}</th>
                                        <th>{{ ucwords(lang('price', $translations)) }}</th> --}}
                                        {{-- <th>{{ ucwords(lang('campaign start', $translations)) }}</th>
                                        <th>{{ ucwords(lang('campaign end', $translations)) }}</th> --}}
                                        {{-- <th>{{ ucwords(lang('approval status', $translations)) }}</th> --}}
                                        <th>{{ ucwords(lang('published status', $translations)) }}</th>
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
                                        <th>ID</th>
                                        <th>{{ ucwords(lang('seller', $translations)) }}</th>
                                        <th>{{ ucwords(lang('category', $translations)) }}</th>
                                        <th>{{ ucwords(lang('SKU var.', $translations)) }}</th>
                                        <th>{{ ucwords(lang('product name', $translations)) }}</th>
                                        <th>{{ ucwords(lang('product image', $translations)) }}</th>
                                        {{-- <th>{{ ucwords(lang('QTY', $translations)) }}</th>
                                        <th>{{ ucwords(lang('price', $translations)) }}</th> --}}
                                        {{-- <th>{{ ucwords(lang('campaign start', $translations)) }}</th>
                                        <th>{{ ucwords(lang('campaign end', $translations)) }}</th>
                                        <th>{{ ucwords(lang('approval status', $translations)) }}</th> --}}
                                        <th>{{ ucwords(lang('published status', $translations)) }}</th>
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
            {{ $function_get_data }}

            $('#status').on('change', function() {
                {{ $function_get_data }}
                $(this).blur();

                set_param_url('status', $(this).val());
            });
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
                ajax: "{{ route('admin.product_item.get_data') }}?status="+status,
                order: [[ 7, 'desc' ]],
                columns: [
                    { data: 'id', name: 'product_item.id' },
                    { data: 'seller_store_name', name: 'seller.store_name' },
                    { data: 'product_category_name', name: 'product_category.name' },
                    { data: 'sku_variant', name: 'product_item_variant.sku_id' },
                    { data: 'name', name: 'product_item.name' },
                    { data: 'source_image', name: 'source_image' },
                    // { data: 'qty', name: 'product_item.qty' },
                    // { data: 'price', name: 'product_item.price' },
                    // { data: 'campaign_start', name: 'product_item.campaign_start' },
                    // { data: 'campaign_end', name: 'product_item.campaign_end' },
                    // { data: 'approval', name: 'approval' },
                    { data: 'published', name: 'published' },
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
                ajax: "{{ route('admin.product_item.get_deleted_data') }}",
                order: [[ 7, 'desc' ]],
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'seller_store_name', name: 'seller_store_name' },
                    { data: 'product_category_name', name: 'product_category.name' },
                    { data: 'sku_variant', name: 'product_item_variant.sku_id' },
                    { data: 'name', name: 'name' },
                    { data: 'source_image', name: 'source_image' },
                    // { data: 'qty', name: 'qty' },
                    // { data: 'price', name: 'price' },
                    // { data: 'campaign_start', name: 'campaign_start' },
                    // { data: 'campaign_end', name: 'campaign_end' },
                    // { data: 'approval', name: 'approval' },
                    { data: 'published', name: 'published' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'updated_at', name: 'updated_at' },
                    { data: 'action', name: 'action' }
                ]
            });
        }

        function preview(id) {
            $.ajax({
                type: "POST",
                url: "{{ route('web.product.ajax_validate_preview_item') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id
                },
                beforeSend: function () {
                    // do something before send the data
                },
            })
            .done(function (response) {
                // Callback handler that will be called on success
                if (typeof response != 'undefined') {
                    if (response.status == true) {
                        var new_tab = window.open(response.data, '_blank');
                    } else {
                        // FAILED RESPONSE
                        alert(response.message);
                    }
                } else {
                    alert('Server not respond, please try again.');
                }
            })
            .fail(function (jqXHR, textStatus, errorThrown) {
                // Callback handler that will be called on failure

                // Log the error to the console
                console.error("The following error occurred: " + textStatus, errorThrown);

                alert("Gagal preview produk, silahkan coba lagi atau hubungi admin.");
            })
            .always(function () {
                // Callback handler that will be called regardless
                // if the request failed or succeeded
            });
        }

        function confirm_export() {
            if (confirm("{{ lang('Are you sure to export this #item?', $translations, ['#item' => 'product item']) }}")) {
                var status = $('#status').val();
                if (typeof status == 'undefined') {
                    status = '';
                }

                window.location.href = "{{ route('admin.product_item.export') }}?status="+status;
            }
        }
    </script>
@endsection