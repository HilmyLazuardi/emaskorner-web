@extends('_template_adm.master')

@php
    use App\Libraries\Helper;

    $pagetitle = ucwords(lang('order', $translations));
    // if (isset($data)) {
    //    $pagetitle .= ' ('.ucwords(lang('edit', $translations)).')';
    // } else {
    //    $pagetitle .= ' ('.ucwords(lang('new', $translations)).')';
    //    $data       = null;
    // }
@endphp

@section('title', $pagetitle)

@section('content')
    <div class="">
        <!-- message info -->
        @include('_template_adm.message')

        <div class="page-title">
            <div class="title_left">
                <h3>{!! $pagetitle !!}</h3>
            </div>
        </div>

        <div class="clearfix"></div>

        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>{!! ucwords(lang('order', $translations)) !!}</h2>
                        <ul class="nav navbar-right panel_toolbox">
                            <li style="float: right !important;"><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
                        </ul>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        {{-- <form action="#" method="POST"> --}}
                            {{-- @csrf --}}
                            <section class="content invoice">
                                <!-- title row -->
                                <div class="row">
                                    <div class="col-xs-12 invoice-header">
                                        <h1>
                                            <small class="pull-right">{{ $data->transaction_id }}</small>
                                        </h1>
                                        <h1 style="font-size: 32px !important;">
                                            {{-- Rules "Progress Status" (1:waiting for payment | 2:paid | 3:shipped | 4:cancel) --}}
                                            @switch($data->progress_status)
                                                @case(1)
                                                    <label class="label label-info">Menunggu Pembayaran</label>
                                                    @break
                                                @case(2)
                                                    <label class="label label-warning">Siap Dikirim (Terbayar)</label>
                                                    @break
                                                @case(3)
                                                    <label class="label label-success">Telah Dikirim (Selesai)</label>
                                                    @break
                                                @case(4)
                                                    <label class="label label-danger">BATAL</label>
                                                    @break
                                                @case(5)
                                                    <label class="label label-default" style="background:#D3A381; color:#FFFFFF;">Refunded</label>
                                                    @break
                                                @default
                                                    <label class="label label-default">{{ ucwords(lang('unknown', $translations)) }}</label>
                                            @endswitch
                                        </h1>
                                    </div>
                                    <!-- /.col -->
                                </div>

                                <!-- info row -->
                                <div class="row invoice-info" style="margin-top: 10px">
                                    <div class="col-sm-4 invoice-col">
                                        <b>Pengirim :</b>
                                        <address>
                                            <strong>{{ $seller->fullname }}</strong>
                                            <br>{{ $seller->village_name }}, {{ $seller->sub_district_name }}
                                            <br>{{ $seller->city_name }}, {{ $seller->province_name }} {{ $seller->village_postal_codes }}
                                            <br>{!! ucwords(lang('phone', $translations)) !!}: {{ $seller->phone_number }}
                                            <br>{!! ucwords(lang('email', $translations)) !!}: {{ $seller->email }}
                                        </address>
                                    </div>
                                    <!-- /.col -->
                                    <div class="col-sm-4 invoice-col">
                                        <b>Penerima :</b>
                                        <address>
                                            <strong>{{ $data->buyer_fullname }}</strong>
                                            <br>{{ $data->shipment_address_details }}
                                            <br>{!! $data->village_name !!}, {!! $data->sub_district_name !!}
                                            <br>{{ $data->city_name }}, {!! $data->province_name !!} {!! $data->village_postal_codes !!}
                                            {{-- <br>{!! ucwords(lang('phone', $translations)) !!}: {!! $data->buyer_phone_number !!} --}}
                                            {{-- <br>{!! ucwords(lang('email', $translations)) !!}: {!! $data->buyer_email !!} --}}
                                        </address>
                                    </div>
                                    <!-- /.col -->
                                    <div class="col-sm-4 invoice-col">
                                        <b>Tanggal Transaksi Dibuat :</b> {{ Helper::locale_timestamp($data->created_at, 'j/m/Y H:i', false) }} WIB
                                        @if ($data->progress_status == 1)
                                            {{-- hanya tampilkan jika masih Menunggu Pembayaran --}}
                                            <br>
                                            <b>Tanggal Transaksi Kadaluarsa :</b> {{ Helper::locale_timestamp($data->expired_at, 'j/m/Y H:i', false) }} WIB
                                        @endif
                                        @if ($data->paid_at)
                                            {{-- hanya tampilkan jika sudah membayar --}}
                                            <br>
                                            <b>Tanggal Pembayaran :</b> {{ Helper::locale_timestamp($data->paid_at, 'j/m/Y H:i', false) }} WIB
                                        @endif
                                    </div>
                                    <!-- /.col -->
                                </div>
                                <!-- /.row -->

                                <!-- Table row -->
                                <div class="row">
                                    <div class="col-xs-12 table">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Gambar</th>
                                                    <th>SKU</th>
                                                    <th>Produk</th>
                                                    <th>Varian</th>
                                                    <th>Harga</th>
                                                    <th>Qty</th>
                                                    <th>Subtotal</th>
                                                    <th>Catatan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if (isset($order_details[0]))
                                                    @foreach ($order_details as $item)
                                                        <tr>
                                                            <td><img src="{{ asset($item->product_image) }}" alt="{!! $item->product_name !!}" style="max-height:100px;"></td>
                                                            <td>{!! $item->variant_sku !!}</td>
                                                            <td>{!! $item->product_name !!}</td>
                                                            <td>{!! $item->variant_name !!}</td>
                                                            <td>Rp{!! number_format($item->price_per_item, 0, ',', '.') !!}</td>
                                                            <td>{!! $item->qty !!}</td>
                                                            <td>Rp{!! number_format($item->price_subtotal, 0, ',', '.') !!}</td>
                                                            <td>
                                                                @if ($item->remarks)
                                                                    {!! $item->remarks !!}
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- /.col -->
                                </div>
                                <!-- /.row -->

                                <div class="row">
                                    <!-- accepted payments column -->
                                    <div class="col-sm-6" style="margin-top: 10px">
                                        <p class="lead">Informasi Seller</p>
                                        <p class="text-muted well well-sm no-shadow" style="margin-top: 10px;">
                                            @php
                                                // tanggal kirim direkomendasi max 30 hari setelah campaign berakhir
                                                $tanggal_kirim_expired_raw = date('j/m/Y', strtotime($order_details[0]->product_campaign_end . ' +30 days'));
                                                $arr_tmp = explode('/', $tanggal_kirim_expired_raw);
                                                $arr_bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                                $tanggal_kirim_expired = $arr_tmp[0].' '.$arr_bulan[(int) $arr_tmp[1]].' '.$arr_tmp[2];
                                            @endphp
                                            Harap seller mengirimkan pesanan sebelum tanggal {{ $tanggal_kirim_expired }}.
                                        </p>

                                        <p class="lead">Total Berat : {{ number_format($data->shipment_total_weight, 0, ',', '.') }} gram</p>
                                        <p class="lead">Pengiriman : {{ ($data->shipper_name) }} ({{ $data->shipper_service_type }})</p>
                                        <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="shipping_number">Nomor Resi</label>
                                            <div class="col-md-9 col-sm-9 col-xs-12">
                                                <input type="text" value="{{ $data->shipping_number }}" name="shipping_number" id="shipping_number" class="form-control col-xs-12" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /.col -->
                                    <div class="col-sm-6" style="margin-top: 10px">
                                        <p class="lead">
                                            @if ($data->shipped_at)
                                                @php
                                                    $date_raw = Helper::locale_timestamp($data->shipped_at, 'j/m/Y', false);
                                                    $arr_tmp = explode('/', $date_raw);
                                                    $date_indo = $arr_tmp[0].' '.$arr_bulan[(int) $arr_tmp[1]].' '.$arr_tmp[2];
                                                @endphp
                                                Pesanan telah dikirim : {{ $date_indo }}
                                            @else
                                                Mohon dikirim sebelum : {{ $tanggal_kirim_expired }}
                                            @endif
                                        </p>
                                        <div class="table-responsive">
                                            <table class="table">
                                                <tbody>
                                                    <tr>
                                                        <th style="width:50%">Subtotal:</th>
                                                        <td>Rp{{ number_format($data->price_subtotal, 0, ',', '.') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Ongkos Kirim:</th>
                                                        <td>Rp{{ number_format($data->price_shipping, 0, ',', '.') }}</td>
                                                    </tr>
                                                    @if ($data->use_insurance_shipping)
                                                        <tr>
                                                            <th>Asuransi:</th>
                                                            <td>Rp{{ number_format($data->insurance_shipping_fee, 0, ',', '.') }}</td>
                                                        </tr>
                                                    @endif
                                                    <tr>
                                                        <th>TOTAL:</th>
                                                        <td>Rp{{ number_format($data->price_total, 0, ',', '.') }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <!-- /.col -->
                                </div>
                                <!-- /.row -->

                                <div class="ln_solid"></div>

                                <!-- this row will not appear when printing -->
                                <div class="row no-print">
                                    <div class="col-xs-12">
                                        {{-- <button type="button" class="btn btn-default" onclick="window.print();"><i class="fa fa-print"></i> Print</button>
                                        <button type="submit" class="btn btn-success">Request Pickup</button> --}}
                                        <a href="{{ route('admin.order') }}" class="btn btn-default pull-right"><i class="fa fa-times"></i>&nbsp; {!! ucwords(lang('close', $translations)) !!}</a>
                                        {{-- <button class="btn btn-primary pull-right" style="margin-right: 5px;"><i class="fa fa-download"></i> Generate PDF</button> --}}
                                    </div>
                                </div>
                            </section>
                        {{-- </form> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection