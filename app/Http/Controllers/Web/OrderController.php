<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Validator;

// LIBRARIES
use App\Libraries\Helper;
use App\Libraries\HelperWeb;
use App\Libraries\XenditLaravel;
use App\Libraries\Jne;
use App\Libraries\Anteraja;

// MODELS
use App\Models\carts;
use App\Models\buyer;
use App\Models\buyer_address;
use App\Models\product_item;
use App\Models\product_item_variant;
use App\Models\shippers;
use App\Models\order;
use App\Models\order_details;
use App\Models\payment_request;
use App\Models\payment_webhook;
use App\Models\seller;
use App\Models\jne_origin;
use App\Models\jne_destination;
use App\Models\anteraja_origin;
use App\Models\anteraja_destination;
use App\Models\global_config;
use App\Models\invoice;

class OrderController extends Controller
{
    // SET BULAN
    protected $bulan = array(1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
    protected $bulan3char = array(1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des');
    protected $indonesian_day = array(
        'Monday'  => 'Senin',
        'Tuesday'   => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday'  => 'Kamis',
        'Friday'    => 'Jumat',
        'Saturday'  => 'Sabtu',
        'Sunday'    => 'Minggu'
    );

    // unused per Dec 2022
    /**
     * ORDER SUMMARY
     */
    public function summary(Request $request)
    {
        $user_session = Session::get('buyer');

        if (is_null($user_session->fullname) || is_null($user_session->phone_number)) {
            // Session::put('from_page', route('web.product.detail', $request->product_slug));

            return redirect()
                ->route('web.buyer.profile')
                ->with('error_profile', 'Isi dulu nama lengkap dan nomor telepon kamu sebelum lanjut ya!');
        }

        // VALIDATE VARIANT / SKU ID
        $variant_id = (int) $request->variant;

        if (empty($variant_id)) {
            return back()
                ->withInput()
                ->with('error_variant', 'Pilih varian dulu sebelum melanjutkan');
        }

        $validation = [
            'product_id'    => 'required|integer',
            'product_slug'  => 'required',
            // 'variant'       => 'required|integer',
            'quant'         => 'required|integer'
        ];

        $message    = [
            'required'  => ':attribute ' . 'tidak boleh kosong',
            'integer'   => ':attribute ' . 'tidak valid'
        ];

        $names      = [
            'product_id'    => 'Produk',
            'product_slug'  => 'Slug',
            // 'variant'       => 'Variant',
            'quant'         => 'Jumlah'
        ];

        $this->validate($request, $validation, $message, $names);

        // BUYER ID
        $buyer_id   = (int) Session::get('buyer')->id;

        $navigation_menu    = HelperWeb::get_nav_menu();

        $data           = new \stdClass();
        $data->date     = date('Y-m-d');

        // CHECK PRODUCT
        $product_item   = product_item::select(
            'product_item.id',
            'product_item.slug',
            'product_item.image as product_image',
            'product_item.name as product_name',
            'product_item.price',
            'product_item.qty',
            'seller.store_name as seller_name'
        )
            ->where('product_item.id', (int) $request->product_id)
            ->leftJoin('seller', 'product_item.seller_id', 'seller.id')
            ->first();

        if (empty($product_item)) {
            return redirect()->route('web.home')->with('error', 'Product tidak tersedia.');
        }

        // CHECK QTY
        if ((int) $request->quant > $product_item->qty) {
            return redirect()->route('web.product.detail', $request->product_slug)->with('error', 'Quantity melebihi stok yang tersedia.');
        }

        $data->seller           = $product_item->seller_name;
        $data->product_id       = $product_item->id;
        $data->product_slug     = $product_item->slug;
        $data->product_image    = $product_item->product_image;
        $data->product_name     = $product_item->product_name;
        $data->product_price    = $product_item->price;
        $data->qty              = (int) $request->quant;

        // CHECK VARIANT
        $variant = product_item_variant::where('product_item_id', (int) $request->product_id)->where('id', $variant_id)->first();

        if (empty($variant)) {
            return redirect()->route('web.product.detail', $request->product_slug)->with('error', 'Variant tidak tersedia.');
        }

        $data->variant_name     = $variant->name;
        $data->variant_sku      = $variant->sku_id;
        $data->variant_id       = $variant->id;
        $data->variant_weight   = $variant->weight;
        $data->total_weight = $request->quant * $variant->weight;

        return view('web.order.summary', compact('data', 'navigation_menu'));
    }

    // unused per Dec 2022
    /**
     * PROCESS ORDER
     */
    public function process(Request $request)
    {
        if (!Session::get('buyer')) {
            return redirect()->route('web.auth.login')->with('error', 'Silahkan login terlebih dahulu.');
        }

        $navigation_menu    = HelperWeb::get_nav_menu();

        // BUYER ID
        $buyer_id = (int) Session::get('buyer')->id;

        if ($request->isMethod('POST')) {
            $validation = [
                'product_id'    => 'required|integer',
                'variant_id'    => 'required',
                'variant_sku'   => 'required',
                'qty'           => 'required|integer'
            ];

            $message    = [
                'required'  => ':attribute ' . 'tidak boleh kosong',
                'integer'   => ':attribute ' . 'tidak valid'
            ];

            $names      = [
                'product_id'    => 'Produk',
                'variant_id'    => 'Variant',
                'variant_sku'   => 'Variant',
                'qty'           => 'Jumlah'
            ];

            $this->validate($request, $validation, $message, $names);

            // VALIDATE SKU
            $variant_sku = Helper::validate_input_text($request->variant_sku);
            if (!$variant_sku) {
                return back()->withInput()->with('error', 'SKU tidak valid.');
            }

            $variant_id = (int) $request->variant_id;
        } else {
            // CHECK BY CART
            $cart = carts::where('user_id', $buyer_id)->first();
            if (empty($cart)) {
                return redirect()->route('web.home')->with('error', 'Tidak ada barang yang bisa diproses.');
            }
        }

        DB::beginTransaction();
        try {
            $data_detail        = new \stdClass();
            $data_detail->date  = date('Y-m-d');

            // CHECK PRODUCT
            if ($request->isMethod('POST')) {
                $product_item   = product_item::select(
                    'product_item.id',
                    'product_item.slug',
                    'product_item.image as product_image',
                    'product_item.name as product_name',
                    'product_item.price',
                    'product_item.qty',
                    'seller.store_name as seller_name',
                    'seller.id as seller_id'
                )
                    ->where('product_item.id', (int) $request->product_id)
                    ->leftJoin('seller', 'product_item.seller_id', 'seller.id')
                    ->first();

                if (empty($product_item)) {
                    return redirect()->route('web.home')->with('error', 'Product tidak tersedia.');
                }

                $quantity = (int) $request->qty;
            } else {
                $product_item = product_item::select(
                    'product_item.id',
                    'product_item.slug',
                    'product_item.image as product_image',
                    'product_item.name as product_name',
                    'product_item.price',
                    'product_item.qty',

                    'seller.store_name as seller_name',
                    'seller.id as seller_id',

                    'product_item_variant.sku_id as variant_sku',
                    'product_item_variant.id as variant_id'
                )
                    ->leftJoin('product_item_variant', 'product_item.id', 'product_item_variant.product_item_id')
                    ->leftJoin('seller', 'product_item.seller_id', 'seller.id')
                    ->where('product_item_variant.id', (int) $cart->product_id)
                    ->first();

                if (empty($product_item)) {
                    return redirect()->route('web.home')->with('error', 'Product tidak tersedia.');
                }

                $quantity = (int) $cart->qty;
            }

            // CHECK QTY
            if ($quantity > $product_item->qty) {
                return redirect()->route('web.product.detail', $product_item->slug)->with('error', 'Quantity melebihi stok yang tersedia.');
            }

            $data_detail->seller_id        = $product_item->seller_id;
            $data_detail->seller           = $product_item->seller_name;
            $data_detail->product_id       = $product_item->id;
            $data_detail->product_slug     = $product_item->slug;
            $data_detail->product_image    = $product_item->product_image;
            $data_detail->product_name     = $product_item->product_name;
            $data_detail->product_price    = $product_item->price;
            $data_detail->qty              = $quantity;

            // CHECK VARIANT
            if ($request->isMethod('GET')) {
                $variant_id     = $product_item->variant_id;
                $variant_sku    = $product_item->variant_sku;
            }

            $variant = product_item_variant::where('product_item_id', (int) $product_item->id)
                ->where('id', $variant_id)
                ->where('sku_id', $variant_sku)
                ->first();

            if (empty($variant)) {
                return redirect()->route('web.product.detail', $product_item->slug)->with('error', 'Variant tidak tersedia.');
            }

            $data_detail->variant_name              = $variant->name;
            $data_detail->variant_sku               = $variant->sku_id;
            $data_detail->variant_id                = $variant->id;
            $data_detail->variant_weight            = $variant->weight;
            $data_detail->total_weight              = $quantity * $variant->weight;
            $data_detail->total_price               = $quantity * $product_item->price;
            $data_detail->formatted_total_weight    = number_format(($quantity * $variant->weight / 1000), 1);

            // BUYER ADDRESS
            $buyer_address = buyer_address::select(
                'buyer_address.*',
                'id_provinces.name as province_name',
                'id_cities.name as city_name',
                'id_sub_districts.name as sub_district_name',
                'id_villages.name as village_name'
            )
                ->leftJoin('id_provinces', 'buyer_address.province_code', 'id_provinces.code')
                ->leftJoin('id_cities', 'buyer_address.district_code', 'id_cities.full_code')
                ->leftJoin('id_sub_districts', 'buyer_address.sub_district_code', 'id_sub_districts.full_code')
                ->leftJoin('id_villages', 'buyer_address.village_code', 'id_villages.full_code')
                ->where('buyer_address.user_id', (int) Session::get('buyer')->id)
                ->get();


            // SHIPPER
            $shippers = shippers::where('status', 1)->orderBy('ordinal')->get();

            $shipper_warning = [];

            // CHECK SHIPPER IS WORING WELL OR NOT
            // if (isset($shippers[0])) {
            //     foreach ($shippers as $key_s => $value_s) {
            //         if ($value_s->name == 'JNE') {
            //             $check_jne = Jne::check_tariff('BKI10000', 'CGK10000', 1); // BEKASI TO JAKARTA WITH 1 KG OF WEIGHT
            //             if (!$check_jne['status']) {
            //                 unset($shippers[$key_s]);

            //                 $obj                = new \stdClass();
            //                 $obj->name          = 'JNE';
            //                 $shipper_warning[]  = $obj;
            //             }
            //         } else {
            //             $check_anteraja = Anteraja::check_tariff('32.75.08', '31.71.07', 1); // BEKASI TIMUR TO JAKARTA PUSAT WITH 1 KG OF WEIGHT
            //             if (!$check_anteraja['status']) {
            //                 unset($shippers[$key_s]);

            //                 $obj                = new \stdClass();
            //                 $obj->name          = 'Anteraja';
            //                 $shipper_warning[]  = $obj;
            //             }
            //         }
            //     }
            // } else {
            //     $obj                = new \stdClass();
            //     $obj->name          = 'JNE';
            //     $shipper_warning[]  = $obj;

            //     $obj                = new \stdClass();
            //     $obj->name          = 'Anteraja';
            //     $shipper_warning[]  = $obj;
            // }

            // CHECK CART BY USER ID, MUST EMPTY
            carts::where('user_id', (int) Session::get('buyer')->id)->delete();

            // ADD NEW CART
            $data               = new carts();
            $data->user_id      = (int) Session::get('buyer')->id;
            $data->product_id   = $data_detail->variant_id;
            $data->qty          = $data_detail->qty;

            // REMARKS
            if ($request->isMethod('POST')) {
                if ($request->catatan) {
                    $data->remarks  = Helper::validate_input_text($request->catatan);
                }
            } else {
                if ($cart->remarks) {
                    $data->remarks  = Helper::validate_input_text($cart->remarks);
                }
            }

            $data->save();

            DB::commit();

            return view('web.order.payment', compact('data', 'data_detail', 'buyer_address', 'shippers', 'navigation_menu', 'shipper_warning'));
        } catch (\Exception $e) {
            DB::rollback();
            if (env('APP_MODE', 'STAGING') == 'STAGING') {
                // dd($e->getMessage() . ' ' . $e->getLine());
                return back()->withInput()->with('error', 'Oops, terjadi kesalahan, silahkan hubungi admin');
            }
        }
    }

    // unused per Dec 2022
    /**
     * CONFIRM PROCESS
     */
    public function confirm(Request $request)
    {
        if (!Session::get('buyer')) {
            return redirect()->route('web.auth.login')->with('error', 'Silahkan login terlebih dahulu.');
        }

        $validation = [
            'product_id'        => 'required|integer',
            'address'           => 'required',
            'shipper'           => 'required|integer',
            'delivery_method'   => 'required',
            'price_shipping'    => 'required|integer',
            'total_price'       => 'required|integer',
            'service_type'      => 'required',
            'origin_code'       => 'required',
            'destination_code'  => 'required',
            'estimate'          => 'integer'
        ];

        $message    = [
            'required'  => ':attribute ' . 'tidak boleh kosong',
            'integer'   => ':attribute ' . 'tidak valid'
        ];

        $names      = [
            'product_id'        => 'Produk',
            'address'           => 'Alamat',
            'shipper'           => 'Kurir',
            'delivery_method'   => 'Metode Pengiriman',
            'price_shipping'    => 'Biaya Pengiriman',
            'total_price'       => 'Harga Total',
            'service_type'      => 'Pengiriman',
            'origin_code'       => 'Origin Code',
            'destination_code'  => 'Destination Code',
            'estimate'          => 'Estimasi Waktu'
        ];

        $this->validate($request, $validation, $message, $names);

        $navigation_menu    = HelperWeb::get_nav_menu();

        // BUYER ID
        $buyer_id   = (int) Session::get('buyer')->id;

        // VALIDATE SKU
        $variant_sku = Helper::validate_input_text($request->variant_sku);
        if (!$variant_sku) {
            return redirect()->route('web.product.detail', $request->product_slug)->with('error', 'SKU tidak tersedia.');
        }

        $variant_id = (int) $request->variant_id;

        DB::beginTransaction();
        try {
            $confirm        = new \stdClass();
            $confirm->date  = date('Y-m-d');

            // PRODUCT
            $product_item   = product_item::select(
                'product_item.id',
                'product_item.slug',
                'product_item.name as product_name',
                'product_item.price',
                'product_item.qty'
            )
                ->where('product_item.id', (int) $request->product_id)
                ->leftJoin('seller', 'product_item.seller_id', 'seller.id')
                ->first();

            if (empty($product_item)) {
                return redirect()->route('web.home')->with('error', 'Product tidak tersedia.');
            }

            // CHECK QTY
            if ((int) $request->qty > $product_item->qty) {
                return redirect()->route('web.product.detail', $request->product_slug)->with('error', 'Quantity melebihi stok yang tersedia.');
            }

            $confirm->product_id       = $product_item->id;
            $confirm->product_slug     = $product_item->slug;
            $confirm->product_name     = $product_item->product_name;
            $confirm->product_price    = $product_item->price;
            $confirm->qty              = (int) $request->qty;

            // CHECK VARIANT
            $variant = product_item_variant::where('product_item_id', (int) $request->product_id)
                ->where('id', $variant_id)
                ->where('sku_id', $variant_sku)
                ->first();

            if (empty($variant)) {
                return redirect()->route('web.product.detail', $request->product_slug)->with('error', 'Variant tidak tersedia.');
            }

            $confirm->variant_id = $variant->id;

            // BUYER ADDRESS
            $pengiriman = buyer_address::select(
                'buyer_address.*',
                'id_provinces.name as province_name',
                'id_cities.name as city_name',
                'id_sub_districts.name as sub_district_name',
                'id_villages.name as village_name'
            )
                ->leftJoin('id_provinces', 'buyer_address.province_code', 'id_provinces.code')
                ->leftJoin('id_cities', 'buyer_address.district_code', 'id_cities.full_code')
                ->leftJoin('id_sub_districts', 'buyer_address.sub_district_code', 'id_sub_districts.full_code')
                ->leftJoin('id_villages', 'buyer_address.village_code', 'id_villages.full_code')
                ->where('buyer_address.id', $request->address)
                ->where('buyer_address.user_id', (int) Session::get('buyer')->id)
                ->first();

            if (empty($pengiriman)) {
                return back()->withInput()->with('error', 'Alamat tidak ditemukan');
            }

            $confirm->address_id = $request->address;

            $shipper = shippers::where('id', $request->shipper)->where('status', 1)->first();

            if (empty($shipper)) {
                return back()->withInput()->with('error', 'jasa pengiriman tidak ditemukan');
            }

            $confirm->shipper_id = $shipper->id;
            $confirm->shipper_name = $shipper->name;
            $confirm->shipper_service_type = Helper::validate_input_text($request->service_type);
            $confirm->origin_code = Helper::validate_input_text($request->origin_code);
            $confirm->destination_code = Helper::validate_input_text($request->destination_code);
            $confirm->estimate = (int) $request->estimate;
            $confirm->delivery_method = $request->delivery_method;
            $confirm->sub_total_price = $request->qty * $product_item->price;
            $confirm->price_shipping = $request->price_shipping;
            $confirm->insurance_shipping_fee = isset($request->insurance_shipping_fee) ? $request->insurance_shipping_fee : 0;
            $confirm->total_price = $request->total_price;

            return view('web.order.confirm', compact('confirm', 'pengiriman', 'navigation_menu'));
        } catch (\Exception $e) {
            DB::rollback();
            if (env('APP_MODE', 'STAGING') == 'STAGING') {
                // dd($e->getMessage() . ' ' . $e->getLine());
                return back()->withInput()->with('error', 'Oops, terjadi kesalahan, silahkan hubungi admin');
            }
        }
    }

    // unused per Dec 2022
    /**
     * CREATE ORDER
     */
    public function create(Request $request)
    {
        if (!Session::get('buyer')) {
            return redirect()
                ->route('web.auth.login')
                ->with('error', 'Silahkan login terlebih dahulu.');
        }

        $validation = [
            'product_id'            => 'required|integer',
            'address'               => 'required',
            'shipper'               => 'required|integer',
            'delivery_method'       => 'required',
            'price_shipping'        => 'required|integer',
            'total_price'           => 'required|integer',
            'shipper_service_type'  => 'required',
            'origin_code'           => 'required',
            'destination_code'      => 'required',
            'estimate'              => 'required|integer'
        ];
        $message    = [
            'required'  => ':attribute ' . 'tidak boleh kosong',
            'integer'   => ':attribute ' . 'tidak valid'
        ];
        $names      = [
            'product_id'            => 'Produk',
            'address'               => 'Alamat',
            'shipper'               => 'Kurir',
            'delivery_method'       => 'Metode Pengiriman',
            'price_shipping'        => 'Biaya Pengiriman',
            'total_price'           => 'Harga Total',
            'shipper_service_type'  => 'Service Type',
            'origin_code'           => 'Origin Code',
            'destination_code'      => 'Destination Code',
            'estimate'              => 'Estimasi'
        ];
        $this->validate($request, $validation, $message, $names);

        DB::beginTransaction();
        try {
            // BUYER ID
            $buyer_id   = (int) Session::get('buyer')->id;

            // CHECK DATA BUYER
            $buyer = buyer::where('id', $buyer_id)->where('status', 1)->first();
            if (empty($buyer)) {
                return redirect()->route('web.home')->with('error', 'Data akun tidak valid, silahkan coba kembali.');
            }

            // CHECK FULLNAME AND PHONE NUMBER
            if (is_null($buyer->fullname) || $buyer->fullname == '' || is_null($buyer->phone_number) || $buyer->phone_number == '') {
                return redirect()->route('web.home')->with('error', 'Silahkan lengkapi Nama Lengkap & Nomor Telepon Anda terlebih dahulu.');
            }

            // CHECK CART
            $product_id = (int) $request->product_id;
            $check_cart = carts::select(
                'carts.qty',
                'carts.remarks',

                'product_item_variant.sku_id',
                'product_item_variant.name as variant_name',
                'product_item_variant.weight',
                'product_item_variant.details as detail_product',

                'product_item.name as product_name',
                'product_item.price as product_price',

                'seller.id as seller_id',
                'seller.store_name as seller_name'
            )
                ->leftJoin('product_item_variant', 'carts.product_id', 'product_item_variant.id')
                ->leftJoin('product_item', 'product_item_variant.product_item_id', 'product_item.id')
                ->leftJoin('seller', 'product_item.seller_id', 'seller.id')
                ->where('user_id', $buyer_id)
                ->where('product_id', $product_id)
                ->first();

            if (empty($check_cart)) {
                return redirect()->route('web.home')->with('error', 'Anda belum memilih produk apapun');
            }

            $data_product = product_item_variant::select(
                'product_item.id',
                'product_item.name',
                'product_item.summary',
                'product_item.slug',
                'product_item.image',
                'product_item.price',
                'product_item.qty',
                'product_item.qty_booked',
                'product_item.campaign_end',
                'seller.store_name as seller_name',
                'order_details.qty as order_qty'
            )
                ->leftJoin('product_item', 'product_item_variant.product_item_id', 'product_item.id')
                ->leftJoin('seller', 'product_item.seller_id', 'seller.id')
                ->leftJoin('order_details', 'product_item_variant.id', 'order_details.product_id')
                ->leftJoin('order', function ($join) {
                    $join->on('order_details.order_id', '=', 'order.id')
                        ->where('order.progress_status', '!=', 2);
                });

            if (isset($product_id)) {
                $product_by_id = $data_product->where('product_item_variant.id', $product_id)->first();
            }

            // $product_all = $data_product->orderBy('product_item.created_at', 'desc')->get();

            $data                   = new \stdClass();
            $data->date             = date('Y-m-d');
            $data->product_name     = $product_by_id->name;
            $data->product_image    = $product_by_id->image;
            $data->seller_name      = $product_by_id->seller_name;
            $data->product_qty      = $product_by_id->qty;
            $data->order_qty        = $product_by_id->order_qty;

            // PROCESS INSERT
            $insert                     = new order();
            $insert->transaction_id     = null;
            $insert->seller_id          = $check_cart->seller_id;
            $insert->buyer_id           = $buyer_id;

            // SHIPMENT
            $address        = (int) $request->address;

            // BUYER ADDRESS
            $check_address = buyer_address::select(
                'buyer_address.*',
                'id_provinces.name as province_name',
                'id_cities.name as city_name',
                'id_sub_districts.name as sub_district_name',
                'id_villages.name as village_name'
            )
                ->leftJoin('id_provinces', 'buyer_address.province_code', 'id_provinces.code')
                ->leftJoin('id_cities', 'buyer_address.district_code', 'id_cities.full_code')
                ->leftJoin('id_sub_districts', 'buyer_address.sub_district_code', 'id_sub_districts.full_code')
                ->leftJoin('id_villages', 'buyer_address.village_code', 'id_villages.full_code')
                ->where('buyer_address.user_id', $buyer_id)
                ->where('id', $address)
                ->first();

            if (empty($check_address)) {
                return redirect()->route('web.home')->with('error', 'Alamat anda tidak tersedia, silahkan coba lagi.');
            }

            $insert->shipment_province_code     = $check_address->province_code;
            $insert->shipment_district_code     = $check_address->district_code;
            $insert->shipment_sub_district_code = $check_address->sub_district_code;
            $insert->shipment_village_code      = $check_address->village_code;
            $insert->shipment_postal_code       = $check_address->postal_code;
            $insert->shipment_address_details   = $check_address->address_details;
            $insert->shipment_remarks           = $check_address->remarks;

            // CHECK SHIPPER
            $shipper_id     = (int) $request->shipper;
            $check_shipper  = shippers::where('id', $shipper_id)->where('status', 1)->first();
            if (empty($check_shipper)) {
                return redirect()->route('web.home')->with('error', 'Kurir tidak tersedia, silahkan coba lagi.');
            }

            $insert->shipper_id     = $check_shipper->id;
            $insert->shipper_name   = $check_shipper->name;

            $insert->origin_code = Helper::validate_input_text($request->origin_code);
            if (!$insert->origin_code) {
                return redirect()->route('web.home')->with('error', 'Data pengiriman tidak valid.');
            }

            $insert->destination_code = Helper::validate_input_text($request->destination_code);
            if (!$insert->destination_code) {
                return redirect()->route('web.home')->with('error', 'Data pengiriman tidak valid.');
            }

            $insert->shipper_service_type = Helper::validate_input_text($request->shipper_service_type);
            if (!$insert->shipper_service_type) {
                return redirect()->route('web.home')->with('error', 'Metode pengiriman tidak valid.');
            }

            $insert->shipment_total_weight  = $check_cart->weight * $check_cart->qty;
            $insert->shipping_number        = null;
            $insert->shipped_at             = null;

            $total_days = 30 + (int) $request->estimate;
            $insert->estimate_arrived_at    = date('Y-m-d', strtotime($product_by_id->campaign_end . ' +' . $total_days . ' days'));
            $insert->price_shipping         = (int) $request->price_shipping;

            if ($request->insurance_shipping_fee) {
                $insert->use_insurance_shipping = 1;
                $insert->insurance_shipping_fee = (int) $request->insurance_shipping_fee;
            } else {
                $insert->use_insurance_shipping = 0;
                $insert->insurance_shipping_fee = null;
            }

            // SUM OF PRICE_SUBTOTAL IN ORDER_DETAILS TABLE
            $insert->price_subtotal = $check_cart->product_price * $check_cart->qty;

            // PERCENTAGE FEE
            $global_config          = global_config::where('name', 'percentage_fee')->first();
            if (empty($global_config)) {
                $percentage_fee     = 10;
            } else {
                $percentage_fee     = $global_config->value;
            }
            $insert->percentage_fee = $percentage_fee;

            // AMOUNT FEE
            $insert->amount_fee     = ($insert->price_subtotal * $insert->percentage_fee) / 100;

            $insert->price_discount = 0;
            $insert->price_total    = $insert->price_shipping + $insert->insurance_shipping_fee + $insert->price_subtotal - $insert->price_discount;

            $insert->order_remarks      = $check_cart->remarks;
            $insert->payment_result_id  = 0;
            $insert->payment_method     = null;
            $insert->payment_channel    = null;
            $insert->payment_remarks    = null;

            $insert->paid_at            = null;
            $insert->payment_status     = 0; // 0:PENDING 1:PAID
            $insert->progress_status    = 1; // 1:WAITING FOR PAYMENT 2:PAID 3:SHIPPED 4:CANCELED

            // EXPIRED AT
            $expired_env                = env('XENDIT_INVOICE_DURATION', 86400);
            $insert->expired_at         = date('Y-m-d H:i:s', time() + $expired_env);

            $insert->save();

            // UPDATE TRANSACTION ID
            $insert->transaction_id = 'TLK-' . date('YmdHis') . '-' . substr(time(), 0, 4);
            $insert->save();

            // THEN INSERT ORDER DETAILS
            $order_detail                   = new order_details();
            $order_detail->order_id         = $insert->id;
            $order_detail->product_id       = $product_id;
            $order_detail->qty              = $check_cart->qty;
            $order_detail->weight           = $check_cart->weight; 
            $order_detail->total_weight     = $check_cart->weight * $check_cart->qty;
            $order_detail->price_per_item   = $check_cart->product_price;
            $order_detail->remarks          = $insert->order_remarks;
            $order_detail->price_subtotal   = $insert->price_subtotal;
            $order_detail->save();

            // UPDATE STOCK IN PRODUCT ITEM
            $product_by_id->qty         = $product_by_id->qty - $check_cart->qty;
            $product_by_id->qty_booked  = $product_by_id->qty_booked + $check_cart->qty;
            product_item::where('id', $product_by_id->id)->update(['qty' => $product_by_id->qty, 'qty_booked' => $product_by_id->qty_booked]);

            // create invoice
            $external_id = $insert->transaction_id;  // used transaction_id from order
            $amount = $insert->price_total;
            $description = 'LokalKorner';
            $payer_email = Session::get('buyer')->email;
            $order_id = $insert->id;
            $invoice = XenditLaravel::create_invoice($external_id, $amount, $description, $payer_email, $order_id);

            if (isset($invoice['request'])) {
                // record the request
                $payment = new payment_request();
                $payment->order_id = $order_id;
                $payment->request_data = json_encode($invoice['request']);
                $payment->response_data = json_encode($invoice['response']);
                $payment->save();

                if (isset($invoice['response']['invoice_url'])) {
                    $payment_url = $invoice['response']['invoice_url'];
                    $insert->payment_url = $payment_url;
                    $insert->save();

                    DB::commit();

                    // GET DATA FOR EMAIL
                    $email_data = order::select(
                        'product_item.name as product_name',
                        'product_item_variant.name as variant_name',
                        'product_item.image as product_image'
                    )
                        ->leftJoin('order_details', 'order.id', 'order_details.order_id')
                        ->leftJoin('product_item_variant', 'order_details.product_id', 'product_item_variant.id')
                        ->leftJoin('product_item', 'product_item_variant.product_item_id', 'product_item.id')
                        ->where('order.id', (int) $insert->id)
                        ->first();

                    // SEND EMAIL
                    $this_subject                       = '[' . $insert->transaction_id . '] Menunggu Pembayaran';

                    $content                            = [];
                    $content['title']                   = '[' . $insert->transaction_id . '] Menunggu Pembayaran';
                    $content['fullname']                = isset($buyer->fullname) ? $buyer->fullname : $buyer->email;
                    $content['method']                  = '-';
                    $content['expired_date']            = Helper::locale_timestamp($insert->expired_at, 'Y-m-d', false);
                    $content['expired_time']            = Helper::locale_timestamp($insert->expired_at, 'H:i', false);
                    $content['total_price']             = 'Rp' . number_format($insert->price_total, 0, ',', '.');
                    $content['transaction_id']          = $insert->transaction_id;


                    $content['product_name']            = $email_data->product_name;
                    $content['quantity']                = $order_detail->qty;
                    $content['weight']                  = number_format(($order_detail->total_weight / 1000), 1);
                    $content['variant_name']            = $email_data->variant_name;
                    $content['image']                   = $email_data->product_image;

                    $content['buyer_name']              = isset($buyer->fullname) ? $buyer->fullname : $buyer->email;
                    $content['buyer_address']           = $check_address->address_details . '<br>'. $check_address->village_name . ', ' . $check_address->sub_district_name . ', ' . $check_address->city_name . ', ' . $check_address->province_name;
                    if (isset($check_address->postal_code)) {
                        $content['buyer_address'] .= ' ' . $check_address->postal_code;
                    }
                    if ($check_address->remarks) {
                        $content['buyer_address'] .= '<br>'.$check_address->remarks;
                    }

                    $content['subtotal']                = 'Rp' . number_format($insert->price_subtotal, 0, ',', '.');
                    $content['shipping_fee']            = 'Rp' . number_format($insert->price_shipping, 0, ',', '.');

                    if (!is_null($insert->insurance_shipping_fee)) {
                        $content['insurance_shipping_fee']  = 'Rp' . number_format($insert->insurance_shipping_fee, 0, ',', '.');
                    } else {
                        $content['insurance_shipping_fee']  = 'Rp0';
                    }
                    $content['total_price']             = 'Rp' . number_format($insert->price_total, 0, ',', '.');

                    $content['link']                    = $insert->payment_url;
                    $company_info = HelperWeb::get_company_info();
                    $content['wa_number']   = env('COUNTRY_CODE').$company_info->wa_phone;
                    $content['wa_link']     = 'https://wa.me/62' . $company_info->wa_phone;
                    $email                              = $buyer->email;

                    Mail::send('emails.order.buyer_create_order', ['data' => $content], function ($message) use ($email, $this_subject) {
                        if (env('APP_MODE', 'STAGING') == 'STAGING') {
                            $this_subject = '[STAGING] ' . $this_subject;
                        }

                        $message->subject($this_subject);
                        $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                        $message->to($email);
                    });

                    // redirect to Xendit (payment page)
                    return redirect($payment_url);
                } else {
                    # ERROR
                    return redirect()->route('web.order.process')->with('error', 'Sistem pembayaran gagal dihubungi, mohon coba lagi.');
                }
            } else {
                # ERROR
                return redirect()->route('web.order.process')->with('error', 'Sistem pembayaran bermasalah, mohon coba lagi.');
            }

            // GET DATA HARDOCED FOR PAYMENT THANKYOU
            // $navigation_menu = HelperWeb::get_nav_menu();
            // $company_info = HelperWeb::get_company_info();
            // $social_media = HelperWeb::get_social_media();

            // return view('web.order.payment_thankyou', compact('navigation_menu', 'company_info', 'social_media', 'data', 'product_all'));
        } catch (\Exception $ex) {
            DB::rollback();

            $error_msg = $ex->getMessage() . ' in ' . $ex->getFile() . ' at line ' . $ex->getLine();
            Helper::error_logging($error_msg, NULL, NULL, "Error Web/OrderController@create()");
            if (env('APP_DEBUG') == FALSE) {
                $error_msg = 'Oops, terjadi kesalahan, silahkan hubungi admin';
            }

            return redirect()->route('web.order.process')->with('error', $error_msg);
        }
    }

    // unused per Dec 2022
    public function payment_webhook_process(Request $request)
    {
        // save first without validation
        $data = new payment_webhook();
        $data->header = json_encode($request->header());
        $data->body = json_encode($request->all());
        $data->save();

        DB::beginTransaction();
        try {
            // auth
            if ($request->hasHeader('x-callback-token')) {
                $token_auth = $request->header('x-callback-token');
                if ($token_auth != env('XENDIT_CALLBACK_VERIFICATION_TOKEN')) {
                    # ERROR
                    return response()->json([
                        'message' => 'TOKEN UNAUTHORIZED'
                    ], 401);
                }

                // process the data
                $external_id = $request->external_id;
                $status = $request->status;

                if ($status == 'PAID') {
                    $amount = $request->paid_amount;
                    $payment_method = $request->payment_method;
                    $payment_channel = $request->payment_channel;
                } else {
                    $amount = $request->amount;
                }

                $invoice = order::where('transaction_id', $external_id)->first();
                if (!$invoice) {
                    # ERROR
                    return response()->json([
                        'message' => 'external_id not found in our system'
                    ], 404);
                }

                // validate the amount
                if ($amount != $invoice->price_total) {
                    // something's weird - need checking to "payment_webhooks" manually
                    $invoice->payment_remarks = 'amount is not the same as the data in our system. Please check the log #' . $data->id;
                    $invoice->save();

                    DB::commit();

                    # ERROR
                    return response()->json([
                        'message' => 'amount is not the same as the data in our system'
                    ], 404);
                }

                // GET ORDER DETAILS
                $order_details = order::select(
                    'product_item.id as product_id',
                    'order_details.qty'
                )
                    ->leftJoin('order_details', 'order.id', 'order_details.order_id')
                    ->leftJoin('product_item_variant', 'order_details.product_id', 'product_item_variant.id')
                    ->leftJoin('product_item', 'product_item_variant.product_item_id', 'product_item.id')
                    ->where('order.transaction_id', $external_id)
                    ->first();

                // PROCESSING THE ORDER STATUS
                if ($status == 'PAID') {
                    $invoice->payment_result_id = $data->id;
                    $invoice->payment_method = $payment_method;
                    $invoice->payment_channel = $payment_channel;
                    // 0:unpaid | 1:paid
                    $invoice->payment_status = 1;
                    $invoice->paid_at = date('Y-m-d H:i:s');
                    // 1=waiting for payment | 2=paid | 3=shipped | 4=canceled
                    $invoice->progress_status = 2;

                    $product = product_item::find($order_details->product_id);
                    // jika "qty_booked" >= jumlah qty yg dipesan
                    if ($product->qty_booked >= $order_details->qty) {
                        // pindahkan stok yg dibooking ke sold
                        $product->qty_booked -= $order_details->qty;
                        $product->qty_sold += $order_details->qty;
                        $product->save();
                    }
                } elseif ($status == 'EXPIRED') {
                    $invoice->payment_result_id = $data->id;
                    // status dibatalkan
                    $invoice->progress_status = 4;

                    $product = product_item::find($order_details->product_id);
                    // jika "qty_booked" >= jumlah qty yg dipesan
                    if ($product->qty_booked >= $order_details->qty) {
                        // kembalikan stok yg dibooking
                        $product->qty_booked -= $order_details->qty;
                        $product->qty += $order_details->qty;
                        $product->save();
                    }
                } else {
                    $invoice->payment_result_id = $data->id;
                    // something's weird - need checking to "payment_webhooks" manually
                    $invoice->payment_remarks = 'please check the log #' . $data->id;
                }
                $invoice->save();

                DB::commit();

                // GET DATA FOR EMAIL
                $email_data = order::select(
                    'buyer.fullname',
                    'buyer.email',

                    'order.price_total',
                    'order.transaction_id',
                    'order.price_subtotal',
                    'order.price_shipping',
                    'order.insurance_shipping_fee',
                    'order.shipment_postal_code',
                    'order.paid_at',
                    'order.estimate_arrived_at',
                    'order.shipment_address_details',
                    'order.shipment_remarks',


                    'order_details.qty',
                    'order_details.total_weight',

                    'product_item.id as product_id',
                    'product_item.qty_booked',
                    'product_item.qty_sold',
                    'product_item.qty AS qty_stock',
                    'product_item.name as product_name',
                    'product_item_variant.name as variant_name',
                    'product_item.image as product_image',

                    'id_provinces.name as province_name',
                    'id_cities.name as city_name',
                    'id_sub_districts.name as sub_district_name',
                    'id_villages.name as village_name'
                )
                    ->leftJoin('buyer', 'order.buyer_id', 'buyer.id')

                    ->leftJoin('order_details', 'order.id', 'order_details.order_id')

                    ->leftJoin('product_item_variant', 'order_details.product_id', 'product_item_variant.id')
                    ->leftJoin('product_item', 'product_item_variant.product_item_id', 'product_item.id')

                    ->leftJoin('id_provinces', 'order.shipment_province_code', 'id_provinces.code')
                    ->leftJoin('id_cities', 'order.shipment_district_code', 'id_cities.full_code')
                    ->leftJoin('id_sub_districts', 'order.shipment_sub_district_code', 'id_sub_districts.full_code')
                    ->leftJoin('id_villages', 'order.shipment_village_code', 'id_villages.full_code')

                    ->where('order.transaction_id', $external_id)
                    ->first();

                if ($status == 'PAID') {
                    #  SEND EMAIL PAID ORDER TO BUYER 
                    $this_subject                       = '[' . $email_data->transaction_id . '] Konfirmasi Pemesanan Kamu';

                    $content                            = [];
                    $content['title']                   = $this_subject;
                    $content['fullname']                = isset($email_data->fullname) ? $email_data->fullname : $email_data->email;
                    $content['total_price']             = 'Rp' . number_format($email_data->price_total, 0, ',', '.');
                    $content['method']                  = str_replace("_", " ", $payment_method) . ' (' . str_replace("_", " ", $payment_channel) . ')';
                    $content['transaction_date']        = Helper::locale_timestamp($email_data->paid_at, 'Y-m-d', false);
                    $content['transaction_time']        = Helper::locale_timestamp($email_data->paid_at, 'H:i', false);
                    $content['estimate_arrived_at']     = Helper::convert_date_to_indonesian($email_data->estimate_arrived_at);
                    $content['link_order_history']      = route('web.order.history');
                    $content['link_home']               = route('web.home');
                    $content['transaction_id']          = $email_data->transaction_id;

                    $content['product_name']            = $email_data->product_name;
                    $content['quantity']                = $email_data->qty;
                    $content['weight']                  = $email_data->total_weight;
                    $content['variant_name']            = $email_data->variant_name;
                    $content['image']                   = $email_data->product_image;

                    $content['buyer_name']              = isset($email_data->fullname) ? $email_data->fullname : $email_data->email;
                    $content['buyer_address']           = $email_data->shipment_address_details .'<br>'. $email_data->village_name . ', ' . $email_data->sub_district_name . ', ' . $email_data->city_name . ', ' . $email_data->province_name;
                    if (isset($email_data->shipment_postal_code)) {
                        $content['buyer_address'] .= ' ' . $email_data->shipment_postal_code;
                    }
                    if ($email_data->shipment_remarks) {
                        $content['buyer_address'] .= '<br>'.$email_data->shipment_remarks;
                    }

                    $content['subtotal']                = 'Rp' . number_format($email_data->price_subtotal, 0, ',', '.');
                    $content['shipping_fee']            = 'Rp' . number_format($email_data->price_shipping, 0, ',', '.');

                    if (!is_null($email_data->insurance_shipping_fee)) {
                        $content['insurance_shipping_fee']  = 'Rp' . number_format($email_data->insurance_shipping_fee, 0, ',', '.');
                    } else {
                        $content['insurance_shipping_fee']  = 'Rp0';
                    }
                    $content['total_price']             = 'Rp' . number_format($email_data->price_total, 0, ',', '.');

                    $company_info = HelperWeb::get_company_info();
                    $content['wa_number']   = env('COUNTRY_CODE').$company_info->wa_phone;
                    $content['wa_link']     = 'https://wa.me/62' . $company_info->wa_phone;
                    $email                              = $email_data->email;

                    Mail::send('emails.order.buyer_paid_order', ['data' => $content], function ($message) use ($email, $this_subject) {
                        if (env('APP_MODE', 'STAGING') == 'STAGING') {
                            $this_subject = '[STAGING] ' . $this_subject;
                        }

                        $message->subject($this_subject);
                        $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                        $message->to($email);
                    });
                } elseif ($status == 'EXPIRED') {
                    # SEND EMAIL EXPIRED ORDER TO BUYER
                    $this_subject                       = '[' . $email_data->transaction_id . '] - Waktu Pembayaran Kamu Habis';

                    $content                            = [];
                    $content['title']                   = $this_subject;
                    $content['fullname']                = isset($email_data->fullname) ? $email_data->fullname : $email_data->email;
                    $content['total_price']             = 'Rp' . number_format($email_data->price_total, 0, ',', '.');
                    $content['transaction_id']          = $email_data->transaction_id;

                    $content['product_name']            = $email_data->product_name;
                    $content['quantity']                = $email_data->qty;
                    $content['weight']                  = $email_data->total_weight;
                    $content['variant_name']            = $email_data->variant_name;
                    $content['image']                   = $email_data->product_image;

                    $content['buyer_name']              = isset($email_data->fullname) ? $email_data->fullname : $email_data->email;
                    $content['buyer_address']           = $email_data->shipment_address_details .'<br>'. $email_data->village_name . ', ' . $email_data->sub_district_name . ', ' . $email_data->city_name . ', ' . $email_data->province_name;
                    if (isset($email_data->shipment_postal_code)) {
                        $content['buyer_address'] .= ' ' . $email_data->shipment_postal_code;
                    }
                    if ($email_data->shipment_remarks) {
                        $content['buyer_address'] .= '<br>'.$email_data->shipment_remarks;
                    }

                    $content['subtotal']                = 'Rp' . number_format($email_data->price_subtotal, 0, ',', '.');
                    $content['shipping_fee']            = 'Rp' . number_format($email_data->price_shipping, 0, ',', '.');

                    if (!is_null($email_data->insurance_shipping_fee)) {
                        $content['insurance_shipping_fee']  = 'Rp' . number_format($email_data->insurance_shipping_fee, 0, ',', '.');
                    } else {
                        $content['insurance_shipping_fee']  = 'Rp0';
                    }
                    $content['total_price']             = 'Rp' . number_format($email_data->price_total, 0, ',', '.');

                    // $content['link']                    = $email_data->payment_url;
                    $company_info = HelperWeb::get_company_info();
                    $content['wa_number']   = env('COUNTRY_CODE').$company_info->wa_phone;
                    $content['wa_link']     = 'https://wa.me/62' . $company_info->wa_phone;
                    $email                              = $email_data->email;

                    Mail::send('emails.link_expired', ['data' => $content], function ($message) use ($email, $this_subject) {
                        if (env('APP_MODE', 'STAGING') == 'STAGING') {
                            $this_subject = '[STAGING] ' . $this_subject;
                        }

                        $message->subject($this_subject);
                        $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                        $message->to($email);
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
            Helper::error_logging($error_msg, NULL, NULL, "Error Web/OrderController@payment_webhook_process()");
            if (env('APP_DEBUG') == FALSE) {
                $error_msg = 'Oops, something went wrong in our system. Please contact our team.';
            }

            # ERROR
            return response()->json([
                'message' => $error_msg
            ], 500);
        }
    }

    /**
     * ORDER HISTORY PAGE
     */
    public function history(Request $request)
    {
        if (!Session::get('buyer')) {
            return redirect()->route('web.auth.login');
        }

        $navigation_menu    = HelperWeb::get_nav_menu();
        $buyer_id           = (int) Session::get('buyer')->id;

        return view('web.order.history', compact('navigation_menu', 'buyer_id'));
    }

    /**
     * ORDER HISTORY DATA
     */
    public function history_data(Request $request)
    {
        // FILTER
        $filter = '';
        if ($request->filter && is_numeric($request->filter)) {
            $filter = (int) $request->filter;
        }

        // KEYWORD
        $keyword = '';
        if ($request->keyword) {
            $keyword = Helper::validate_input_text($request->keyword);
            if (!$keyword) {
                $keyword = '';
            }
        }

        $data = order::select(
            'order.id',
            'order.transaction_id',
            'order.created_at',
            'order.progress_status',
            'order.price_total',
            'order.payment_url',
            'order.estimate_arrived_at',

            'seller.store_name as seller_name',

            'order_details.qty',

            'product_item_variant.name as variant_name',
            'product_item.name as product_name',
            'product_item.image as product_image',
            'product_item_variant.variant_image',

            'invoices.invoice_no'
        )
            ->leftJoin('seller', 'order.seller_id', 'seller.id')
            ->leftJoin('order_details', 'order.id', 'order_details.order_id')
            ->leftJoin('product_item_variant', 'order_details.product_id', 'product_item_variant.id')
            ->leftJoin('product_item', 'product_item_variant.product_item_id', 'product_item.id')
            ->leftJoin('invoices', 'invoices.id', 'order.invoice_id')
            ->where('order.buyer_id', (int) $request->buyer_id)
            ->orderBy('order.created_at', 'desc');

        // FILTER
        if ($filter != '') {
            $data = $data->where('order.progress_status', $filter);
        }

        // KEYWORD
        if ($keyword != '') {
            $data = $data->where(function ($query) use ($keyword) {
                $query->where('product_item.name', "like", "%" . $keyword . "%")
                    ->orWhere('product_item_variant.name', "like", "%" . $keyword . "%")
                    ->orWhere('seller.store_name', "like", "%" . $keyword . "%")
                    ->orWhere('order.transaction_id', "like", "%" . $keyword . "%");
            });
        }

        $data = $data->get();

        $html = '';

        if (isset($data[0])) {
            // GROUPING DATA BY TRANSACTION ID
            $data_output = [];
            foreach ($data as $item) {
                $data_tmp = [
                    'id'                  => $item->id,
                    'invoice_no'          => $item->invoice_no,
                    'transaction_id'      => $item->transaction_id,
                    'created_at'          => $item->created_at,
                    'progress_status'     => $item->progress_status,
                    'price_total'         => $item->price_total,
                    'payment_url'         => $item->payment_url,
                    'estimate_arrived_at' => $item->estimate_arrived_at,
                    'seller_name'         => $item->seller_name,
                    'qty'                 => $item->qty,
                    'variant_name'        => $item->variant_name,
                    'product_name'        => $item->product_name,
                    'product_image'       => empty($item->variant_image) ? $item->product_image : $item->variant_image,
                ];
                $data_tmp = (object) $data_tmp;
    
                if (isset($data_output[$item->transaction_id])) {
                    array_push($data_output[$item->transaction_id], $data_tmp);
                } else {
                    $data_output[$item->transaction_id][] = $data_tmp;
                }
            }

            // CREATE OUTPUT DATA
            foreach ($data_output as $key => $item) {
                if (!empty($item[0]->product_image)) {
                    $item[0]->product_image = asset($item[0]->product_image);
                } else {
                    $item[0]->product_image = '';
                }

                // PRICE
                if (isset($item[0]->price_total)) {
                    $item[0]->formated_price = 'Rp' . number_format($item[0]->price_total, 0, ',', '.');
                }

                // PROGRESS STATUS 
                $item[0]->status = '';
                switch ($item[0]->progress_status) {
                    case 1:
                        $item[0]->status = 'Menunggu pembayaran';
                        $item[0]->label = 'yellow_label';
                        break;

                    case 2:
                        $item[0]->status = 'Sudah dibayar';
                        $item[0]->label = 'green_label';
                        break;

                    case 3:
                        $item[0]->status = 'Sudah dikirim';
                        $item[0]->label = 'blue_label';
                        break;

                    case 4:
                        $item[0]->status = 'Pesanan dibatalkan';
                        $item[0]->label = 'red_label';
                        break;

                    case 5:
                        $item[0]->status = 'Refunded';
                        $item[0]->label = 'orange_label';
                        break;
                }

                $create = date('Y-m-d', strtotime($item[0]->created_at));
                $transaction_date = explode('-', $create);
                $tgl_indo = $transaction_date[2] . ' ' . $this->bulan[(int)$transaction_date[1]] . ' ' . $transaction_date[0];

                $html .= '<div class="history_box mvp2">';
                    $html .= '<div class="history_top">';
                        $html .= '<span class="label_status ' . $item[0]->label . '">' . $item[0]->status . '</span>';
                        $html .= '<table>';
                            $html .= '<tr>';
                                $html .= '<td>Tanggal Transaksi</td>';
                                $html .= '<td>:</td>';
                                $html .= '<td>' . $tgl_indo . '</td>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td>Order ID</td>';
                                $html .= '<td>:</td>';
                                
                                if (in_array($item[0]->progress_status, [2, 3, 5])) { // ORDER DETAIL
                                    $html .= '<td><a href="' . route('web.order.order_detail', $item[0]->transaction_id) . '">' . $item[0]->transaction_id . '</td>';
                                }

                                if (in_array($item[0]->progress_status, [1, 4])) { // INVOICE DETAIL
                                    $html .= '<td><a href="' . route('web.order.invoice_detail', $item[0]->invoice_no) . '">' . $item[0]->invoice_no . '</td>';
                                }
                            $html .= '</tr>';
                            if (!is_null($item[0]->estimate_arrived_at)) {
                                $day               = date('l', strtotime($item[0]->estimate_arrived_at));
                                $tanggal           = $this->indonesian_day[$day];
                                $estimate          = date('Y-m-d', strtotime($item[0]->estimate_arrived_at));
                                $estimate_date     = explode('-', $estimate);
                                $tgl_indo_estimate = $tanggal . ', ' . $estimate_date[2] . ' ' . $this->bulan3char[(int)$estimate_date[1]] . ' ' . $estimate_date[0];

                                $html .= '<tr>';
                                    $html .= '<td>Estimasi Tiba</td>';
                                    $html .= '<td>:</td>';
                                    $html .= '<td>' . $tgl_indo_estimate . '</td>';
                                $html .= '</tr>';
                            }
                        $html .= '</table>';
                    $html .= '</div>';

                    $html .= '<div class="history_bot">';
                        $html .= '<div class="row_flex">';
                            $html .= '<div class="history_img"><img src="' . $item[0]->product_image . '"></div>';
                            $html .= '<div class="history_info">';
                                $html .= '<div class="product_name">' . $item[0]->product_name . '</div>';
                                $html .= '<div class="product_toko">' . $item[0]->seller_name . '</div>';
                                $html .= '<div class="product_total">' . $item[0]->qty . ' Pcs</div>';
                                $html .= '<div class="product_varian">' . $item[0]->variant_name . '</div>';
                                $html .= '<div class="product_price">';
                                    $html .= 'Total<span>' . $item[0]->formated_price . '</span>';
                                $html .= '</div>';
                                if ($item[0]->progress_status == 1) {
                                    $html .= '<a class="red_btn" href="' . $item[0]->payment_url . '">Bayar</a>';
                                }
                            $html .= '</div>';
                        $html .= '</div>';
                        
                        $total_items = count($item);
                        if ($total_items > 1) {
                            $html .= '<span class="other_product">+' . ($total_items - 1) . ' produk lainnya</span>';
                        }
                    $html .= '</div>';

                    if (in_array($item[0]->progress_status, [2, 3, 5])) { // ORDER DETAIL
                        $html .= '<a href="' . route('web.order.order_detail', $item[0]->transaction_id) . '" class="fake_box"></a>';
                    }

                    if (in_array($item[0]->progress_status, [1, 4])) { // INVOICE DETAIL
                        $html .= '<a href="' . route('web.order.invoice_detail', $item[0]->invoice_no) . '" class="fake_box"></a>';
                    }
                $html .= '</div>';
            }
        } else {
            $html = '';
        }

        // SUCCESSFULLY GET DATA
        $response = [
            'status'     => 'success',
            'message'    => 'Successfully get order history',
            'html'       => $html
        ];
        return response()->json($response, 200);
    }

    public function order_detail($transaction_id)
    {
        if (!Session::get('buyer')) {
            return redirect()->route('web.auth.login');
        }

        $trans_id = Helper::validate_input_text($transaction_id);
        if (!$trans_id) {
            return back()->withInput()->with('error', 'Oops, link tidak valid');
        }

        $navigation_menu    = HelperWeb::get_nav_menu();

        $buyer_id           = (int) Session::get('buyer')->id;
        $data               = order::select(
                'order.id',
                'order.invoice_id',
                'order.transaction_id',
                'order.progress_status',
                'order.price_subtotal',
                'order.price_shipping',
                'order.use_insurance_shipping',
                'order.insurance_shipping_fee',
                'order.price_total',
                'order.shipper_service_type',
                'order.shipment_address_details',
                'order.shipment_postal_code',
                'order.receiver_name',
                'order.receiver_phone',

                'seller.store_name as seller_name',

                'order_details.qty',
                'order_details.remarks',

                'product_item_variant.name as variant_name',
                'product_item.name as product_name',
                'product_item.image as product_image',
                'product_item_variant.variant_image',
                'product_item_variant.slug',
                'product_item_variant.price',

                'id_provinces.name as province_name',
                'id_cities.name as city_name',
                'id_sub_districts.name as sub_district_name',
                'id_villages.name as village_name',
            )
            ->leftJoin('seller', 'order.seller_id', 'seller.id')
            ->leftJoin('order_details', 'order.id', 'order_details.order_id')
            ->leftJoin('product_item_variant', 'order_details.product_id', 'product_item_variant.id')
            ->leftJoin('product_item', 'product_item_variant.product_item_id', 'product_item.id')
            ->leftJoin('id_provinces', 'order.shipment_province_code', 'id_provinces.code')
            ->leftJoin('id_cities', 'order.shipment_district_code', 'id_cities.full_code')
            ->leftJoin('id_sub_districts', 'order.shipment_sub_district_code', 'id_sub_districts.full_code')
            ->leftJoin('id_villages', 'order.shipment_village_code', 'id_villages.full_code')
            ->where('order.buyer_id', (int) $buyer_id)
            ->where('order.transaction_id', $trans_id)
            ->whereIn('order.progress_status', [2, 3, 5])
            ->get();

        if (empty($data[0])) {
            return back()->withInput()->with('error', 'Oops, link tidak valid');
        }

        // LABELING ORDER
        $status = '-';
        $status_label = '-';

        switch ($data[0]->progress_status) {
            case 2:
                $status = 'Sudah dibayar';
                $status_label = 'green_c';
                break;

            case 3:
                $status = 'Sudah dikirim';
                $status_label = 'blue_c';
                break;

            case 5:
                $status = 'Refunded';
                $status_label = 'orange_c';
                break;
        }

        $buyer_address  = $data[0]->shipment_address_details . ', ';
        $buyer_address .= $data[0]->village_name . ', ';
        $buyer_address .= $data[0]->sub_district_name . ', ';
        $buyer_address .= $data[0]->city_name . ', ';
        $buyer_address .= $data[0]->province_name . ', ';
        $buyer_address .= $data[0]->shipment_postal_code;

        $data_order = [
            'transaction_id'       => $data[0]->transaction_id,
            'seller_name'          => $data[0]->seller_name,
            'buyer_address'        => $buyer_address,
            'receiver_name'        => $data[0]->receiver_name,
            'receiver_phone'       => $data[0]->receiver_phone,
            'status'               => $status,
            'status_label'         => $status_label,
            'price_subtotal'       => $data[0]->price_subtotal,
            'price_shipping'       => $data[0]->price_shipping,
            'price_insurance'      => $data[0]->insurance_shipping_fee > 0 ? $data[0]->insurance_shipping_fee : 0,
            'price_total'          => $data[0]->price_total,
            'shipper_service_type' => $data[0]->shipper_service_type,
        ];

        // INVOICE DETAIL
        $data_invoice = [];
        $data_invoice_price = [];
        $data_voucher = [];

        if (!empty($data[0]->invoice_id)) { // JIKA ORDER BARU / SUDAH ADA INVOICE
            $invoice_id = $data[0]->invoice_id;
            $invoice = invoice::select(
                'invoices.id',
                'invoices.subtotal',
                'invoices.shipping_fee',
                'invoices.shipping_insurance_fee',
                'invoices.total_amount',
                'invoices.voucher_code',
                'invoices.discount_amount',

                'order.transaction_id',
                'order.price_total',

                'seller.store_name as seller_name'
            )
                ->leftJoin('order', 'order.invoice_id', 'invoices.id')
                ->leftJoin('seller', 'order.seller_id', 'seller.id')
                ->where('invoices.id', $invoice_id)
                ->get();

            // GROUPING DATA INVOICE BY SELLER
            foreach ($invoice as $item) {
                $data_invoice[$item->seller_name] = [
                    'transaction_id' => $item->transaction_id,
                    'price_total'    => $item->price_total,
                ];
            }

            // RINCIAN HARGA INVOICE
            $data_invoice_price = [
                'price_subtotal'       => $invoice[0]->subtotal,
                'price_shipping'       => $invoice[0]->shipping_fee,
                'price_insurance'      => $invoice[0]->shipping_insurance_fee,
                'price_total'          => $invoice[0]->total_amount,
            ];


            // GENERATE DATA VOUCHER
            if (!empty($invoice[0]->voucher_code) || !empty($invoice[0]->discount_amount)) {
                $data_voucher = [
                    'voucher_code'    => $invoice[0]->voucher_code,
                    'discount_amount' => $invoice[0]->discount_amount
                ];
            }
        } else { // JIKA ORDER LAMA / BLM ADA INVOICE
            // GROUPING DATA INVOICE BY SELLER
            $data_invoice[$data[0]->seller_name] = [
                'transaction_id' => $data[0]->transaction_id,
                'price_total'    => $data[0]->price_total,
            ];;

            // RINCIAN HARGA INVOICE
            $data_invoice_price = [
                'price_subtotal'       => $data[0]->price_subtotal,
                'price_shipping'       => $data[0]->price_shipping,
                'price_insurance'      => $data[0]->use_insurance_shipping == 1 ? $data[0]->insurance_shipping_fee : 0,
                'price_total'          => $data[0]->price_total,
            ];
        }

        // return view('web.order.detail', compact('navigation_menu', 'data'));
        return view('web.order.detail_neo', compact('navigation_menu', 'data', 'data_order', 'data_invoice', 'data_invoice_price', 'data_voucher'));
    }

    /**
     * hanya landing page untuk success redirect page dari Xendit
     * tidak ada logic insert/update data, hanya ada read (select)
     */
    public function order_success($transaction_id)
    {
        $user_session = Session::get('buyer');
        $now = Helper::current_datetime();

        // get order data - page ini hanya bisa diakses oleh pemilik order
        $order = order::select(
            'order_details.qty',
            'order_details.product_id',
            'order.created_at',
            'order.estimate_arrived_at',
            'order.payment_status'
        )
            ->leftJoin('order_details', 'order.id', 'order_details.order_id')
            ->where('order.transaction_id', $transaction_id)
            ->where('order.buyer_id', $user_session->id)
            ->first();
        if (!$order) {
            # ERROR
            // return abort(404);
            dd('order not found');
        }

        // page ini menggunakan metode GET sehingga perlu dibatasi agar tidak disalahgunakan
        // dibatasi dimana hanya bisa diakses sampai 10 menit setelah order dibuat
        if (time() > (strtotime($order->created_at) + (60 * 30))) {
            # ERROR
            return redirect()
                ->route('web.home');
        }

        // get product data
        $product = product_item_variant::select(
            'product_item_variant.*',
            'product_item.id AS product_item_id',
            'product_item.campaign_end',
            'product_item.qty',
            'product_item.qty_booked',
            'product_item.qty_sold',
            'product_item.image',
            'product_item.name AS product_name',
            'product_item.slug AS product_slug',
            'seller.store_name'
        )
            ->leftJoin('product_item', 'product_item_variant.product_item_id', 'product_item.id')
            ->leftJoin('seller', 'product_item.seller_id', 'seller.id')
            ->where('product_item_variant.id', $order->product_id)
            ->first();
        if (!$product) {
            # ERROR
            // return abort(404);
            dd('product not found');
        }

        // progress_status = 1=waiting for payment | 2=paid | 3=shipped | 4=canceled

        // get total order for this product (except canceled)
        $get_total_order = product_item::select(
            'product_item.id',
            DB::raw('SUM(order_details.qty) AS total_order')
        )
            ->leftJoin('product_item_variant', 'product_item.id', 'product_item_variant.product_item_id')
            ->leftJoin('order_details', 'product_item_variant.id', 'order_details.product_id')
            ->leftJoin('order', function ($join) {
                $join->on('order_details.order_id', '=', 'order.id')
                    ->where('order.progress_status', '!=', 4);
            })
            ->where('product_item.id', $product->product_item_id)
            ->groupBy('product_item.id')
            ->get();
        $total_order = $get_total_order[0]->total_order;

        // get recommended products (except this product)
        $products = product_item::select(
            'product_item.id',
            'product_item.name',
            'product_item.slug',
            'product_item.summary',
            'product_item.image',
            'product_item.price',
            'product_item.qty',
            'product_item.qty_booked',
            'product_item.qty_sold',
            'seller.store_name',
            DB::raw('IF(SUM(order_details.qty) > 0, SUM(order_details.qty), 0) AS total_order')
        )
            ->leftJoin('seller', 'product_item.seller_id', 'seller.id')
            ->leftJoin('product_item_variant', 'product_item.id', 'product_item_variant.product_item_id')
            ->leftJoin('order_details', 'product_item_variant.id', 'order_details.product_id')
            ->leftJoin('order', function ($join) {
                $join->on('order_details.order_id', '=', 'order.id')
                    ->where('order.progress_status', '!=', 4);
            })
            ->where('product_item.id', '!=', $product->product_item_id)
            ->where('product_item.published_status', 1)
            ->where('product_item.approval_status', 1)
            ->where('product_item.qty', '>', 0)
            ->where('product_item.campaign_start', '<=', $now)
            ->groupBy(
                'product_item.id',
                'product_item.name',
                'product_item.slug',
                'product_item.summary',
                'product_item.image',
                'product_item.price',
                'product_item.qty',
                'product_item.qty_booked',
                'product_item.qty_sold',
                'seller.store_name'
            )
            // ->orderBy('total_order', 'desc')
            ->limit(12)
            ->get();

        // dd($products);

        // GET DATA HARDOCED FOR PAYMENT THANKYOU
        $navigation_menu = HelperWeb::get_nav_menu();
        $company_info = HelperWeb::get_company_info();
        $social_media = HelperWeb::get_social_media();

        return view('web.order.payment_thankyou', compact('navigation_menu', 'company_info', 'social_media', 'order', 'product', 'total_order', 'products'));
    }

    /**
     * hanya landing page untuk failed redirect page dari Xendit
     * tidak ada logic insert/update data, hanya ada read (select)
     */
    public function order_failed($transaction_id)
    {
        $user_session = Session::get('buyer');

        // get order data
        $order = order::select(
            'order_details.qty',
            'order_details.product_id',
            'order.created_at'
        )
            ->leftJoin('order_details', 'order.id', 'order_details.order_id')
            ->where('order.transaction_id', $transaction_id)
            ->where('order.buyer_id', $user_session->id)
            ->where('order.payment_status', 0)
            ->first();
        if (!$order) {
            # ERROR
            // return abort(404);
            dd('order not found');
        }

        // page ini menggunakan metode GET sehingga perlu dibatasi agar tidak disalahgunakan
        // dibatasi dimana hanya bisa diakses sampai 10 menit setelah order dibuat
        if (time() > (strtotime($order->created_at) + (60 * 10))) {
            # ERROR
            // return redirect()
            //     ->route('web.home');
        }

        // get product data
        $product = product_item_variant::select(
            'product_item_variant.*',
            'product_item.campaign_end',
            'product_item.qty',
            'product_item.qty_booked',
            'product_item.qty_sold',
            'product_item.image',
            'product_item.name AS product_name',
            'seller.store_name'
        )
            ->leftJoin('product_item', 'product_item_variant.product_item_id', 'product_item.id')
            ->leftJoin('seller', 'product_item.seller_id', 'seller.id')
            ->where('product_item_variant.id', $order->product_id)
            ->first();
        if (!$product) {
            # ERROR
            // return abort(404);
            dd('product not found');
        }

        // progress_status = 1=waiting for payment | 2=paid | 3=shipped | 4=canceled

        // get total order for this product (except canceled)
        $get_total_order = order_details::select(
            DB::raw('SUM(order_details.qty) AS total_order')
        )
            ->leftJoin('order', 'order_details.order_id', 'order.id')
            ->where('order_details.product_id', $order->product_id)
            ->where('order.progress_status', '!=', 4)
            ->get();
        $total_order = $get_total_order[0]->total_order;

        // GET DATA HARDOCED FOR PAYMENT THANKYOU
        $navigation_menu = HelperWeb::get_nav_menu();
        $company_info = HelperWeb::get_company_info();
        $social_media = HelperWeb::get_social_media();

        return view('web.order.payment_failed', compact('navigation_menu', 'company_info', 'social_media', 'order', 'product', 'total_order'));
    }

    /**
     * GET DATA SHIPMENT FOR JNE
     */
    public function ajax_check_shipment_jne(Request $request)
    {
        $validation = [
            'seller_id'     => 'required',
            'destination'   => 'required',
            'weight'        => 'required'
        ];

        $message    = [
            'required'      => ':attribute' . ' Wajib diisi'
        ];

        $names      = [
            'seller_id'     => 'Data Seller',
            'destination'   => 'Alamat',
            'weight'        => 'Berat',
        ];

        $validator = Validator::make($request->all(), $validation, $message, $names);

        if ($validator->fails()) {
            $data = [
                'status'  => false,
                'message' => 'Validation Error',
                // 'data'    => $validator->errors()->messages()
                'data'    => $validator->errors()->all()
            ];

            return response()->json($data);
        }

        // VALIDATE INPUT
        $seller_id      = (int) $request->seller_id;
        $destination    = Helper::validate_input_text($request->destination);
        $weight         = Helper::validate_input_text($request->weight);

        // CHECK SELLER ADDRESS FOR GET DATA ORIGIN
        $seller = seller::select('id_cities.city_name')
            ->leftJoin('id_cities', 'seller.district_code', 'id_cities.city_code')
            ->where('seller.id', $seller_id)
            ->first();

        if (empty($seller)) {
            return response()->json([
                'status'  => false,
                'message' => 'Data tidak valid',
                'data'    => ''
            ]);
        }

        /**
         * CHECK FOR JABODETABEK
         */
        $origin_code = '';

        // JAKARTA
        if (strpos($seller->city_name, 'JAKARTA') !== false) {
            $origin_code = 'CGK10000';
        } else if (strpos($seller->city_name, 'BOGOR') !== false) {
            $origin_code = 'BOO10000';
        } else if (strpos($seller->city_name, 'DEPOK') !== false) {
            $origin_code = 'DPK10000';
        } else if (strpos($seller->city_name, 'TANGERANG') !== false) {
            $origin_code = 'TGR10000';
        } else if (strpos($seller->city_name, 'BEKASI') !== false) {
            $origin_code = 'BKI10000';
        }

        // AFTER GET CITY NAME, CHECK TO TABLE JNE_ORIGIN (THIS CONDITION WILL AFFECTED IF $ORIGIN_CODE == '')
        if ($origin_code == '') {
            $jne_origin = jne_origin::where('origin_name', 'LIKE', '%' . $seller->city_name . '%')->first();

            if (empty($jne_origin)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Maaf, area seller tidak masuk dalam area operasional JNE. Silahkan gunakan kurir lain.',
                    'data'    => ''
                ]);
            }

            $origin_code = $jne_origin->origin_code;
        }

        // CHECK DESTINATION BY ZIPCODE
        $jne_destination = jne_destination::where('zip_code', $destination)->first();
        if (empty($jne_destination)) {
            return response()->json([
                'status'  => false,
                'message' => 'Maaf, area Anda tidak masuk dalam area operasional JNE. Silahkan gunakan kurir lain.',
                'data'    => ''
            ]);
        }

        $tariff_code = $jne_destination->tariff_code;

        // LAST, CHECK TARIFF
        $check_tariff = Jne::check_tariff($origin_code, $tariff_code, $weight);
        if (!isset($check_tariff['status']) || $check_tariff['status'] == false) {
            return response()->json([
                'status'  => false,
                'message' => 'Maaf, gagal mendapatkan ongkos kirim JNE. Silahkan coba lagi.',
                'data'    => [
                    'origin' => $origin_code,
                    'destination' => $tariff_code
                ]
            ]);
        }
        if (!isset($check_tariff['data'][0])) {
            return response()->json([
                'status'  => false,
                // 'message' => 'Maaf, area operasional JNE belum menjangkau daerah seller atau Anda. Silahkan gunakan kurir lain.',
                'message' => 'Wah, kurir JNE belum bisa menjangkau daerah kamu. Tenang, kamu bisa pilih jasa pengiriman lainnya, kok!',
                'data'    => [
                    'origin' => $origin_code,
                    'destination' => $tariff_code
                ]
            ]);
        }

        return response()->json([
            'status'            => true,
            'message'           => 'Berhasil mendapatkan data ongkos kirim',
            'data'              => $check_tariff,
            'origin_code'       => $origin_code,
            'destination_code'  => $tariff_code
        ]);
    }

    /**
     * GET DATA SHIPMENT FOR ANTERAJA
     */
    public function ajax_check_shipment_anteraja(Request $request)
    {
        $validation = [
            'seller_id'     => 'required',
            'destination'   => 'required',
            'weight'        => 'required'
        ];

        $message    = [
            'required'      => ':attribute' . ' Wajib diisi'
        ];

        $names      = [
            'seller_id'     => 'Data Seller',
            'destination'   => 'Alamat',
            'weight'        => 'Berat',
        ];

        $validator = Validator::make($request->all(), $validation, $message, $names);

        if ($validator->fails()) {
            $data = [
                'status'  => false,
                'message' => 'Validation Error',
                // 'data'    => $validator->errors()->messages()
                'data'    => $validator->errors()->all()
            ];

            return response()->json($data);
        }

        // VALIDATE INPUT
        $seller_id          = (int) $request->seller_id;
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $seller_id      = Helper::validate_token($request->seller_id);
        }
        $destination_raw    = Helper::validate_input_text($request->destination);
        $weight             = Helper::validate_input_text($request->weight);

        // CHECK SELLER ADDRESS FOR GET DATA ORIGIN
        $seller = seller::select('postal_code', 'sub_district_code')->where('seller.id', $seller_id)->first();

        if (empty($seller)) {
            return response()->json([
                'status'  => false,
                'message' => 'Data tidak valid',
                'data'    => ''
            ]);
        }

        $origin = anteraja_origin::where('zip_code', $seller->postal_code)->first();
        if (!$origin) {
            # jika cek menggunakan kodepos tidak ketemu, maka pakai nama kecamatan

            // get seller's sub_district (kecamatan)
            $seller_sub_district = DB::table('id_sub_districts')
                ->select('name as sub_district_name')
                ->where('full_code', $seller->sub_district_code)
                ->first();
            if ($seller_sub_district) {
                $origin = anteraja_origin::where('district_name', $seller_sub_district->sub_district_name)->first();
            }
        }
        if (empty($origin)) {
            return response()->json([
                'status'  => false,
                'message' => 'Maaf, area seller tidak masuk dalam area operasional AnterAja. Silahkan gunakan kurir lain.',
                'data'    => ''
            ]);
        }

        $origin_code = $origin->tariff_code;

        // CHECK DESTINATION BY ZIPCODE
        $destination = anteraja_destination::where('zip_code', $destination_raw)->first();
        // dd($destination, $destination_raw, empty($destination));
        if (!$destination) {
            # jika cek menggunakan kodepos tidak ketemu, maka pakai nama kecamatan

            // get buyer's sub_district (kecamatan)
            $buyer_sub_district = DB::table('id_sub_districts')
                ->select('name as sub_district_name')
                ->where('sub_district_postal_codes', 'LIKE', '%'.$destination_raw.'%')
                ->first();
            if ($buyer_sub_district) {
                $destination = anteraja_destination::where('district_name', $buyer_sub_district->sub_district_name)->first();
            }
        }
        if (empty($destination)) {
            return response()->json([
                'status'  => false,
                'message' => 'Wah, kurir AnterAja belum bisa menjangkau daerah kamu. Tenang, kamu bisa pilih jasa pengiriman lainnya, kok!',
                'data'    => ''
            ]);
        }

        $tariff_code = $destination->tariff_code;

        // LAST, CHECK TARIFF
        $check_tariff = Anteraja::check_tariff($origin_code, $tariff_code, $weight);
        dd($check_tariff, $origin_code, $tariff_code, $weight);
        if (!isset($check_tariff['status']) || $check_tariff['status'] == false) {
            return response()->json([
                'status'  => false,
                'message' => 'Wah, kurir AnterAja belum bisa menjangkau daerah kamu. Tenang, kamu bisa pilih jasa pengiriman lainnya, kok!',
                'data'    => [
                    'origin' => $origin_code,
                    'destination' => $tariff_code
                ]
            ]);
        }
        if (!isset($check_tariff['data'][0])) {
            return response()->json([
                'status'  => false,
                // 'message' => 'Maaf, area operasional AnterAja belum menjangkau daerah seller atau Anda. Silahkan gunakan kurir lain.',
                'message' => 'Wah, kurir AnterAja belum bisa menjangkau daerah kamu. Tenang, kamu bisa pilih jasa pengiriman lainnya, kok!',
                'data'    => [
                    'origin' => $origin_code,
                    'destination' => $tariff_code
                ]
            ]);
        }

        foreach ($check_tariff['data'] as $item) {
            $explode_estimate   = explode(' ', $item->etd);
            $item->etd_thru     = $explode_estimate[0];

            if (strlen($item->etd_thru) == 3) { // CASE FOR "1-2"
                $item->etd_last = substr($item->etd_thru, -1);
            } else {
                $item->etd_last = $item->etd_thru;
            }
        }

        return response()->json([
            'status'            => true,
            'message'           => 'Berhasil mendapatkan data ongkos kirim',
            'data'              => $check_tariff,
            'origin_code'       => $origin_code,
            'destination_code'  => $tariff_code
        ]);
    }

    /**
     * CRON TO END EXPIRED ORDER AUTOMATICALLY
     */
    public function set_expired()
    {
        // GET DATA ORDER
        $affected_row   = 0;
        $limit          = 100;
        $data           = order::select(
            'order.id',
            'order.payment_status',
            'order.created_at',
            'order.expired_at',

            'order_details.qty',

            'product_item.id as product_id',
            'product_item.qty_booked',
        )
            ->leftJoin('order_details', 'order.id', 'order_details.order_id')
            ->leftJoin('product_item_variant', 'order_details.product_id', 'product_item_variant.id')
            ->leftJoin('product_item', 'product_item_variant.product_item_id', 'product_item.id')

            ->where('order.payment_status', 0) // UPDAID
            ->where('order.progress_status', 1) // WAITING FOR PAYMENT
            ->whereDate('order.expired_at', '<', date('Y-m-d H:i:s')) // EXPIRED
            ->take($limit)
            ->get();

        if (isset($data[0])) {
            DB::beginTransaction();
            try {
                foreach ($data as $item) {
                    // UPDATE ORDER PROGRESS STATUS
                    $order = order::where('id', $item->id)->update(['progress_status' => 4]);

                    // UPDATE QTY
                    $product_item = product_item::where('id', $item->product_id)->first();

                    if (!empty($product_item)) {
                        $qty_booked = $product_item->qty_booked - $item->qty;
                        $qty        = $product_item->qty + $item->qty;

                        if ($qty_booked >= 0 && $aty >= 0) {
                            $update_qty = $product_item->update([
                                'qty' => $qty,
                                'qty_booked' => $qty_booked
                            ]);
                        }
                    }

                    // GET DATA FOR SEND EMAIL
                    $email_data = order::select(
                        'order.*',

                        'order_details.qty',
                        'order_details.total_weight',

                        'product_item.name as product_name',
                        'product_item_variant.name as variant_name',
                        'product_item.image as product_image',

                        'buyer.fullname',
                        'buyer.email',

                        'id_provinces.name as province_name',
                        'id_cities.name as city_name',
                        'id_sub_districts.name as sub_district_name',
                        'id_villages.name as village_name'
                    )
                        ->leftJoin('order_details', 'order.id', 'order_details.order_id')
                        ->leftJoin('product_item_variant', 'order_details.product_id', 'product_item_variant.id')
                        ->leftJoin('product_item', 'product_item_variant.product_item_id', 'product_item.id')
                        ->leftJoin('buyer', 'order.buyer_id', 'buyer.id')
                        ->leftJoin('id_provinces', 'order.shipment_province_code', 'id_provinces.code')
                        ->leftJoin('id_cities', 'order.shipment_district_code', 'id_cities.full_code')
                        ->leftJoin('id_sub_districts', 'order.shipment_sub_district_code', 'id_sub_districts.full_code')
                        ->leftJoin('id_villages', 'order.shipment_village_code', 'id_villages.full_code')
                        ->where('order.id', (int) $item->id)
                        ->first();

                    // SEND EMAIL
                    $this_subject                       = '[' . $email_data->transaction_id . '] Order Expired';

                    $content                            = [];
                    $content['title']                   = '[' . $email_data->transaction_id . '] Order Expired';
                    $content['fullname']                = isset($email_data->fullname) ? $email_data->fullname : $email_data->email;
                    $content['total_price']             = 'Rp' . number_format($email_data->price_total, 0, ',', '.');
                    $content['transaction_id']          = $email_data->transaction_id;

                    $content['product_name']            = $email_data->product_name;
                    $content['quantity']                = $email_data->qty;
                    $content['weight']                  = $email_data->total_weight;
                    $content['variant_name']            = $email_data->variant_name;
                    $content['image']                   = $email_data->product_image;

                    $content['buyer_name']              = isset($email_data->fullname) ? $email_data->fullname : $email_data->email;
                    $content['buyer_address']           = $email_data->shipment_address_details .'<br>'. $email_data->village_name . ', ' . $email_data->sub_district_name . ', ' . $email_data->city_name . ', ' . $email_data->province_name;
                    if (isset($email_data->shipment_postal_code)) {
                        $content['buyer_address'] .= ' ' . $email_data->shipment_postal_code;
                    }
                    if ($email_data->shipment_remarks) {
                        $content['buyer_address'] .= '<br>'.$email_data->shipment_remarks;
                    }

                    $content['subtotal']                = 'Rp' . number_format($email_data->price_subtotal, 0, ',', '.');
                    $content['shipping_fee']            = 'Rp' . number_format($email_data->price_shipping, 0, ',', '.');

                    if (!is_null($email_data->insurance_shipping_fee)) {
                        $content['insurance_shipping_fee']  = 'Rp' . number_format($email_data->insurance_shipping_fee, 0, ',', '.');
                    } else {
                        $content['insurance_shipping_fee']  = 'Rp0';
                    }
                    $content['total_price']             = 'Rp' . number_format($email_data->price_total, 0, ',', '.');

                    $content['link']                    = $email_data->payment_url;
                    $company_info = HelperWeb::get_company_info();
                    $content['wa_number']   = env('COUNTRY_CODE').$company_info->wa_phone;
                    $content['wa_link']     = 'https://wa.me/62' . $company_info->wa_phone;
                    $email                              = $email_data->email;

                    Mail::send('emails.link_expired', ['data' => $content], function ($message) use ($email, $this_subject) {
                        if (env('APP_MODE', 'STAGING') == 'STAGING') {
                            $this_subject = '[STAGING] ' . $this_subject;
                        }

                        $message->subject($this_subject);
                        $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                        $message->to($email);
                    });

                    $affected_row++;
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                if (env('APP_MODE', 'STAGING') == 'STAGING') {
                    // dd($e->getMessage() . ' ' . $e->getLine());
                    return response()->json([
                        'status'        => true,
                        'message'       => 'Unsuccessfully running cron set expired order',
                        'affected_row'  => $affected_row
                    ]);
                }
            }
        }

        return response()->json([
            'status'        => true,
            'message'       => 'Successfully run cron set expired order',
            'affected_row'  => $affected_row
        ]);
    }

    public function order_list()
    {
        $data               = Session::get('buyer');
        $navigation_menu    = HelperWeb::get_nav_menu();

        return view('web.order.order_list', compact('data', 'navigation_menu'));
    }

    public function invoice_detail($invoice_id)
    {
        if (!Session::get('buyer')) {
            return redirect()->route('web.auth.login');
        }

        $invoice_id = Helper::validate_input_text($invoice_id);
        if (!$invoice_id) {
            return back()->withInput()->with('error', 'Oops, link tidak valid');
        }

        $navigation_menu    = HelperWeb::get_nav_menu();

        $buyer_id           = (int) Session::get('buyer')->id;
        $data               = invoice::select(
            'invoices.invoice_no',
            'invoices.subtotal',
            'invoices.shipping_fee',
            'invoices.shipping_insurance_fee',
            'invoices.discount_amount',
            'invoices.total_amount',
            'invoices.voucher_code',
            'invoices.payment_url',

            'order.id',
            'order.invoice_id',
            'order.progress_status',
            'order.price_subtotal',
            'order.price_shipping',
            'order.use_insurance_shipping',
            'order.insurance_shipping_fee',
            'order.price_total',
            'order.shipper_service_type',
            'order.shipment_address_details',
            'order.shipment_postal_code',
            'order.receiver_name',
            'order.receiver_phone',

            'seller.store_name as seller_name',

            'order_details.qty',
            'order_details.remarks',

            'product_item_variant.name as variant_name',
            'product_item.name as product_name',
            'product_item.image as product_image',
            'product_item_variant.variant_image',
            'product_item_variant.price',

            'id_provinces.name as province_name',
            'id_cities.name as city_name',
            'id_sub_districts.name as sub_district_name',
            'id_villages.name as village_name',
        )
            ->leftJoin('order', 'order.invoice_id', 'invoices.id')
            ->leftJoin('seller', 'order.seller_id', 'seller.id')
            ->leftJoin('order_details', 'order.id', 'order_details.order_id')
            ->leftJoin('product_item_variant', 'order_details.product_id', 'product_item_variant.id')
            ->leftJoin('product_item', 'product_item_variant.product_item_id', 'product_item.id')
            ->leftJoin('id_provinces', 'order.shipment_province_code', 'id_provinces.code')
            ->leftJoin('id_cities', 'order.shipment_district_code', 'id_cities.full_code')
            ->leftJoin('id_sub_districts', 'order.shipment_sub_district_code', 'id_sub_districts.full_code')
            ->leftJoin('id_villages', 'order.shipment_village_code', 'id_villages.full_code')
            ->where('order.buyer_id', (int) $buyer_id)
            ->where('invoices.invoice_no', $invoice_id)
            ->whereIn('order.progress_status', [1, 4])
            ->get();

        if (empty($data[0])) {
            return redirect()->route('web.order.history')->with('error', 'Oops, link tidak valid');
        }

        // LABELING INVOICE
        $status = '-';
        $status_label = '-';
        $status_payment_buttons = false;

        switch ($data[0]->progress_status) {
            case 1:
                $status = 'Menunggu pembayaran';
                $status_label = 'yellow_c';
                $status_payment_buttons = true;
                break;

            case 4:
                $status = 'Pesanan dibatalkan';
                $status_label = 'red_c';
                break;
        }

        // GROUPING DATA INVOICE
        $buyer_address  = $data[0]->shipment_address_details . ', ';
        $buyer_address .= $data[0]->village_name . ', ';
        $buyer_address .= $data[0]->sub_district_name . ', ';
        $buyer_address .= $data[0]->city_name . ', ';
        $buyer_address .= $data[0]->province_name . ', ';
        $buyer_address .= $data[0]->shipment_postal_code;

        $data_invoice = [
            'invoice_no'                => $data[0]->invoice_no,
            'buyer_address'             => $buyer_address,
            'receiver_name'             => $data[0]->receiver_name,
            'receiver_phone'            => $data[0]->receiver_phone,
            'status_payment_buttons'    => $status_payment_buttons,
            'status'                    => $status,
            'status_label'              => $status_label,
            'price_subtotal'            => $data[0]->subtotal,
            'price_shipping'            => $data[0]->shipping_fee,
            'price_insurance'           => $data[0]->shipping_insurance_fee,
            'discount_amount'           => $data[0]->discount_amount,
            'total_amount'              => $data[0]->total_amount,
            'voucher_code'              => $data[0]->voucher_code,
            'payment_url'               => $data[0]->payment_url,
        ];

        // GROUPING DATA ITEM BY SELLER
        $data_item = [];
        $data_price = [];
        foreach ($data as $item) {
            $data_tmp = [
                'product_image'       => empty($item->variant_image) ? $item->product_image : $item->variant_image,
                'product_name'        => $item->product_name . ' - ' . $item->variant_name,
                'qty'                 => $item->qty,
                'price_total'         => $item->price * $item->qty,
                'remarks'             => $item->remarks
            ];
            $data_tmp = (object) $data_tmp;

            if (isset($data_item[$item->seller_name])) {
                array_push($data_item[$item->seller_name], $data_tmp);
            } else {
                $data_item[$item->seller_name][] = $data_tmp;

                $data_price[$item->seller_name] = [
                    'price_subtotal' => $item->price_subtotal,
                    'price_shipping' => $item->price_shipping,
                    'insurance_shipping_fee' => $item->insurance_shipping_fee,
                    'price_total' => $item->price_total,
                    'shipper_service_type' => $item->shipper_service_type,
                ];
            }
        }

        // dd($data_invoice, $data_item, $data);

        return view('web.order.invoice', compact('data', 'data_invoice', 'data_item', 'data_price'));
    }
}
