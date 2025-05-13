@extends('_template_adm.master')

@php
    $module             = ucwords(lang('Electronic Contract', $translations));
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
                                        <th>{{ ucwords(lang('title', $translations)) }}</th>
                                        <th>{{ ucwords(lang('action', $translations)) }}</th>
<<<<<<< HEAD
                                        <th>{{ ucwords(lang('created at', $translations)) }}</th>
=======
                                        <th>{{ ucwords(lang('created', $translations)) }}</th>
>>>>>>> 502cbd85893c12d281aaf92921b88213b3be4055
                                        <th>{{ ucwords(lang('last updated', $translations)) }}</th>
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
                ajax: "{{ route('admin.econtract.get_data') }}",
                order: [[ 0, 'desc' ]],
                columns: [
                    { data: 'title', name: 'title' },
                    { data: 'action', name: 'action' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'updated_at', name: 'updated_at' },
                ]
            });
        }
    </script>
@endsection