<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;

// Libraries
use App\Libraries\Helper;
use App\Libraries\Anteraja;
use App\Libraries\XenditLaravel;

// Models
use App\Models\buyer;
use App\Models\cart_checkout;
use App\Models\seller;
use App\Models\anteraja_origin;
use App\Models\anteraja_destination;
use App\Models\buyer_address;
use App\Models\invoice;
use App\Models\order;
use App\Models\order_details;
use App\Models\global_config;
use App\Models\product_item;
use App\Models\product_item_variant;
use App\Models\payment_request;
use App\Models\voucher;
use App\Models\payment_webhook;

class TransactionController extends Controller
{
    public function create_transaction(Request $request)
    {
        if ($request->isMethod('post')) {
            # POST

            // isi request
            // $requests = [
            //     'address_id' => 'integer',
            //     'delivery_method' => 'array dgn index menggunakan seller_id',
            //     'use_shipping_insurance' => 'array dgn index menggunakan seller_id',
            //     'subtotal' => 'integer',
            //     'shipping_fee' => 'integer',
            //     'shipping_insurance_fee' => 'integer',
            //     'voucher_code' => 'string',
            //     'discount_amount' => 'integer',
            //     'total_amount' => 'integer'
            // ];

            $rules = [
                'address_id' => 'required',
                'delivery_method' => 'required',
                'subtotal' => 'required|integer',
                'shipping_fee' => 'required|integer',
                'shipping_insurance_fee' => 'required|integer',
                'total_amount' => 'required|integer'
            ];
            if ($request->discount_amount) {
                $rules['discount_amount'] = 'required|integer';
                $rules['voucher_code'] = 'required'; // jika ada nominal diskon, maka harus ada kode voucher yg diinput
            }

            $messages = [
                'required'  => ':attribute ' . 'tidak boleh kosong',
                'integer'   => ':attribute ' . 'tidak valid'
            ];

            $customAttributes = [
                'address_id' => 'Alamat Tujuan Pengiriman',
                'delivery_method' => 'Metode Pengiriman',
                'use_shipping_insurance' => 'Status Asuransi Pengiriman',
                'subtotal' => 'Subtotal',
                'shipping_fee' => 'Total Ongkos Kirim',
                'shipping_insurance_fee' => 'Asuransi Pengiriman',
                'voucher_code' => 'Kode Voucher',
                'discount_amount' => 'Nominal Diskon',
                'total_amount' => 'Harga Total'
            ];

            $this->validate($request, $rules, $messages, $customAttributes);

            DB::beginTransaction();
            try {
                // BUYER ID
                $buyer_id = (int) Session::get('buyer')->id;

                // cek status buyer, pastikan buyer status itu aktif
                // penjagaan jika buyer tsb diblokir, maka tidak bisa buat pesanan
                $buyer = buyer::where('id', $buyer_id)->where('status', 1)->first();
                if (empty($buyer)) {
                    # FAILED
                    return redirect()
                        ->route('web.home')
                        ->with('error', 'Maaf, akun Anda telah diblokir.');
                }

                // pastikan user sudah melengkapi data pribadi dulu
                if (is_null($buyer->fullname) || $buyer->fullname == '' || is_null($buyer->phone_number) || $buyer->phone_number == '') {
                    # FAILED
                    return redirect()
                        ->route('web.buyer.profile')
                        ->with('error', 'Silahkan lengkapi Nama Lengkap & Nomor Telepon Anda terlebih dahulu.');
                }

                // ambil list produk yang dibeli
                $data = cart_checkout::select(
                    'cart_checkout.qty as cart_qty',
                    'cart_checkout.note',
                    'product_item.name',
                    'product_item.image',
                    'product_item.global_stock',
                    'product_item.qty',
                    'product_item.campaign_start',
                    'product_item.campaign_end',
                    'product_item.need_insurance',
                    'product_item_variant.id as variant_id',
                    'product_item_variant.name as variant_name',
                    'product_item_variant.variant_image',
                    'product_item_variant.slug as variant_slug',
                    'product_item_variant.qty as variant_qty',
                    'product_item_variant.price as variant_price',
                    'product_item_variant.weight as variant_weight',
                    'product_item.seller_id',
                    'seller.store_name'
                )
                    ->leftJoin('product_item_variant', 'cart_checkout.product_item_variant_id', 'product_item_variant.id')
                    ->leftJoin('product_item', 'product_item_variant.product_item_id', 'product_item.id')
                    ->leftJoin('seller', 'product_item.seller_id', 'seller.id')
                    ->where('cart_checkout.buyer_id', $buyer_id)
                    ->where('product_item.published_status', 1)
                    ->where('product_item.approval_status', 1)
                    ->whereNull('product_item.deleted_at')
                    ->where('product_item_variant.status', 1)
                    ->whereNull('product_item_variant.deleted_at')
                    ->orderBy('product_item.seller_id')
                    ->get();
                // dd($data);

                if (!isset($data[0])) {
                    # FAILED - redirect to Cart page
                    return redirect()
                        ->route('web.buyer.cart')
                        ->with('error', 'Silahkan masukkan produk pilihan Anda ke Keranjang terlebih dahulu.');
                }

                $now = Helper::convert_timestamp(date('Y-m-d H:i:s'), 'Y-m-d H:i:s', env('APP_TIMEZONE', 'UTC'));

                $subtotal = 0;
                $shipping_fee = 0;
                $shipping_insurance_fee = 0;
                $discount_amount = 0;
                $total_amount = 0;
                $total_weight_per_seller = [];
                $subtotal_per_seller = [];
                $order_per_seller = [];
                $campaign_end_per_seller = [];
                $need_insurance_per_seller = [];
                $product_variant_ids = [];
                $email_orders = [];

                foreach ($data as $key => $value) {
                    // set product name
                    $product_name = $value->name . ' - ' . $value->variant_name;

                    // cek apakah masih bisa dibeli karena masih dlm periode campaign
                    if ($now >= $value->campaign_end) {
                        # FAILED - redirect to Cart page
                        return redirect()
                            ->route('web.buyer.cart')
                            ->with('error', 'Maaf, produk pilihan Anda (' . $product_name . ') telah habis waktu campaign-nya sehingga tidak bisa dipesan lagi.');
                    }

                    // dapatkan wkt campaign_end terlama per seller utk estimasi wkt sampai pengiriman
                    if (isset($campaign_end_per_seller[$value->seller_id])) {
                        # jika sudah ada, maka bandingkan lalu ambil waktu terlama
                        if ($value->campaign_end > $campaign_end_per_seller[$value->seller_id]) {
                            $campaign_end_per_seller[$value->seller_id] = $value->campaign_end;
                        }
                    } else {
                        # set wkt campaign_end
                        $campaign_end_per_seller[$value->seller_id] = $value->campaign_end;
                    }

                    // cek apakah menggunakan global_stock
                    if ($value->global_stock) {
                        # use global_stock
                        $use_global_stock = true;

                        // cek apakah msh ada qty utk dibeli
                        if ($value->qty < $value->cart_qty) {
                            # FAILED - redirect to Cart page
                            return redirect()
                                ->route('web.buyer.cart')
                                ->with('error', 'Maaf, Anda kehabisan produk pilihan Anda (' . $product_name . ').');
                        }
                    } else {
                        # use variant_stock
                        $use_global_stock = false;

                        // cek apakah msh ada qty utk dibeli
                        if ($value->variant_qty < $value->cart_qty) {
                            # FAILED - redirect to Cart page
                            return redirect()
                                ->route('web.buyer.cart')
                                ->with('error', 'Maaf, Anda kehabisan produk pilihan Anda (' . $product_name . ').');
                        }
                    }

                    // hitung "Subtotal"
                    $subtotal_per_product = $value->cart_qty * $value->variant_price;
                    $subtotal += $subtotal_per_product;

                    if (isset($subtotal_per_seller[$value->seller_id])) {
                        $subtotal_per_seller[$value->seller_id] += $subtotal_per_product;
                    } else {
                        $subtotal_per_seller[$value->seller_id] = $subtotal_per_product;
                    }


                    // hitung total berat per seller utk cek ongkos kirim
                    if (isset($total_weight_per_seller[$value->seller_id])) {
                        $total_weight_per_seller[$value->seller_id] += $value->variant_weight * $value->cart_qty;
                    } else {
                        $total_weight_per_seller[$value->seller_id] = $value->variant_weight * $value->cart_qty;
                    }

                    // tampung data order per seller
                    $order_per_seller[$value->seller_id][] = [
                        'product_id' => $value->variant_id,
                        'product_name' => $product_name,
                        'product_variant' => $value->variant_name,
                        'product_image' => (!empty($value->variant_image) ? $value->variant_image : $value->image),
                        'qty' => $value->cart_qty,
                        'weight' => $value->variant_weight,
                        'total_weight' => $value->variant_weight * $value->cart_qty,
                        'price_per_item' => $value->variant_price,
                        'price_subtotal' => $value->variant_price * $value->cart_qty,
                        'remarks' => $value->note,
                        'use_global_stock' => $use_global_stock,
                        'seller' => $value->store_name
                    ];

                    // tampung data id product utk hapus data di shopping_cart & cart_checkout jika berhasil
                    $product_variant_ids[] = $value->variant_id;

                    // simpan status penggunaan asuransi pengiriman per seller
                    if (isset($need_insurance_per_seller[$value->seller_id])) {
                        # jika salah satu produk mewajibkan asuransi pengiriman
                        # maka semua produk dari seller tsb akan diasuransikan
                        if ($need_insurance_per_seller[$value->seller_id] == 0) {
                            // jika status sebelumnya msh blm menggunakan asuransi, maka simpan value terbaru
                            $need_insurance_per_seller[$value->seller_id] = $value->need_insurance;
                        }
                    } else {
                        # simpan status penggunaan asuransi pengiriman
                        $need_insurance_per_seller[$value->seller_id] = $value->need_insurance;
                    }
                }
                // dd($subtotal, $total_weight_per_seller);

                // validasi "Subtotal" hasil perhitungan dgn request yg dikirim
                if ((int) $request->subtotal != $subtotal) {
                    # FAILED - redirect to Checkout page
                    return redirect()
                        ->route('web.cart.checkout')
                        ->with('error', 'Terjadi perbedaan nominal Subtotal, mohon coba lagi.');
                }

                // cek alamat tujuan pengiriman (pastikan itu alamat dari buyer tsb)
                $buyer_address_id = (int) Helper::validate_token($request->address_id);
                $buyer_address = buyer_address::select(
                    'buyer_address.*',
                    'id_provinces.province_name',
                    'id_cities.city_name',
                    'id_sub_districts.sub_district_name',
                    'id_villages.village_name'
                )
                    ->leftJoin('id_provinces', 'buyer_address.province_code', 'id_provinces.province_code')
                    ->leftJoin('id_cities', 'buyer_address.district_code', 'id_cities.city_code')
                    ->leftJoin('id_sub_districts', 'buyer_address.sub_district_code', 'id_sub_districts.sub_district_code')
                    ->leftJoin('id_villages', 'buyer_address.village_code', 'id_villages.village_code')
                    ->where('buyer_address.user_id', $buyer->id)
                    ->where('buyer_address.id', $buyer_address_id)
                    ->first();
                if (empty($buyer_address)) {
                    # FAILED - redirect to Checkout page
                    return redirect()
                        ->route('web.cart.checkout')
                        ->with('error', 'Alamat tujuan pengiriman tidak ditemukan, mohon coba lagi.');
                }

                // cek apakah sudah set data penerima
                if (empty($buyer_address->fullname) || empty($buyer_address->phone_number)) {
                    # FAILED - redirect to Checkout page
                    return redirect()
                        ->route('web.cart.checkout')
                        ->with('error', 'Nama dan nomor telepon penerima untuk alamat tujuan pengiriman yang dipilih belum diset. Mohon set terlebih dulu di menu Daftar Alamat.');
                }

                // cek kode pos alamat tujuan pengiriman
                $destination = anteraja_destination::where('zip_code', $buyer_address->postal_code)->first();

                // jika cek menggunakan kode pos tidak ketemu, maka pakai nama kecamatan
                if (empty($destination)) {
                    // get buyer's sub_district (kecamatan)
                    $buyer_sub_district = DB::table('id_sub_districts')
                        ->select('sub_district_name')
                        ->where('sub_district_postal_codes', 'LIKE', '%' . $buyer_address->postal_code . '%')
                        ->first();
                    if ($buyer_sub_district) {
                        $destination = anteraja_destination::where('district_name', $buyer_sub_district->sub_district_name)->first();
                    }
                }

                if (empty($destination)) {
                    # FAILED - redirect to Checkout page
                    return redirect()
                        ->route('web.cart.checkout')
                        ->with('error', 'Wah, kurir AnterAja belum bisa menjangkau daerah kamu.');
                }

                // set destination code
                $destination_code = $destination->tariff_code;

                // validasi request delivery_method
                $request_delivery_method = [];
                foreach ($request->delivery_method as $encrypted_seller_id => $delivery_method) {
                    $request_delivery_method[Helper::validate_token($encrypted_seller_id)] = $delivery_method;
                }

                // validasi request use_shipping_insurance
                $request_use_shipping_insurance = [];
                if ($request->use_shipping_insurance) {
                    foreach ($request->use_shipping_insurance as $encrypted_seller_id => $use_shipping_insurance) {
                        $seller_id = Helper::validate_token($encrypted_seller_id);

                        # simpan status penggunaan asuransi pengiriman dari request
                        $request_use_shipping_insurance[$seller_id] = $use_shipping_insurance;
                    }
                }

                // cocokkan dgn persyaratan produk wajib asuransi atau tidak
                foreach ($need_insurance_per_seller as $insurance_seller_id => $insurance_seller_status) {
                    if (isset($request_use_shipping_insurance[$insurance_seller_id]) && $insurance_seller_status == 0) {
                        $need_insurance_per_seller[$insurance_seller_id] = 1;
                    }
                }
                // dd($need_insurance_per_seller, $request_use_shipping_insurance);

                // hitung "Total Ongkos Kirim"
                $shipping_per_seller = [];
                foreach ($total_weight_per_seller as $seller_id => $total_weight) {
                    # cek ongkos kirim per seller

                    // get alamat seller utk set origin (asal kirim)
                    $seller = seller::select('postal_code', 'sub_district_code')->where('seller.id', $seller_id)->first();
                    if (empty($seller)) {
                        # FAILED - redirect to Checkout page
                        return redirect()
                            ->route('web.cart.checkout')
                            ->with('error', 'Data penjual tidak ditemukan.');
                    }

                    // cek menggunakan kode pos seller
                    $origin = anteraja_origin::where('zip_code', $seller->postal_code)->first();

                    // jika cek menggunakan kode pos tidak ketemu, maka pakai nama kecamatan
                    if (empty($origin)) {
                        // get seller's sub_district (kecamatan)
                        $seller_sub_district = DB::table('id_sub_districts')
                            ->select('sub_district_name')
                            ->where('sub_district_code', $seller->sub_district_code)
                            ->first();
                        if ($seller_sub_district) {
                            $origin = anteraja_origin::where('district_name', $seller_sub_district->sub_district_name)->first();
                        }
                    }
                    // dd($origin);

                    if (empty($origin)) {
                        # FAILED - redirect to Checkout page
                        return redirect()
                            ->route('web.cart.checkout')
                            ->with('error', 'Maaf, area penjual tidak masuk dalam area operasional AnterAja.');
                    }

                    // set origin data
                    $origin_code = $origin->tariff_code;

                    // validate total weight
                    if ($total_weight < 1000) {
                        // minimal berat itu 1 kg = 1000 gram
                        $total_weight = 1000;
                    }
                    if ($total_weight > 50000) {
                        // max berat itu 50 kg = 50000 gram
                        # FAILED - redirect to Checkout page
                        return redirect()
                            ->route('web.cart.checkout')
                            ->with('error', 'Maaf, maksimal berat paket per satu pengiriman adalah 50 KG.');
                    }

                    // convert berat dari Gram ke KG
                    $total_weight = $total_weight / 1000;

                    // cek ongkos kirim
                    $check_tariff = Anteraja::check_tariff($origin_code, $destination_code, $total_weight);

                    if (!isset($check_tariff['status']) || $check_tariff['status'] == false || !isset($check_tariff['data'][0])) {
                        # FAILED - redirect to Checkout page
                        return redirect()
                            ->route('web.cart.checkout')
                            ->with('error', 'Wah, kurir AnterAja belum bisa menjangkau daerah kamu.');
                    }

                    // validasi ongkos kirim per seller
                    $selected_shipping_method = $request_delivery_method[$seller_id];

                    // validate estimasi sampai
                    $shipping_fee_per_seller = 0;
                    $etd_per_seller = 0;
                    foreach ($check_tariff['data'] as $item) {
                        $explode_estimate = explode(' ', $item->etd);
                        $item->etd_thru = $explode_estimate[0];

                        if (strlen($item->etd_thru) == 3) {
                            // CASE FOR "1-2"
                            $item->etd_last = substr($item->etd_thru, -1);
                        } else {
                            $item->etd_last = $item->etd_thru;
                        }

                        // get ongkos kirim per seller
                        if ($selected_shipping_method == $item->product_code) {
                            $shipping_fee_per_seller = $item->rates;

                            $etd_per_seller = (int) $item->etd_last;
                        }
                    }

                    // hitung total ongkos kirim
                    $shipping_fee += $shipping_fee_per_seller;

                    // hitung "Asuransi Pengiriman"
                    $shipping_insurance_fee_per_seller = 0;
                    if ($need_insurance_per_seller[$seller_id] == 1) {
                        // ASURANSI = 0,2% X HARGA BARANG + 5000 (BIAYA ADM)
                        $shipping_insurance_fee_per_seller = (0.2 / 100) * $subtotal_per_seller[$seller_id] + 5000;

                        // hitung total asuransi kirim (semua seller)
                        $shipping_insurance_fee += $shipping_insurance_fee_per_seller;
                    }

                    // debug purpose
                    $shipping_per_seller[$seller_id] = [
                        'origin_code' => $origin_code,
                        'destination_code' => $destination_code,
                        'total_weight' => $total_weight,
                        'service_type' => $selected_shipping_method,
                        'shipping_fee' => $shipping_fee_per_seller,
                        'shipping_insurance_fee' => $shipping_insurance_fee_per_seller,
                        'estimate_arrived_at' => $etd_per_seller,
                        'check_tariff' => $check_tariff,
                        'subtotal_per_seller' => $subtotal_per_seller[$seller_id],
                    ];
                }
                // dd($shipping_per_seller);

                // validasi "Total Ongkos Kirim" hasil perhitungan dgn request yg dikirim
                if ($shipping_fee != (int) $request->shipping_fee) {
                    // dd('Terjadi perbedaan nominal Total Ongkos Kirim', $shipping_fee, $request->shipping_fee);

                    # FAILED - redirect to Checkout page
                    return redirect()
                        ->route('web.cart.checkout')
                        ->with('error', 'Terjadi perbedaan nominal Total Ongkos Kirim, mohon coba lagi.');
                }

                // validasi "Asuransi Pengiriman" hasil perhitungan dgn request yg dikirim
                if ($shipping_insurance_fee != (int) $request->shipping_insurance_fee) {
                    // dd('Terjadi perbedaan nominal Asuransi Pengiriman', $shipping_insurance_fee, $request->shipping_insurance_fee);

                    # FAILED - redirect to Checkout page
                    return redirect()
                        ->route('web.cart.checkout')
                        ->with('error', 'Terjadi perbedaan nominal Asuransi Pengiriman, mohon coba lagi.');
                }

                // validasi "Kode Voucher" jika diinput
                if ($request->discount_amount) {
                    $voucher_code = Helper::validate_input_text($request->voucher_code, TRUE);

                    $voucher = voucher::where('unique_code', $voucher_code)->first();

                    // voucher tidak ditemukan
                    if (empty($voucher)) {
                        # FAILED - redirect to Checkout page
                        return redirect()
                            ->route('web.cart.checkout')
                            ->with('error', 'Kode voucher tidak terdaftar.');
                    }

                    // cek status voucher
                    if (!$voucher->is_active) {
                        # FAILED - redirect to Checkout page
                        return redirect()
                            ->route('web.cart.checkout')
                            ->with('error', 'Kode voucher tidak terdaftar.');
                    }

                    // hitung total penggunaan voucher ini (hanya hitung dari order yg paid & menunggu pembayaran)
                    $total_used_voucher = invoice::where('voucher_code', $voucher_code)->where('is_cancelled', 0)->count();

                    // validasi qty voucher
                    if ($total_used_voucher >= $voucher->qty) {
                        # FAILED - redirect to Checkout page
                        return redirect()
                            ->route('web.cart.checkout')
                            ->with('error', 'Kuota voucher telah habis.');
                    }

                    // hitung total penggunaan voucher ini (hanya hitung dari order yg paid & menunggu pembayaran) oleh buyer ini
                    $total_used_voucher_by_this_buyer = invoice::where('voucher_code', $voucher_code)->where('is_cancelled', 0)->where('buyer_id', $buyer_id)->count();

                    // validasi qty voucher per user
                    if ($total_used_voucher_by_this_buyer >= $voucher->qty_per_user) {
                        # FAILED - redirect to Checkout page
                        return redirect()
                            ->route('web.cart.checkout')
                            ->with('error', 'Oops, kuota voucher kamu telah habis digunakan.');
                    }

                    // cek minimal transaksi berlakunya voucher
                    if ($subtotal < $voucher->min_transaction) {
                        # FAILED - redirect to Checkout page
                        return redirect()
                            ->route('web.cart.checkout')
                            ->with('error', 'Anda belum memenuhi syarat minimal pembelian sebesar ' . Helper::currency_format($voucher->min_transaction, 0, ',', '.', 'Rp', null));
                    }

                    // cek periode berlakunya voucher
                    if ($now < $voucher->period_begin || $now > $voucher->period_end) {
                        # FAILED - redirect to Checkout page
                        return redirect()
                            ->route('web.cart.checkout')
                            ->with('error', 'Kode voucher tidak berlaku.');
                    }

                    // validasi tipe voucher
                    if ($voucher->voucher_type == 'shipping') {
                        $amount_calculate_voucher = $shipping_fee;
                    }

                    if ($voucher->voucher_type == 'transaction') {
                        $amount_calculate_voucher = $subtotal;
                    }

                    if (!isset($amount_calculate_voucher)) {
                        # FAILED - redirect to Checkout page
                        return redirect()
                            ->route('web.cart.checkout')
                            ->with('error', 'Kode voucher tidak terdaftar.');
                    }

                    // hitung "Nominal Diskon"
                    if ($voucher->discount_type == 'percentage') {
                        // jika diskon dlm persentase
                        $discount_amount = $amount_calculate_voucher * $voucher->discount_value / 100;
                    }

                    if ($voucher->discount_type == 'amount') {
                        // jika diskon dlm nominal langsung
                        $discount_amount = $voucher->discount_value;
                    }

                    // validasi nominal maksimal diskon
                    if (!empty($voucher->discount_max_amount) && $discount_amount > $voucher->discount_max_amount) {
                        $discount_amount = $voucher->discount_max_amount;
                    }

                    if ($voucher->voucher_type == 'shipping' && $discount_amount > $shipping_fee) {
                        $discount_amount = $shipping_fee;
                    }

                    // validasi "Nominal Diskon" hasil perhitungan dgn request yg dikirim
                    $request_discount_amount = (int) $request->discount_amount;
                    if ($discount_amount != $request_discount_amount) {
                        # FAILED - redirect to Checkout page
                        return redirect()
                            ->route('web.cart.checkout')
                            ->with('error', 'Terjadi perbedaan nominal Diskon, mohon coba lagi.');
                    }
                }


                // hitung "Harga Total"
                $total_amount = (int) ($subtotal + $shipping_fee + $shipping_insurance_fee - $discount_amount);

                // validasi "Harga Total" hasil perhitungan dgn request yg dikirim
                if ($total_amount != (int) $request->total_amount) {
                    // dd('Terjadi perbedaan nominal Harga Total', $subtotal, $shipping_fee, $shipping_insurance_fee, $discount_amount);

                    # FAILED - redirect to Checkout page
                    return redirect()
                        ->route('web.cart.checkout')
                        ->with('error', 'Terjadi perbedaan nominal Harga Total, mohon coba lagi.');
                }
                // dd($total_amount);

                // create new invoice
                $invoice = new invoice();

                // generate invoice number
                $invoice_no = 'INV-' . date('YmdHis') . '-' . substr(time(), 0, 4);
                // make sure invoice number is unique
                $invoice->invoice_no = Helper::check_unique('invoices', $invoice_no, 'invoice_no');
                $invoice->buyer_id = $buyer_id;
                $invoice->subtotal = $subtotal;
                $invoice->shipping_fee = $shipping_fee;
                $invoice->shipping_insurance_fee = $shipping_insurance_fee;

                // input voucher details
                if ($discount_amount > 0) {
                    $invoice->voucher_id = $voucher->id;
                    $invoice->voucher_code = $voucher_code;
                    $invoice->voucher_type = $voucher->voucher_type;
                    $invoice->discount_amount = $discount_amount;
                }

                $invoice->total_amount = $total_amount;

                // set waktu expired order ini
                $expired_env            = env('XENDIT_INVOICE_DURATION', 86400);
                $expired_invoice        = date('Y-m-d H:i:s', time() + $expired_env);
                $invoice->expired_at    = $expired_invoice;

                $invoice->save();

                // get invoice id
                $invoice_id = $invoice->id;

                // create order per seller
                $i = 0;
                $order_ids = [];
                foreach ($subtotal_per_seller as $seller_id => $subtotal_seller) {
                    $order = new order();
                    $order->invoice_id = $invoice_id;

                    // generate order number
                    $transaction_id = 'TLK-' . date('YmdHis') . '-' . (substr(time(), 0, 4) + $i);
                    // make sure invoice number is unique
                    $order->transaction_id = Helper::check_unique('order', $transaction_id, 'transaction_id');

                    $order->seller_id = $seller_id;
                    $order->buyer_id = $buyer_id;

                    $order->shipment_province_code     = $buyer_address->province_code;
                    $order->shipment_district_code     = $buyer_address->district_code;
                    $order->shipment_sub_district_code = $buyer_address->sub_district_code;
                    $order->shipment_village_code      = $buyer_address->village_code;
                    $order->shipment_postal_code       = $buyer_address->postal_code;
                    $order->shipment_address_details   = $buyer_address->address_details;
                    $order->receiver_name              = $buyer_address->fullname;
                    $order->receiver_phone             = $buyer_address->phone_number;

                    // sementara di-hardcoded ke AnterAja
                    $order->shipper_id              = 2;
                    $order->shipper_name            = 'AnterAja';

                    // get shipping details per seller
                    $shipping_per_seller_details = $shipping_per_seller[$seller_id];

                    $order->shipper_service_type    = $shipping_per_seller_details['service_type'];
                    $order->origin_code             = $shipping_per_seller_details['origin_code'];
                    $order->destination_code        = $shipping_per_seller_details['destination_code'];
                    $order->shipment_total_weight   = $shipping_per_seller_details['total_weight'] * 1000; // convert dari KG ke Gram

                    // calculate estimate_arrived_at
                    // estimasi sampai = wkt campaign berakhir paling lama dari produk yg dibeli di seller ini + estimasi wkt produksi (dlm hari) + estimasi sampai shipper
                    $total_days = env('ESTIMATED_PRODUCTION_DAYS', 30) + $shipping_per_seller_details['estimate_arrived_at'];
                    $order->estimate_arrived_at = date('Y-m-d', strtotime($campaign_end_per_seller[$seller_id] . ' +' . $total_days . ' days'));

                    $order->price_shipping = (int) $shipping_per_seller_details['shipping_fee'];
                    $order->use_insurance_shipping = $need_insurance_per_seller[$seller_id];
                    $order->insurance_shipping_fee = $shipping_per_seller_details['shipping_insurance_fee'];
                    $order->price_subtotal = (int) $subtotal_seller;

                    $order->price_discount = 0; // currently blm ada diskon per seller

                    $order->price_total = $order->price_subtotal + $order->price_shipping + $order->insurance_shipping_fee - $order->price_discount;

                    // calculate admin fee yg di-charge oleh platform
                    $global_config = global_config::where('name', 'percentage_fee')->first();
                    if (empty($global_config)) {
                        $percentage_fee = 10;
                    } else {
                        $percentage_fee = $global_config->value;
                    }
                    $order->percentage_fee  = $percentage_fee;
                    $order->amount_fee      = $order->price_subtotal * $order->percentage_fee / 100;

                    // set waktu expired order ini
                    $order->expired_at  = $expired_invoice;

                    // simpan data order
                    $order->save();

                    // kumpulkan data id order utk update payment details
                    $order_ids[] = $order->id;

                    // set data orders utk email
                    $email_orders[$seller_id] = [
                        'order_data' => $order,
                        'order_details' => $order_per_seller[$seller_id]
                    ];

                    // insert order details
                    foreach ($order_per_seller[$seller_id] as $key => $order_product) {
                        $order_details                  = new order_details();
                        $order_details->order_id        = $order->id;
                        $order_details->product_id      = $order_product['product_id'];
                        $order_details->qty             = $order_product['qty'];
                        $order_details->weight          = $order_product['weight'];
                        $order_details->total_weight    = $order_product['total_weight'];
                        $order_details->price_per_item  = $order_product['price_per_item'];
                        $order_details->price_subtotal  = $order_product['price_subtotal'];
                        $order_details->remarks         = $order_product['remarks'];
                        $order_details->save();

                        // update stock product
                        $product_item_variant = product_item_variant::find($order_details->product_id);
                        if ($order_product['use_global_stock'] == true) {
                            # use global stock product
                            $product_item = product_item::find($product_item_variant->product_item_id);
                            $product_item->qty = $product_item->qty - $order_details->qty; // available stock dikurangi
                            $product_item->qty_booked = $product_item->qty_booked + $order_details->qty; // booked stock ditambah
                            $product_item->save();
                        } else {
                            # use variant stock product
                            $product_item_variant->qty = $product_item_variant->qty - $order_details->qty; // available stock dikurangi
                            $product_item_variant->qty_booked = $product_item_variant->qty_booked + $order_details->qty; // booked stock ditambah
                            $product_item_variant->save();
                        }
                    }

                    $i++;
                }


                // create invoice for payment gateway
                $external_id = $invoice->invoice_no;  // used invoice_no from invoices table
                $amount = $invoice->total_amount;
                $description = 'LokalKorner';
                $payer_email = $buyer->email;
                $payment_invoice = XenditLaravel::create_invoice($external_id, $amount, $description, $payer_email, $invoice_id);

                // validasi respon dari payment gateway
                if (isset($payment_invoice['request'])) {
                    # SUCCESS

                    // logging request to payment gateway
                    $payment = new payment_request();
                    $payment->order_id = $invoice_id;
                    $payment->request_data = json_encode($invoice['request']);
                    $payment->response_data = json_encode($invoice['response']);
                    $payment->save();

                    // validasi respon dari payment gateway
                    if (isset($payment_invoice['response']['invoice_url'])) {
                        # RESPONSE SUCCESS

                        // simpan payment_url
                        $payment_url = $payment_invoice['response']['invoice_url'];

                        // update ke invoices table
                        $invoice->payment_url = $payment_url;
                        $invoice->save();

                        // update ke order table
                        order::whereIn('id', $order_ids)
                            ->update([
                                'payment_url' => $payment_url
                            ]);

                        // hapus data produk yg dibeli dari cart_checkout
                        DB::table('cart_checkout')
                            ->where('buyer_id', $buyer_id)
                            ->whereIn('product_item_variant_id', $product_variant_ids)
                            ->delete();

                        // hapus data produk yg dibeli dari shopping_cart
                        DB::table('shopping_cart')
                            ->where('buyer_id', $buyer_id)
                            ->whereIn('product_item_variant_id', $product_variant_ids)
                            ->delete();

                        // simpan semua data ke database
                        DB::commit();

                        // ambil data order utk ditampilkan di email
                        $order_details = invoice::select(
                            'product_item.id as product_id',
                            'product_item.global_stock',
                            'product_item.image as product_image',
                            DB::raw('concat(product_item.name, " - ", product_item_variant.name) as product_name'),

                            'product_item_variant.id as product_variant_id',
                            'product_item_variant.name as product_variant_name',
                            'product_item_variant.variant_image as product_variant_image',
                            'order_details.qty',
                            'order_details.weight',

                            'order.seller_id',
                            'seller.store_name',
                            'order.estimate_arrived_at',

                            'order.transaction_id',
                            'order.receiver_name',
                            'order.receiver_phone',
                            'order.shipment_address_details as receiver_address',
                            'id_provinces.province_name as receiver_province_name',
                            'id_cities.city_name as receiver_city_name',
                            'id_sub_districts.sub_district_name as receiver_sub_district_name',
                            'id_villages.village_name as receiver_village_name',
                            'order.shipment_postal_code as receiver_postal_code'
                        )
                            ->leftJoin('order', 'invoices.id', 'order.invoice_id')
                            ->leftJoin('order_details', 'order.id', 'order_details.order_id')
                            ->leftJoin('product_item_variant', 'order_details.product_id', 'product_item_variant.id')
                            ->leftJoin('product_item', 'product_item_variant.product_item_id', 'product_item.id')
                            ->leftJoin('seller', 'order.seller_id', 'seller.id')
                            ->leftJoin('id_provinces', 'order.shipment_province_code', 'id_provinces.province_code')
                            ->leftJoin('id_cities', 'order.shipment_district_code', 'id_cities.city_code')
                            ->leftJoin('id_sub_districts', 'order.shipment_sub_district_code', 'id_sub_districts.sub_district_code')
                            ->leftJoin('id_villages', 'order.shipment_village_code', 'id_villages.village_code')
                            ->where('invoices.id', $invoice->id)
                            ->orderBy('order.seller_id')
                            ->get();

                        # ubah format data order berdasarkan seller
                        $params_child = [
                            'product_id',
                            'global_stock',
                            'product_image',
                            'product_name',
                            'product_variant_id',
                            'product_variant_name',
                            'product_variant_image',
                            'qty',
                            'weight',
                            'seller_id',
                            'store_name',
                            'estimate_arrived_at',
                            'transaction_id',
                            'receiver_name',
                            'receiver_phone',
                            'receiver_address',
                            'receiver_province_name',
                            'receiver_city_name',
                            'receiver_sub_district_name',
                            'receiver_village_name',
                            'receiver_postal_code'
                        ];
                        $order_per_seller = Helper::generate_parent_child_data($order_details, 'seller_id', $params_child);

                        // kirimkan email tagihan (invoice) ke buyer
                        $email_subject = '[' . $invoice->invoice_no . '] Menunggu Pembayaran'; // [INV-20221205084211-1670]

                        $content                        = [];
                        $content['title']               = $email_subject;
                        $content['user_name']           = isset($buyer->fullname) ? $buyer->fullname : $buyer->email; // Vicky Budiman
                        $content['expired_date']        = Helper::convert_date_to_indonesian(Helper::convert_timestamp($invoice->expired_at, 'Y-m-d', env('APP_TIMEZONE', 'UTC'))); // 10 Oktober 2022
                        $content['expired_time']        = Helper::convert_timestamp($invoice->expired_at, 'H:i', env('APP_TIMEZONE', 'UTC')); // 11:33
                        $content['total_price']         = Helper::currency_format($invoice->total_amount, 0, ',', '.', 'Rp', null); // Rp1.210.500
                        $content['link']                = $payment_url;
                        $content['orders']              = $order_per_seller;
                        $content['receiver_address']    = [
                            $buyer_address->address_details,
                            $buyer_address->village_name . ', ' . $buyer_address->sub_district_name . ', ' . $buyer_address->city_name . ', ' . $buyer_address->province_name . ' ' . $buyer_address->postal_code
                        ];
                        $content['invoice']             = $invoice;

                        $email_to = $buyer->email;

                        Mail::send('emails.order.buyer_create_order', ['data' => $content], function ($message) use ($email_to, $email_subject) {
                            if (env('APP_MODE', 'STAGING') == 'STAGING') {
                                $email_subject = '[STAGING] ' . $email_subject;
                            }

                            $message->subject($email_subject);
                            $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                            $message->to($email_to);
                        });

                        // redirect buyer to payment_url to complete the payment
                        return redirect($payment_url);
                    } else {
                        # FAILED - redirect to Checkout page
                        return redirect()
                            ->route('web.cart.checkout')
                            ->with('error', 'Sistem pembayaran gagal dihubungi, mohon coba lagi.');
                    }
                } else {
                    # FAILED - redirect to Checkout page
                    return redirect()
                        ->route('web.cart.checkout')
                        ->with('error', 'Sistem pembayaran bermasalah, mohon coba lagi.');
                }
            } catch (\Exception $ex) {
                DB::rollback();

                $error_msg = $ex->getMessage() . ' in ' . $ex->getFile() . ' at line ' . $ex->getLine();
                Helper::error_logging($error_msg, NULL, NULL, "Error Web/TransactionController@create_transaction()");

                if (env('APP_DEBUG') == FALSE) {
                    $error_msg = 'Ups, terjadi kesalahan. Silahkan hubungi admin untuk bantuan.';
                }

                # FAILED
                return redirect()
                    ->route('web.cart.checkout')
                    ->with('error', $error_msg);
            }
        } else {
            # GET

            return redirect()->route('web.buyer.cart');
        }
    }

