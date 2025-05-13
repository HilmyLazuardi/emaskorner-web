@extends('_template_adm.master')

@php
    use App\Libraries\Helper;

    $pagetitle = ucwords(lang('product variant', $translations));

    if (isset($data[0])) {
        # EDIT
        $edit_mode = true;
        $link = route('admin.product_variant.update', ['product_item_id' => $raw_product_item_id]);

        $variant_ids = [];
        $variant_prices = [];
        $variant_weights = [];
        $variant_stocks = [];
        $variant_images = [];
        $variant_status = [];
        foreach ($data as $key => $value) {
            $variant_ids[] = $value->id;
            $variant_prices[] = number_format($value->price);
            $variant_weights[] = number_format($value->weight);
            $variant_stocks[] = number_format($value->qty);
            $variant_images[] = $value->variant_image;
            $variant_status[] = $value->status;
        }

        $campaign_live = false;
        if ($product_item->published_status != 0 || $product_item->approval_status != 0) {
            $campaign_live = true;
        }

        $global_stock = false;
        if ($product_item->global_stock) {
            $global_stock = true;
        }
    } else {
        # ADD NEW
        $edit_mode = false;
        $link = route('admin.product_variant.store', ['product_item_id' => $raw_product_item_id]);
        $campaign_live = false;
    }

    $variants_list = [];
    $variant_1_list = [];
    if ($product_item->variant_1 && $product_item->variant_1_list) {
        $variant_1_list = json_decode($product_item->variant_1_list);

        foreach ($data as $item_variant) {
            $variants_list[] = $item_variant;
        }
    }

    $variant_2_list = [];
    if ($product_item->variant_2 && $product_item->variant_2_list) {
        $variant_2_list = json_decode($product_item->variant_2_list);
    }
@endphp

@section('title', $product_item->name . ' - ' . $pagetitle)

@section('content')
    <div class="">
        <!-- message info -->
        @include('_template_adm.message')

        <div class="page-title">
            <div class="title_left" style="width: 100% !important;">
                <h3>{{ $pagetitle }}</h3>
                <h4>{{ $product_item->name }}</h4>
            </div>
        </div>

        <div class="clearfix"></div>

        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>{{ ucwords(lang('form details', $translations)) }}</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <form id="form_data" class="form-horizontal form-label-left" action="{{ $link }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            {{-- VARIANT 1 NAME --}}
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">{{ ucwords(lang('variant 1', $translations)) }} <span class="required" style="color:red">*</span></label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input type="text" autocomplete="off" name="variant_1" id="variant_1" oninput="variant_table_head_name(1)" class="form-control col-md-7 col-xs-12" placeholder="Masukkan nama varian : Warna / Ukuran / dll"  required="required" value="{{ (old('variant_1')) ? old('variant_1') : ((isset($product_item->variant_1)) ? $product_item->variant_1 : '' ) }}" />
                                </div>
                            </div>

                            {{-- VARIANT 1 LIST --}}
                            <div class="">
                                <div class="sortable_list_1" id="add_list_1">
                                    @php
                                        // $uniqid = Helper::unique_string();
                                        $uniqid = time();
                                    @endphp
                                    <div class="form-group" id="{{ 'form-group-'.$uniqid }}">
                                        <label class="control-label col-md-3 col-sm-3 col-xs-12">&nbsp;</label>
                                        <div class="col-md-6 col-sm-6 col-xs-10">
                                            <input type="text" autocomplete="off" name="variant_1_list['{{ $uniqid }}']" oninput="variant_1_table_list('{{ $uniqid }}', this.value);" class="form-control col-md-7 col-xs-12 variant_1_list" placeholder="Masukkan pilihan varian : Merah / Biru / Hijau / dll" required="required" value="{{ (isset($variant_1_list[0])) ? $variant_1_list[0] : '' }}" />
                                            <input type="hidden" class="uniqid-variant_1" value="{{ $uniqid }}">
                                        </div>
                                        <label class="control-label col-md-2 col-sm-2 col-xs-2 text-align-left" style="display: none">
                                            <span class="sorting-icon"><i class="fa fa-sort fa-lg"></i></span>
                                            <span onclick="delete_option_variant_1('{{ $uniqid }}')"><i class="fa fa-trash fa-lg color-red"></i></span>
                                        </label>
                                    </div>
                                </div>

                                {{-- TAMBAH PILIHAN BUTTON --}}
                                <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">&nbsp;</span></label>
                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                        <span class="btn btn-primary btn-block" onclick="add_option_1()"><i class="fa fa-plus"></i>&nbsp; Tambah Pilihan</span>
                                    </div>
                                </div>
                            </div>

                            <div class="ln_solid"></div>

                            {{-- VARIANT 2 NAME --}}
                            <div class="form-group input_variant_2">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">{{ ucwords(lang('variant 2', $translations)) }} <span class="required" style="color:red">*</span></label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input type="text" autocomplete="off" name="variant_2" id="variant_2" oninput="variant_table_head_name(2)" class="form-control col-md-7 col-xs-12" placeholder="Masukkan nama varian : Warna / Ukuran / dll" value="{{ (old('variant_2')) ? old('variant_2') : ((isset($product_item->variant_2)) ? $product_item->variant_2 : '' ) }}" />
                                </div>
                            </div>

                            {{-- VARIANT 2 LIST --}}
                            <div class="input_variant_2">
                                <div class="sortable_list_2" id="add_list_2">
                                    @php
                                        // $uniqid = Helper::unique_string();
                                        $uniqid_2 = time() + 1;
                                    @endphp
                                    <div class="form-group" id="{{ 'form-group-'.$uniqid_2 }}">
                                        <label class="control-label col-md-3 col-sm-3 col-xs-12">&nbsp;</label>
                                        <div class="col-md-6 col-sm-6 col-xs-10">
                                            <input type="text" autocomplete="off" name="variant_2_list[{{ $uniqid_2 }}]" oninput="variant_2_table_list('{{ $uniqid_2 }}', this.value);" class="form-control col-md-7 col-xs-12 variant_2_list" placeholder="Masukkan pilihan varian : S / M / L / dll" value="{{ (isset($variant_2_list[0])) ? $variant_2_list[0] : '' }}" />
                                            <input type="hidden" class="uniqid-variant_2" value="{{ $uniqid_2 }}">
                                        </div>
                                        <label class="control-label col-md-2 col-sm-2 col-xs-2 text-align-left">
                                            <span class="sorting-icon"><i class="fa fa-sort fa-lg"></i></span>
                                            <span onclick="delete_option_variant_2('{{ $uniqid_2 }}')"><i class="fa fa-trash fa-lg color-red"></i></span>
                                        </label>
                                    </div>
                                </div>

                                {{-- TAMBAH PILIHAN BUTTON --}}
                                <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">&nbsp;</span></label>
                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                        <span class="btn btn-primary btn-block" onclick="add_option_2()"><i class="fa fa-plus"></i>&nbsp; Tambah Pilihan</span>
                                    </div>
                                </div>
                            </div>

                            {{-- JIKA VARIANT 2 TIDAK JADI DIGUNAKAN, MAKA BISA KLIK BUTTON INI --}}
                            @if (!$campaign_live)
                                <div class="form-group" id="remove_input_variant_2">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">&nbsp;</label>
                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                        <span class="btn btn-warning btn-block" onclick="remove_variant_2()"><i class="fa fa-close"></i>&nbsp; Hapus Varian 2</span>
                                    </div>
                                </div>
                            @endif
                            
                            @if (empty($product_item->variant_2_name) && !$campaign_live)
                                {{-- JIKA VARIANT 2 BELUM DISET, MAKA TAMPILKAN BUTTON TAMBAH VARIANT 2 --}}
                                <div class="form-group" id="add_input_variant_2">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">&nbsp;</label>
                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                        <span class="btn btn-info btn-block" onclick="add_variant_2()"><i class="fa fa-plus"></i>&nbsp; Tambah Varian</span>
                                    </div>
                                </div>
                            @endif

                            <div class="ln_solid"></div>

                            {{-- GLOBAL STOCK --}}
                            @if (!$campaign_live || (isset($global_stock) && $global_stock))
                                <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">
                                        {{ ucwords(lang('global stock', $translations)) }}
                                        <i class="fa fa-info-circle" data-toggle="tooltip" title="Aktifkan Global Stock jika tidak ingin membedakan stok per varian"></i>
                                    </label>
                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                        @php
                                            $input_hidden = '';
                                            if ($campaign_live) {
                                                $input_hidden = 'style=display:none;';
                                            }
                                        @endphp
                                        <div class="col-lg-2" {{ $input_hidden }}>
                                            <label>
                                                <input type="checkbox" class="js-switch" name="global_stock" id="global_stock" value="1" {{ (old('global_stock')) ? 'checked' : (($product_item->global_stock == 1) ? 'checked' : '' ) }} />
                                            </label>
                                        </div>
                                        <div class="col-lg-10" id="input_global_stock" style="display: none;">
                                            <input type="text" name="global_stock_value" id="global_stock_value" autocomplete="off" placeholder="Masukkan stok global" class="form-control col-md-7 col-xs-12" onfocus="numbers_only(this);" onkeyup="numbers_only(this);" onblur="this.value=number_formatting(this.value, ',');" value="{{ (old('global_stock_value')) ? old('global_stock_value') : ((isset($product_item->qty)) ? $product_item->qty : '' ) }}" />
                                        </div>
                                    </div>
                                </div>
                                <div class="ln_solid"></div>
                            @endif

                            {{-- INFORMASI PRODUK --}}
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">Informasi Produk</label>
                                <div class="col-md-3 col-sm-3 col-xs-12">
                                    <div class="input-group">
                                        <span class="input-group-addon">Rp</span>
                                        <input type="text" name="global_price" id="global_price" autocomplete="off" placeholder="Harga" class="form-control col-md-7 col-xs-12" onfocus="numbers_only(this);" onkeyup="numbers_only(this);" onblur="this.value=number_formatting(this.value, ',');">
                                    </div>
                                </div>

                                <div class="col-md-3 col-sm-3 col-xs-12">
                                    <div class="input-group">
                                        <span class="input-group-addon">Gr</span>
                                        <input type="text" name="global_weight" id="global_weight" autocomplete="off" placeholder="Berat" class="form-control col-md-7 col-xs-12" onfocus="numbers_only(this);" onkeyup="numbers_only(this);" onblur="this.value=number_formatting(this.value, ',');">
                                    </div>
                                </div>

                                <div class="col-md-3 col-sm-3 col-xs-12">
                                    <span class="btn bg-purple btn-block" onclick="apply_all()">Terapkan Ke Semua</span>
                                </div>
                            </div>

                            {{-- TABEL VARIAN --}}
                            <div class="table-responsive" id="table_variant">
                                <h2>{{ ucwords('tabel varian') }}</h2>
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            @php
                                                $header_1 = !empty($product_item->variant_1_name) ? $product_item->variant_1_name : '';
                                                $header_2 = !empty($product_item->variant_2_name) ? $product_item->variant_2_name : '';
                                            @endphp
                                            <th id="variant_1_name">{{ $header_1 }}</th>
                                            <th id="variant_2_name" class="col_variant_2" style="display: none;">{{ $header_2 }}</th>
                                            <th>Harga</th>
                                            <th class="col_variant_stock">Stok</th>
                                            <th>Berat</th>
                                            <th>Gambar</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="table_body"></tbody>
                                </table>
                            </div>

                            <div class="ln_solid"></div>

                            {{-- ACTION BUTTONS --}}
                            <div class="form-group">
                                <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fa fa-save"></i>&nbsp; {{ ucwords(lang('save', $translations)) }}
                                    </button>
                                    
                                    @php
                                        // set session name utk validasi sesi add new product item atau bukan
                                        $session_count_product_variant = 'count_product_variant_' . urlencode($raw_product_item_id);
                                    @endphp
                                    
                                    {{-- VALIDASI APAKAH DALAM SESI PROSES ADD NEW PRODUCT ITEM --}}
                                    @if (Session::get('add_new_product') == urlencode($raw_product_item_id) && $edit_mode)
                                        @php
                                            if (Session::has($session_count_product_variant)) {
                                                // UPDATE SESSION VALUE
                                                Session::put($session_count_product_variant, 1);
                                            }
                                        @endphp
                                        {{-- JIKA IYA, MAKA MUNCUL BUTTON UNTUK STEP SELANJUTNYA --}}
                                        <a href="{{ route('admin.product_content', $raw_product_item_id) }}" class="btn btn-primary">Lanjut - Set Deskripsi</a>
                                    @endif

                                    {{-- HANYA MUNCUL JIKA BUKAN DALAM SESI PROSES ADD NEW PRODUCT ITEM --}}
                                    @if (!Session::has('add_new_product'))
                                        <a href="{{ route('admin.product_item.edit', $raw_product_item_id) }}" class="btn btn-default">
                                            <i class="fa fa-times"></i>&nbsp; {{ ucwords(lang('close', $translations)) }}
                                        </a>
                                    @endif
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
    <!-- Switchery -->
    @include('_vendors.switchery.css')

    <style>
        .text-align-left {
            text-align: left !important;
        }

        .color-red {
            color: red;
        }

        .sorting-icon {
            /* margin-right: 10px; */
            display: none;
        }

        .input_variant_2, #table_variant {
            display: none;
        }

        #remove_input_variant_2 {
            display: none;
            margin-top: 30px;
        }
    </style>
@endsection

@section('script')
    <!-- Switchery -->
    @include('_vendors.switchery.script')

    <script>
        function add_variant_2() {
            $('#add_input_variant_2').hide();
            $('.input_variant_2').show();
            $('#remove_input_variant_2').show();

            $('.col_variant_2').show();
        }

        function remove_variant_2() {
            // hide input variant 2
            $('.input_variant_2').hide();
            // kosongkan nama variant 2
            $('#variant_2').val('');
            // tampilkan button "Tambah Varian"
            $('#add_input_variant_2').show();
            // hide button "Hapus Varian 2"
            $('#remove_input_variant_2').hide();
            // hide kolom variant 2 dari tabel
            $('.col_variant_2').hide();
            // kosongkan variant 2 dari tabel
            $('.col_variant_2').html('');

            // hapus list pilihan variant 2 dan sisakan 1 yang pertama
            var variant_2_id = $(".uniqid-variant_2").map(function () {
                return this.value;
            }).get();

            variant_2_id.forEach(function (value, key) {
                if (key > 0) {
                    delete_option_variant_2(value, false);
                }
            });

            // kosongkan value pilihan variant 2 pertama
            $('.variant_2_list').val('');
        }

        $('#global_stock').on('change', function() {
            check_global_stock();
        });

        function check_global_stock() {
            var is_global_stock = $('#global_stock').is(':checked');

            if (is_global_stock) {
                $('#input_global_stock').show();
                $('#global_stock_value').attr('required', true);

                $('.col_variant_stock').hide();
                $('.input_variant_stock').attr('required', false);
            } else {
                $('#input_global_stock').hide();
                $('#global_stock_value').attr('required', false);

                $('.col_variant_stock').show();
                $('.input_variant_stock').attr('required', true);
            }
        }

        // set nama varian di table head
        function variant_table_head_name(variant_no) {
            var name = '#variant_'+variant_no;
            var field = '#variant_'+variant_no+'_name';

            var variant_name = $(name).val();
            $(field).text(variant_name);
        }

        function add_option_1(uniqid = Date.now(), option_value = '') {
            var html = '';
            html += '<div class="form-group" id="form-group-'+uniqid+'">';
                html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">&nbsp;</label>';
                html += '<div class="col-md-6 col-sm-6 col-xs-10">';
                    html += '<input type="text" autocomplete="off" name="variant_1_list['+uniqid+']" oninput="variant_1_table_list(\''+uniqid+'\', this.value);" class="form-control col-md-7 col-xs-12 variant_1_list" placeholder="Masukkan pilihan varian : Merah / Biru / Hijau / dll" required="required" value="'+option_value+'" />';
                    html += '<input type="hidden" class="uniqid-variant_1" value="'+uniqid+'">';
                html += '</div>';
                html += '<label class="control-label col-md-2 col-sm-2 col-xs-2 text-align-left">';
                    html += '<span class="sorting-icon"><i class="fa fa-sort fa-lg"></i></span>';
                    html += '<span onclick="delete_option_variant_1('+uniqid+')"><i class="fa fa-trash fa-lg color-red"></i></span>';
                html += '</label>';
            html += '</div>';
            
            $('#add_list_1').append(html);
        }

        function add_option_2(uniqid = Date.now(), option_value = '') {
            var html = '';
            html += '<div class="form-group input_variant_2_item" id="form-group-'+uniqid+'">';
                html += '<label class="control-label col-md-3 col-sm-3 col-xs-12">&nbsp;</label>';
                html += '<div class="col-md-6 col-sm-6 col-xs-10">';
                    html += '<input type="text" autocomplete="off" name="variant_2_list['+uniqid+']" oninput="variant_2_table_list(\''+uniqid+'\', this.value);" class="form-control col-md-7 col-xs-12 variant_2_list" placeholder="Masukkan pilihan varian : S / M / L / dll" required="required" value="'+option_value+'" />';
                    html += '<input type="hidden" class="uniqid-variant_2" value="'+uniqid+'">';
                html += '</div>';
                html += '<label class="control-label col-md-2 col-sm-2 col-xs-2 text-align-left">';
                    html += '<span class="sorting-icon"><i class="fa fa-sort fa-lg"></i></span>';
                    html += '<span onclick="delete_option_variant_2('+uniqid+')"><i class="fa fa-trash fa-lg color-red"></i></span>';
                html += '</label>';
            html += '</div>';
            
            $('#add_list_2').append(html);
        }

        function delete_option_variant_1(uniqid) {
            if (confirm("Anda yakin ingin menghapus pilihan ini?\n(Pilihan yang sudah dihapus tidak bisa dikembalikan)")) {
                // pastikan pilihan variant 1 harus ada minimal 1 (tidak bisa kosong)
                if ($('.variant_1_list').length == 1) {
                    alert('Wajib ada minimal 1 pilihan untuk Variant 1');
                    return false;
                }

                // remove pilihan varian
                $('#form-group-'+uniqid).remove();

                // remove data dari tabel varian
                $('.row-'+uniqid).remove();
            }
            return false;
        }

        function delete_option_variant_2(uniqid, need_confirmation = true) {
            var process_delete = false;
            if (need_confirmation) {
                if (confirm("Anda yakin ingin menghapus pilihan ini?\n(Pilihan yang sudah dihapus tidak bisa dikembalikan)")) {
                    process_delete = true;
                }
            } else {
                process_delete = true;
            }
            
            if (process_delete) {
                // remove pilihan varian
                $('#form-group-'+uniqid).remove();

                // remove data dari tabel varian
                $('.row-'+uniqid).remove();
            }
        }

        function variant_1_table_list(uniqid_var_1, value_var_1) {
            var html = '';

            var variant_2_id = $(".uniqid-variant_2").map(function () {
                return this.value;
            }).get();

            var variant_2_list = $(".variant_2_list").map(function () {
                return this.value;
            }).get();

            // cek apakah ada <tr> dgn class 'row-uniqid_var_1x'
            if ($('.row-' + uniqid_var_1).length == 0) {
                // jika belum ada, maka tambahkan row baru

                var tr_class = 'row-' + uniqid_var_1;
                var tr_id = '';
                var col_variant_2_class = '';
                var col_variant_2_items_style = 'display:none;';

                // cek apakah ada pilihan variant 2 pertama
                var varian_2_first = '';
                if (variant_2_list.length > 0) {
                    // jika ada, maka ...

                    varian_2_first = variant_2_list[0];

                    // set class variant 2 utk <tr>
                    tr_class += ' row-' + variant_2_id[0] + ' row-' + uniqid_var_1 + '-' + variant_2_id[0];
                    tr_id = 'row-' + uniqid_var_1 + '-' + variant_2_id[0];

                    col_variant_2_class = 'col-' + variant_2_id[0];

                    if (varian_2_first != '') {
                        col_variant_2_items_style = '';
                    }
                }

                html += '<tr class="' + tr_class + '">';
                    html += '<td class="col-' + uniqid_var_1 + '">' + value_var_1 + '</td>';
                    html += '<td class="col_variant_2 col_variant_2_items '+col_variant_2_class+'" style="'+col_variant_2_items_style+'">'+varian_2_first+'</td>';
                    html += '<td>'+input_variant_price()+'</td>';
                    html += '<td class="col_variant_stock">'+input_variant_stock()+'</td>';
                    html += '<td>'+input_variant_weight()+'</td>';
                    html += '<td>'+input_variant_image()+'</td>';
                    html += '<td>'+input_variant_status()+'</td>';
                html += '</tr>';

                $('#table_body').append(html);
                $('#table_variant').show();
            } else {
                // jika sudah ada, maka update row existing
                $('.col-' + uniqid_var_1).html(value_var_1);
            }

            // jika ada variant 2
            if (variant_2_list.length > 0) {
                variant_2_id.forEach(function (value, key) {
                    if (variant_2_list[key] != '') {
                        variant_2_table_list(value, variant_2_list[key]);
                    }
                });
            }

            check_global_stock();
        }

        function variant_2_table_list(uniqid_var_2, value_var_2) {
            var variant_1_id = $(".uniqid-variant_1").map(function () {
                return this.value;
            }).get();

            var variant_1_list = $(".variant_1_list").map(function () {
                return this.value;
            }).get();

            var variant_2_id = $(".uniqid-variant_2").map(function () {
                return this.value;
            }).get();

            var variant_2_list = $(".variant_2_list").map(function () {
                return this.value;
            }).get();

            variant_1_id.forEach(function (option_1, i) {
                // cek apakah "pilihan variant 2" ini sudah ada
                if ($('.row-' + option_1 + '-' + uniqid_var_2).length > 0) {
                    // "pilihan variant 2" sudah ada, maka update
                    $('.col-' + uniqid_var_2).html(value_var_2);
                } else {
                    // "pilihan variant 2" belum ada, maka tambahkan

                    // cek ada berapa "pilihan variant 2" yang ada
                    if (variant_2_id.length > 1) {
                        // jika "pilihan variant 2" ada >1, maka masing2 harus ditambahkan "row pilihan variant 2" yg baru

                        var html = '';
                        html += '<tr class="row-' + option_1 + ' row-' + uniqid_var_2 + ' row-' + option_1 + '-' + uniqid_var_2 + '" id="row-' + option_1 + '-' + uniqid_var_2 + '">';
                            html += '<td class="col-' + option_1 + '">' + variant_1_list[i] + '</td>';
                            html += '<td class="col_variant_2 col_variant_2_items col-' + uniqid_var_2 + '">' + value_var_2 + '</td>';
                            html += '<td>'+input_variant_price()+'</td>';
                            html += '<td class="col_variant_stock">'+input_variant_stock()+'</td>';
                            html += '<td>'+input_variant_weight()+'</td>';
                            html += '<td>'+input_variant_image()+'</td>';
                            html += '<td>'+input_variant_status()+'</td>';
                        html += '</tr>';

                        // jika "pilihan variant 2" >2, maka tambahkan "row pilihan variant 2" yg baru setelah latest "row pilihan variant 2"
                        if (variant_2_id.length > 2) {
                            variant_2_id.forEach(function (value, key) {
                                if ($('.row-' + option_1 + '-' + value).length == 0) {
                                    var last_index_var_2 = key - 1;
                                    $(html).insertAfter($('.row-' + option_1 + '-' + variant_2_id[last_index_var_2]));
                                    return true;
                                }
                            });
                        } else {
                            $(html).insertAfter($('.row-' + option_1));
                        }
                    } else {
                        // jika "pilihan variant 2" hanya ada 1, maka ...

                        // isi ".col_variant_2_items" dgn "pilihan variant 2" yg baru
                        $('.col_variant_2_items').html(value_var_2);

                        // tambahkan class "col-uniqid_var_2x" pada class "col_variant_2_items"
                        $('.col_variant_2_items').addClass('col-' + uniqid_var_2);

                        // tambahkan class "row-uniqid_var_2x" pada baris tsb
                        $('.row-' + option_1).addClass('row-' + uniqid_var_2);

                        // tambahkan class "row-uniqid_var_1x-uniqid_var_2x" pada baris tsb
                        $('.row-' + option_1).addClass('row-' + option_1 + '-' + uniqid_var_2);
                    }
                }
            });

            check_global_stock();

            remove_duplicated_rows();
        }

        function remove_duplicated_rows() {
            var variant_1_id = $(".uniqid-variant_1").map(function () {
                return this.value;
            }).get();

            var variant_2_id = $(".uniqid-variant_2").map(function () {
                return this.value;
            }).get();

            variant_1_id.forEach(function (option_1, i) {
                variant_2_id.forEach(function (option_2, j) {
                    if ($('.row-' + option_1 + '-' + option_2).length > 1) {
                        $('#row-' + option_1 + '-' + option_2).remove();
                    }
                });
            });
        }

        function input_variant_price(uniqid = '', value = '') {
            var input_name = 'price';
            @if ($edit_mode)
                if (uniqid == '') {
                    input_name += '_new';
                }
            @endif

            var html = '';

            html += '<div class="input-group">';
                html += '<span class="input-group-addon">Rp</span>';
                html += '<input type="text" name="'+input_name+'['+uniqid+']" value="'+value+'" autocomplete="off" placeholder="Masukkan harga" required="required" class="form-control input_variant_price col-md-4 col-xs-9" onfocus="numbers_only(this)" onkeyup="numbers_only(this);" onblur="this.value=number_formatting(this.value);" />';
            html += '</div>';
            html += '<input type="hidden" name="ordinal[]" value="'+uniqid+'" class="input_variant_ordinal">';

            return html;
        }

        function input_variant_stock(uniqid = '', value = '') {
            var input_name = 'stock';
            @if ($edit_mode)
                if (uniqid == '') {
                    input_name += '_new';
                }
            @endif

            var html = '';

            html += '<input type="text" name="'+input_name+'['+uniqid+']" value="'+value+'" autocomplete="off" placeholder="Masukkan stok" required="required" class="form-control col-md-4 col-xs-9 input_variant_stock" onfocus="numbers_only(this)" onkeyup="numbers_only(this);" onblur="this.value=number_formatting(this.value);" />';

            return html;
        }

        function input_variant_weight(uniqid = '', value = '') {
            var input_name = 'weight';
            @if ($edit_mode)
                if (uniqid == '') {
                    input_name += '_new';
                }
            @endif

            var html = '';

            html += '<div class="input-group">';
                html += '<input type="text" name="'+input_name+'['+uniqid+']" value="'+value+'" autocomplete="off" placeholder="Masukkan berat" required="required" class="form-control input_variant_weight col-md-4 col-xs-9" onfocus="numbers_only(this)" onkeyup="numbers_only(this);" onblur="this.value=number_formatting(this.value);" />';
                html += '<span class="input-group-addon">Gr</span>';
            html += '</div>';

            return html;
        }

        function input_variant_image(uniqid = '', existing_uniqid = '', value = '') {
            var no_image = "{{ asset('images/no-image.png') }}";
            var value_asset = no_image;
            if (value != '') {
                value_asset = "{{ asset('/') }}/"+value;
            }

            var input_name = 'image_variant';
            @if ($edit_mode)
                if (uniqid == '') {
                    input_name += '_new';
                }
            @endif

            var exist_input_name = 'image_variant_exist';
            @if ($edit_mode)
                if (uniqid == '') {
                    exist_input_name += '_new';
                }
            @endif

            var preview_id = Date.now();

            var html = '';

            html += '<label for="image_variant_'+preview_id+'">';
                html += '<img id="vimg_preview_image_'+preview_id+'" src="'+value_asset+'" class="input_variant_image_preview" style="max-width:50px;">';
            html += '</label>';
            html += '<input type="file" name="'+input_name+'['+existing_uniqid+']" id="image_variant_'+preview_id+'" class="form-control input_variant_image col-md-7 col-xs-12" accept="image/*" style="display: none; !important" onchange="readURL(this, \'before\', \'vimg_preview_image_'+preview_id+'\', \''+no_image+'\', \''+value_asset+'\'); input_existing_image('+preview_id+');" />';
            html += '<input type="hidden" name="'+exist_input_name+'['+existing_uniqid+']" value="'+value+'" class="input_variant_image_exist" id="input_variant_image_exist_'+preview_id+'">';

            return html;
        }

        function input_existing_image(uniqid = '') {
            var image = $("#image_variant_"+uniqid)[0].files[0];
            var image_name = image.name;

            $("#input_variant_image_exist_"+uniqid).val(image_name);
        }

        function input_variant_status(uniqid = '', value = 1) {
            var input_name = 'status';
            @if ($edit_mode)
                if (uniqid == '') {
                    input_name += '_new';
                }
            @endif

            var html = '';
            
            html += '<select name="'+input_name+'['+uniqid+']" class="form-control input_variant_status" required="required">';
                html += '<option value="1">Aktif</option>';

                var chosen_0 = '';
                if (value != 1) {
                    chosen_0 = 'selected';
                }
                html += '<option value="0" '+chosen_0+'>Tidak Aktif</option>';
            html += '</select>';

            return html;
        }

        function apply_all() {
            var global_price = $('#global_price').val();
            var global_weight = $('#global_weight').val();

            $('.input_variant_price').val(global_price);
            $('.input_variant_weight').val(global_weight);
        }
    </script>

    <script>
        $(document).ready(function () {
            // cek apakah ada variant 1
            @if (!empty($variant_1_list))
                // set thead name for the variant 1
                variant_table_head_name(1);

                @foreach ($variant_1_list as $key => $variant_1_item)
                    @if ($key == 0)
                        // item list pertama sudah ada, tinggal tampilkan pada tabel
                        variant_1_table_list('{{ $uniqid }}', '{{ $variant_1_item }}');
                    @else
                        @php
                            $uniqid_var_1 = $uniqid + $key + 12500;
                        @endphp

                        // tambahkan item list
                        add_option_1('{{ $uniqid_var_1 }}', '{{ $variant_1_item }}');

                        // tampilkan pada tabel
                        variant_1_table_list('{{ $uniqid_var_1 }}', '{{ $variant_1_item }}');
                    @endif
                @endforeach
            @endif

            // cek apakah ada variant 2
            @if ($product_item->variant_2)
                // tampilkan variant 2 list
                add_variant_2();

                // set thead name for the variant 1
                variant_table_head_name(2);

                @foreach ($variant_2_list as $key => $variant_2_item)
                    @if ($key == 0)
                        // item list pertama sudah ada, tinggal tampilkan pada tabel
                        variant_2_table_list('{{ $uniqid_2 }}', '{{ $variant_2_item }}');
                    @else
                        @php
                            $uniqid_var_2 = $uniqid_2 + $key + 15000;
                        @endphp

                        // tambahkan item list
                        add_option_2('{{ $uniqid_var_2 }}', '{{ $variant_2_item }}');

                        // tampilkan pada tabel
                        variant_2_table_list('{{ $uniqid_var_2 }}', '{{ $variant_2_item }}');
                    @endif
                @endforeach
            @endif

            @if ($edit_mode)
                setTimeout(() => {
                    // input data for table variants
                    input_table_variants();
                }, 500);
            @endif

            // cek apakah menggunakan global stock
            @if ($product_item->qty > 0)
                check_global_stock();
            @endif
        });

        @if ($edit_mode)
            function input_table_variants() {
                var variant_ids = "{!! implode(', ', $variant_ids) !!}".split(', ');
                var variant_prices = "{!! implode(', ', $variant_prices) !!}".split(', ');
                var variant_weights = "{!! implode(', ', $variant_weights) !!}".split(', ');
                var variant_stocks = "{!! implode(', ', $variant_stocks) !!}".split(', ');
                var variant_images = "{!! implode(', ', $variant_images) !!}".split(', ');
                var variant_status = "{!! implode(', ', $variant_status) !!}".split(', ');

                $('.input_variant_price').each(function(key, value) {
                    $(this).attr('name', 'price['+variant_ids[key]+']');
                    $(this).val(variant_prices[key]);
                });

                $('.input_variant_ordinal').each(function(key, value) {
                    $(this).val(variant_ids[key]);
                });

                $('.input_variant_weight').each(function(key, value) {
                    $(this).attr('name', 'weight['+variant_ids[key]+']');
                    $(this).val(variant_weights[key]);
                });

                $('.input_variant_stock').each(function(key, value) {
                    $(this).attr('name', 'stock['+variant_ids[key]+']');
                    $(this).val(variant_stocks[key]);
                });

                $('.input_variant_image').each(function(key, value) {
                    $(this).attr('name', 'image_variant['+variant_ids[key]+']');
                });

                $('.input_variant_image_exist').each(function(key, value) {
                    $(this).attr('name', 'image_variant_exist['+variant_ids[key]+']');
                    $(this).val(variant_images[key]);
                });

                $('.input_variant_image_preview').each(function(key, value) {
                    if (variant_images[key] != '') {
                        $(this).attr('src', '{{ asset("/") }}'+variant_images[key]);
                    }
                });

                $('.input_variant_status').each(function(key, value) {
                    $(this).attr('name', 'status['+variant_ids[key]+']');
                    $(this).val(variant_status[key]);
                });
            }
        @endif

        $("form#form_data").submit(function(e) {
            e.preventDefault();    
            var formData = new FormData(this);

            show_loading();

            $.ajax({
                type: "POST",
                url: "{{ $link }}",
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    // do something before send the data
                }
            })
                .done(function (response) {
                    // Callback handler that will be called on success
                    
                    if (typeof response != 'undefined') {
                        if (response.status == 'true') {
                            // SUCCESS RESPONSE

                            setTimeout(() => {
                                alert(response.message);

                                
                                // refresh page
                                window.location = window.location.href;
                            }, 500);
                        } else {
                            // FAILED RESPONSE

                            setTimeout(() => {
                                alert('ERROR: ' + response.message);
                            }, 500);
                        }
                    } else {
                        setTimeout(() => {
                            alert('Server is not responding, please try again.');
                        }, 500);
                    }
                })
                .fail(function (jqXHR, textStatus, errorThrown) {
                    // Callback handler that will be called on failure

                    // Log the error to the console
                    console.error("The following error occurred: " + textStatus, errorThrown);

                    alert("The following error occurred: " + textStatus + "\n" + errorThrown);
                    // location.reload();
                })
                .always(function () {
                    // Callback handler that will be called regardless
                    // if the request failed or succeeded
                    hide_loading();
            });
        });
    </script>
@endsection
