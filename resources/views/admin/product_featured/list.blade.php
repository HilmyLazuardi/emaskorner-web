@extends('_template_adm.master')

@php
    $module             = ucwords(lang('product featured', $translations));
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
                                        <th>{{ ucwords(lang('no', $translations)) }}</th>
                                        <th>{{ ucwords(lang('product', $translations)) }}</th>
                                        <th>{{ ucwords(lang('created', $translations)) }}</th>
                                        <th>{{ ucwords(lang('last updated', $translations)) }}</th>
                                        <th>{{ ucwords(lang('action', $translations)) }}</th>
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
    <!-- Sortable-Table -->
    @include('_vendors.sortable_table.css')
@endsection

@section('script')
    <!-- DataTables -->
    @include('_vendors.datatables.script')
    <!-- Sortable-Table -->
    @include('_vendors.sortable_table.script')

    <script>
        var AjaxSortingURL = '{{ route("admin.product_featured.sorting") }}';

        $(document).ready(function() {
            {{ $function_get_data }}
        });

        function refresh_data() {
            $('#datatables').show();
            $.ajax({
                type: 'GET',
                url: "{{ route('admin.product_featured.get_data') }}",
                success: function(response) {
                    if (typeof response.status != 'undefined') {
                        if (response.status == 'true') {
                            var html = '';
                            if (response.data == '') {
                                html += '<tr><td colspan="4"><h2 class="text-center">{{ strtoupper(lang("no data available", $translations)) }}</h2></td></tr>';
                            } else {
                                $.each(response.data, function (index, value) {
                                    html += '<tr role="row" id="row-'+value.product_id+'" title="{{ lang("Drag & drop to sorting", $translations) }}" data-toggle="tooltip">';
                                        html += '<td class="dragndrop">'+value.ordinal+'</td>';
                                        html += '<td>'+value.name+'</td>';
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
    </script>
@endsection