    public function payment_webhook_process(Request $request)
    {
        // logging webhook request - save first without validation
        $data = new payment_webhook();
        $data->header = json_encode($request->header());
        $data->body = json_encode($request->all());
        $data->save();

        DB::beginTransaction();
        try {
            # authentication
            if ($request->hasHeader('x-callback-token')) {
                $token_auth = $request->header('x-callback-token');
                if ($token_auth != env('XENDIT_CALLBACK_VERIFICATION_TOKEN')) {
                    # ERROR
                    return response()->json([
                        'message' => 'TOKEN UNAUTHORIZED'
                    ], 401);
                }

                // if authenticated, process the request
                $external_id = $request->external_id;
                $status = $request->status;

                if ($status == 'PAID') {
                    $amount = $request->paid_amount;
                    $payment_method = $request->payment_method;
                    $payment_channel = $request->payment_channel;
                } else {
                    $amount = $request->amount;
                }

                // get invoice data
                $invoice = invoice::where('invoice_no', $external_id)->first();
                if (!$invoice) {
                    # ERROR
                    return response()->json([
                        'message' => 'external_id not found in our system'
                    ], 404);
                }

                // validate the amount
                if ($amount != $invoice->total_amount) {
                    // something's weird - need checking to "payment_webhooks" manually
                    $invoice->payment_remarks = 'amount is not the same as the data in our system. Please check the log #' . $data->id;
                    $invoice->save();

                    DB::commit();

                    # ERROR
                    return response()->json([
                        'message' => 'amount is not the same as the data in our system'
                    ], 404);
                }

                // get order data from this invoice
                // utk update progress status dan update stok
                $order_details = invoice::select(
                    'product_item.id as product_id',
                    'product_item.global_stock',
                    'product_item.image as product_image',
                    DB::raw('concat(product_item.name, " - ", product_item_variant.name) as product_name'),

                    'product_item_variant.id as product_variant_id',
                    'product_item_variant.name as product_variant_name',
                    'product_item_variant.variant_image as product_variant_image',
                    'order_details.qty',
                    'order_details.weight',

                    'order.seller_id',
                    'seller.store_name',
                    'order.estimate_arrived_at',

                    'order.transaction_id',
                    'order.receiver_name',
                    'order.receiver_phone',
                    'order.shipment_address_details as receiver_address',
                    'id_provinces.province_name as receiver_province_name',
                    'id_cities.city_name as receiver_city_name',
                    'id_sub_districts.sub_district_name as receiver_sub_district_name',
                    'id_villages.village_name as receiver_village_name',
                    'order.shipment_postal_code as receiver_postal_code'
                )
                    ->leftJoin('order', 'invoices.id', 'order.invoice_id')
                    ->leftJoin('order_details', 'order.id', 'order_details.order_id')
                    ->leftJoin('product_item_variant', 'order_details.product_id', 'product_item_variant.id')
                    ->leftJoin('product_item', 'product_item_variant.product_item_id', 'product_item.id')
                    ->leftJoin('seller', 'order.seller_id', 'seller.id')
                    ->leftJoin('id_provinces', 'order.shipment_province_code', 'id_provinces.province_code')
                    ->leftJoin('id_cities', 'order.shipment_district_code', 'id_cities.city_code')
                    ->leftJoin('id_sub_districts', 'order.shipment_sub_district_code', 'id_sub_districts.sub_district_code')
                    ->leftJoin('id_villages', 'order.shipment_village_code', 'id_villages.village_code')
                    ->where('invoices.invoice_no', $external_id)
                    ->orderBy('order.seller_id')
                    ->get();

                # ubah format data order berdasarkan seller
                $params_child = [
                    'product_id',
                    'global_stock',
                    'product_image',
                    'product_name',
                    'product_variant_id',
                    'product_variant_name',
                    'product_variant_image',
                    'qty',
                    'weight',
                    'seller_id',
                    'store_name',
                    'estimate_arrived_at',
                    'transaction_id',
                    'receiver_name',
                    'receiver_phone',
                    'receiver_address',
                    'receiver_province_name',
                    'receiver_city_name',
                    'receiver_sub_district_name',
                    'receiver_village_name',
                    'receiver_postal_code'
                ];
                $order_per_seller = Helper::generate_parent_child_data($order_details, 'seller_id', $params_child);

                # PROCESSING THE ORDER STATUS
                if ($status == 'PAID') {
                    // update invoice data
                    $invoice->payment_result_id = $data->id;
                    $invoice->payment_method = $payment_method;
                    $invoice->payment_channel = $payment_channel;
                    $invoice->payment_status = 1; // 0:unpaid | 1:paid
                    $invoice->paid_at = date('Y-m-d H:i:s');
                    $invoice->save();

                    // update order status (sudah dibayar)
                    // order.progress_status >> 1=waiting for payment | 2=paid | 3=shipped | 4=canceled | 5=refunded
                    order::where('invoice_id', $invoice->id)->update([
                        'payment_result_id' => $invoice->payment_result_id,
                        'payment_method' => $invoice->payment_method,
                        'payment_channel' => $invoice->payment_channel,
                        'payment_status' => $invoice->payment_status,
                        'paid_at' => $invoice->paid_at,
                        'progress_status' => 2
                    ]);

                    // pindahkan qty yg dipesan (booked) menjadi sold
                    foreach ($order_details as $order_data) {
                        // cek apakah global_stock
                        if ($order_data->global_stock == 1) {
                            # using global stock

                            $product = product_item::find($order_data->product_id);
                            // jika "qty_booked" >= jumlah qty yg dipesan
                            if ($product->qty_booked >= $order_data->qty) {
                                $product->qty_booked -= $order_data->qty;
                                $product->qty_sold += $order_data->qty;
                                $product->save();
                            }
                        } else {
                            # using variant stock

                            $product = product_item_variant::find($order_data->product_variant_id);
                            // jika "qty_booked" >= jumlah qty yg dipesan
                            if ($product->qty_booked >= $order_data->qty) {
                                $product->qty_booked -= $order_data->qty;
                                $product->qty_sold += $order_data->qty;
                                $product->save();
                            }
                        }
                    }
                } elseif ($status == 'EXPIRED') {
                    // update invoice data
                    $invoice->payment_result_id = $data->id;
                    $invoice->is_cancelled = 1;
                    $invoice->save();

                    // update order status (order dibatalkan)
                    // order.progress_status >> 1=waiting for payment | 2=paid | 3=shipped | 4=canceled | 5=refunded
                    order::where('invoice_id', $invoice->id)->update([
                        'payment_result_id' => $invoice->payment_result_id,
                        'progress_status' => 4
                    ]);

                    // release qty yg dipesan menjadi available lagi (kembalikan stok)
                    foreach ($order_details as $order_data) {
                        // cek apakah global_stock
                        if ($order_data->global_stock == 1) {
                            # using global stock

                            $product = product_item::find($order_data->product_id);
                            // jika "qty_booked" >= jumlah qty yg dipesan
                            if ($product->qty_booked >= $order_data->qty) {
                                $product->qty_booked -= $order_data->qty;
                                $product->qty += $order_data->qty;
                                $product->save();
                            }
                        } else {
                            # using variant stock

                            $product = product_item_variant::find($order_data->product_variant_id);
                            // jika "qty_booked" >= jumlah qty yg dipesan
                            if ($product->qty_booked >= $order_data->qty) {
                                $product->qty_booked -= $order_data->qty;
                                $product->qty += $order_data->qty;
                                $product->save();
                            }
                        }
                    }
                } else {
                    # something's weird - need checking to "payment_webhooks" manually
                    $invoice->payment_result_id = $data->id;
                    $invoice->payment_remarks = 'please check the log #' . $data->id;
                    $invoice->save();
                }

                DB::commit();

                // get buyer details
                $buyer = buyer::find($invoice->buyer_id);
                $email_to = $buyer->email;

                // sending email based on payment status
                if ($status == 'PAID') {
                    # PAID TRANSACTION

                    $email_subject = '[' . $invoice->invoice_no . '] Konfirmasi Pemesanan Kamu';

                    $content                = [];
                    $content['title']       = $email_subject;
                    $content['user_name']   = isset($buyer->fullname) ? $buyer->fullname : $buyer->email; // Vicky Budiman
                    $content['invoice']     = $invoice;
                    $content['orders']      = $order_per_seller;

                    Mail::send('emails.order.buyer_paid_order', ['data' => $content], function ($message) use ($email_to, $email_subject) {
                        if (env('APP_MODE', 'STAGING') == 'STAGING') {
                            $email_subject = '[STAGING] ' . $email_subject;
                        }

                        $message->subject($email_subject);
                        $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                        $message->to($email_to);
                    });
                } elseif ($status == 'EXPIRED') {
                    # EXPIRED TRANSACTION

                    $email_subject = '[' . $invoice->invoice_no . '] - Waktu Pembayaran Kamu Habis';

                    $content                = [];
                    $content['title']       = $email_subject;
                    $content['user_name']   = isset($buyer->fullname) ? $buyer->fullname : $buyer->email; // Vicky Budiman
                    $content['invoice']     = $invoice;
                    $content['orders']      = $order_per_seller;

                    Mail::send('emails.order.buyer_expired_order', ['data' => $content], function ($message) use ($email_to, $email_subject) {
                        if (env('APP_MODE', 'STAGING') == 'STAGING') {
                            $email_subject = '[STAGING] ' . $email_subject;
                        }

                        $message->subject($email_subject);
                        $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                        $message->to($email_to);
                    });
                }

                # SUCCESS
                return response()->json([
                    'message' => 'OK'
                ], 200);
            } else {
                # ERROR
                return response()->json([
                    'message' => 'UNAUTHORIZED'
                ], 401);
            }
        } catch (\Exception $ex) {
            DB::rollback();

            $error_msg = $ex->getMessage() . ' in ' . $ex->getFile() . ' at line ' . $ex->getLine();
            Helper::error_logging($error_msg, NULL, NULL, "Error Web/TransactionController@payment_webhook_process()");

            if (env('APP_DEBUG') == FALSE) {
                $error_msg = 'Oops, something went wrong in our system. Please contact our team (vicky@isysedge.com).';
            }

            # ERROR
            return response()->json([
                'message' => $error_msg
            ], 500);
        }
    }

    /**
     * hanya landing page untuk failed redirect page dari Xendit
     * tidak ada logic insert/update data, hanya ada read (select)
     */
    public function transaction_failed($transaction_id)
    {
        // get buyer's session
        $buyer_session = Session::get('buyer');

        // validate transaction_id
        $safe_transaction_id = Helper::validate_input_text($transaction_id);

        // checking invoice based on transaction_id
        // $invoice = invoice::select(
        //     'invoices.created_at as invoice_created_at',
        //     'product_item.id as product_id',
        //     'product_item.global_stock',
        //     'product_item.image as product_image',
        //     DB::raw('concat(product_item.name, " - ", product_item_variant.name) as product_name'),

        //     'product_item_variant.id as product_variant_id',
        //     'product_item_variant.name as product_variant_name',
        //     'product_item_variant.variant_image as product_variant_image',
        //     'order_details.qty',
        //     'order_details.weight',

        //     'order.seller_id',
        //     'seller.store_name',
        //     'order.estimate_arrived_at',

        //     'order.transaction_id',
        //     'order.receiver_name',
        //     'order.receiver_phone',
        //     'order.shipment_address_details as receiver_address',
        //     'id_provinces.province_name as receiver_province_name',
        //     'id_cities.city_name as receiver_city_name',
        //     'id_sub_districts.sub_district_name as receiver_sub_district_name',
        //     'id_villages.village_name as receiver_village_name',
        //     'order.shipment_postal_code as receiver_postal_code'
        // )
        //     ->leftJoin('order', 'invoices.id', 'order.invoice_id')
        //     ->leftJoin('order_details', 'order.id', 'order_details.order_id')
        //     ->leftJoin('product_item_variant', 'order_details.product_id', 'product_item_variant.id')
        //     ->leftJoin('product_item', 'product_item_variant.product_item_id', 'product_item.id')
        //     ->leftJoin('seller', 'order.seller_id', 'seller.id')
        //     ->leftJoin('id_provinces', 'order.shipment_province_code', 'id_provinces.province_code')
        //     ->leftJoin('id_cities', 'order.shipment_district_code', 'id_cities.city_code')
        //     ->leftJoin('id_sub_districts', 'order.shipment_sub_district_code', 'id_sub_districts.sub_district_code')
        //     ->leftJoin('id_villages', 'order.shipment_village_code', 'id_villages.village_code')
        //     ->where('invoices.invoice_no', $safe_transaction_id)
        //     ->where('invoices.buyer_id', $buyer_session->id)
        //     ->orderBy('order.seller_id')
        //     ->get();

        // if (!isset($invoice[0])) {
        //     # ERROR

        //     if (env('APP_DEBUG') == TRUE) {
        //         dd('order not found');
        //     }

        //     return redirect()
        //         ->route('web.home')
        //         ->with('error', 'Invoice tidak ditemukan');
        // }

        $invoice = invoice::where('invoices.invoice_no', $safe_transaction_id);

        if (empty($invoice)) {
            # ERROR
            if (env('APP_DEBUG') == TRUE) {
                dd('order not found');
            }

            return redirect()
                ->route('web.home')
                ->with('error', 'Invoice tidak ditemukan');
        }

        // page ini menggunakan metode GET sehingga perlu dibatasi agar tidak disalahgunakan
        // dibatasi dimana hanya bisa diakses sampai 10 menit setelah order dibuat
        if (time() > (strtotime($invoice[0]->invoice_created_at) + (60 * 10))) {
            # ERROR
            if (env('APP_DEBUG') == FALSE) {
                return redirect()
                    ->route('web.home');
            }
        }

        return view('web.order.payment_failed', compact('invoice'));
    }

    /**
     * hanya landing page untuk success redirect page dari Xendit
     * tidak ada logic insert/update data, hanya ada read (select)
     */
    public function transaction_success($transaction_id)
    {
        // get buyer's session
        $buyer_session = Session::get('buyer');

        // validate transaction_id
        $safe_transaction_id = Helper::validate_input_text($transaction_id);

        // checking invoice based on transaction_id
        $invoice = invoice::select(
            'invoices.created_at as invoice_created_at',
            'product_item.id as product_id',
            'product_item.global_stock',
            'product_item.image as product_image',
            DB::raw('concat(product_item.name, " - ", product_item_variant.name) as product_name'),

            'product_item_variant.id as product_variant_id',
            'product_item_variant.name as product_variant_name',
            'product_item_variant.variant_image as product_variant_image',
            'order_details.qty',
            'order_details.weight',

            'order.seller_id',
            'seller.store_name',
            'order.estimate_arrived_at',

            'order.transaction_id',
            'order.receiver_name',
            'order.receiver_phone',
            'order.shipment_address_details as receiver_address',
            'id_provinces.province_name as receiver_province_name',
            'id_cities.city_name as receiver_city_name',
            'id_sub_districts.sub_district_name as receiver_sub_district_name',
            'id_villages.village_name as receiver_village_name',
            'order.shipment_postal_code as receiver_postal_code'
        )
            ->leftJoin('order', 'invoices.id', 'order.invoice_id')
            ->leftJoin('order_details', 'order.id', 'order_details.order_id')
            ->leftJoin('product_item_variant', 'order_details.product_id', 'product_item_variant.id')
            ->leftJoin('product_item', 'product_item_variant.product_item_id', 'product_item.id')
            ->leftJoin('seller', 'order.seller_id', 'seller.id')
            ->leftJoin('id_provinces', 'order.shipment_province_code', 'id_provinces.province_code')
            ->leftJoin('id_cities', 'order.shipment_district_code', 'id_cities.city_code')
            ->leftJoin('id_sub_districts', 'order.shipment_sub_district_code', 'id_sub_districts.sub_district_code')
            ->leftJoin('id_villages', 'order.shipment_village_code', 'id_villages.village_code')
            ->where('invoices.invoice_no', $safe_transaction_id)
            ->where('invoices.buyer_id', $buyer_session->id)
            ->orderBy('order.seller_id')
            ->get();

        if (!isset($invoice[0])) {
            # ERROR

            if (env('APP_DEBUG') == TRUE) {
                dd('order not found');
            }

            return redirect()
                ->route('web.home')
                ->with('error', 'Invoice tidak ditemukan');
        }

        // page ini menggunakan metode GET sehingga perlu dibatasi agar tidak disalahgunakan
        // dibatasi dimana hanya bisa diakses sampai 10 menit setelah order dibuat
        if (time() > (strtotime($invoice[0]->invoice_created_at) + (60 * 10))) {
            # ERROR
            if (env('APP_DEBUG') == FALSE) {
                return redirect()
                    ->route('web.home');
            }
        }

        $purchased_products = [];
        foreach ($invoice as $item) {
            $purchased_products[] = $item->product_variant_id;
        }

        $now = Helper::convert_timestamp(date('Y-m-d H:i:s'), 'Y-m-d H:i:s', env('APP_TIMEZONE', 'UTC'));

        // get recommended products (kecuali produk2 yg sudah dibeli di transaksi ini)
        $products = product_item::select(
            'product_item.id as product_id',
            DB::raw('concat(product_item.name, " - ", product_item_variant.name) as product_name'),
            'product_item.image as product_image',
            'product_item_variant.variant_image as product_variant_image',
            'product_item_variant.slug',
            'product_item_variant.price',
            'seller.store_name'
        )
            ->leftJoin('product_item_variant', 'product_item.id', 'product_item_variant.product_item_id')
            ->leftJoin('seller', 'product_item.seller_id', 'seller.id')
            ->where('product_item.published_status', 1)
            ->where('product_item.approval_status', 1)
            ->whereNotIn('product_item_variant.id', $purchased_products)
            ->where('product_item.campaign_start', '<', $now)
            ->where('product_item.campaign_end', '>', $now)
            ->where('product_item_variant.is_default', 1)
            ->limit(12)
            ->get();
        // dd($products);

        return view('web.order.payment_thankyou', compact('invoice', 'products'));
    }
}
