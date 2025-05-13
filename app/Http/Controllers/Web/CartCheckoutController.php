<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Web\OrderController;

// LIBRARIES
use App\Libraries\Helper;
use App\Libraries\HelperWeb;

// MODELS
use App\Models\buyer_address;
use App\Models\cart_checkout;
use App\Models\voucher;
use App\Models\shopping_cart;
use App\Models\invoice;

class CartCheckoutController extends Controller
{
    public function checkout(Request $request)
    {
        if ($request->isMethod('post')) {
            #POST

            if (!Session::get('buyer')) {
                // FAILED GET DATA
                $response = [
                    'status'    => 'failed',
                    'message'   => 'Silakan login untuk melanjutkan',
                    'need_auth' => true
                ];
    
                if ($request->from && $request->from == 'cart.checkout') {
                    $prev_url = Session::get('_previous');
                    Session::put('from_page', $prev_url['url']);
                }
    
                return response()->json($response, 200);
            }
            $buyer = Session::get('buyer');

            // DECRYPT SELECTED ID
            $product_item_variant_id = $request->product_item_variant_id;
            if (env('CRYPTOGRAPHY_MODE', false)) {
                $product_item_variant_id = [];
                foreach ($request->product_item_variant_id as $item) {
                    array_push($product_item_variant_id, Helper::validate_token($item));
                }
            }

            // GET SELECTED CART
            $data = shopping_cart::where('buyer_id', $buyer->id)
                ->whereIn('product_item_variant_id', $product_item_variant_id)
                ->orderBy('created_at')
                ->get();

            if (empty($data[0])) {
                // FAILED GET DATA
                $response = [
                    'status'    => 'failed',
                    'message'   => 'Tidak ada produk dalam keranjang Anda'
                ];
            }

            // INSERT TO CART CHECKOUT
            DB::beginTransaction();
            try {
                // CLEAR CART CHECKOUT BEFORE INSERT
                $data_checkout = cart_checkout::where('buyer_id', $buyer->id)->delete();

                // LOOPING DATA CART AND INSERT TO CART CHECKOUT
                foreach ($data as $item) {
                    $data_checkout                          = new cart_checkout();
                    $data_checkout->buyer_id                = $item->buyer_id;
                    $data_checkout->product_item_variant_id = $item->product_item_variant_id;
                    $data_checkout->qty                     = $item->qty;
                    $data_checkout->note                    = $item->note;
                    $data_checkout->save();
                }

                DB::commit();
            } catch (\Exception $ex) {
                DB::rollback();

                $error_msg = $ex->getMessage() . ' in ' . $ex->getFile() . ' at line ' . $ex->getLine();
                // Helper::error_logging($error_msg, $this->module_id);

                if (env('APP_DEBUG') == false) {
                    $error_msg = lang('Oops, something went wrong please try again later.', $this->translations);
                }

                # ERROR
                return response()->json([
                    'status'    => 'false',
                    'message'   => $error_msg
                ]);
            }

            # SUCCESS
            return response()->json([
                'status'    => 'success',
                'message'   => 'Berhasil membuat data checkout'
            ]);
        } else {
            #GET
            if (!Session::get('buyer')) {
                return redirect()->route('web.auth.login')->with('error', 'Silakan login untuk melanjutkan');
            }

            $buyer              = Session::get('buyer');
            $navigation_menu    = HelperWeb::get_nav_menu();

            // cek apakah buyer sudah input daftar alamat
            $check_input_address = buyer_address::where('user_id', $buyer->id)->count();
            if ($check_input_address == 0) {
                return redirect()
                    ->route('web.buyer.list_address')
                    ->with('error', 'Anda belum memasukkan alamat. Silakan tambahkan alamat Anda');
            }
    
            // BUYER ADDRESS
            $buyer_address      = buyer_address::select(
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
                ->where('buyer_address.user_id', (int) $buyer->id)
                ->orderBy('buyer_address.name')
                ->get();
            
            // ENCRYPT ADDRESS ID
            if (isset($buyer_address[0])) {
                foreach ($buyer_address as $address) {
                    if (env('CRYPTOGRAPHY_MODE', false)) {
                        $address->object_id = Helper::generate_token($address->id);
                    }
                }
            }

            // SELECTED ADDRESS
            $selected_address = $buyer_address->where('is_default', 1)->first();

            // VALIDASI DEFAULT ADDRESS
            if (empty($selected_address)) {
                return redirect()
                    ->route('web.buyer.list_address')
                    ->with('error', 'Anda belum memilih alamat utama. Silakan pilih alamat utama Anda.');
            }

            // BUYER ITEM CHECKOUT
            $db_data = cart_checkout::select(
                    'cart_checkout.qty as cart_qty',
                    'cart_checkout.note',
                    'product_item.name',
                    'product_item.seller_id',
                    'product_item.image',
                    'product_item.global_stock',
                    'product_item.qty',
                    'product_item.need_insurance',
                    'product_item.campaign_start',
                    'product_item.campaign_end',
                    'product_item_variant.id as variant_id',
                    'product_item_variant.name as variant_name',
                    'product_item_variant.variant_image',
                    'product_item_variant.slug as variant_slug',
                    'product_item_variant.qty as variant_qty',
                    'product_item_variant.price as variant_price',
                    'product_item_variant.weight as variant_weight',
                    'seller.store_name as seller_name'
                )
                ->leftJoin('product_item_variant', 'cart_checkout.product_item_variant_id', 'product_item_variant.id')
                ->leftJoin('product_item', 'product_item_variant.product_item_id', 'product_item.id')
                ->leftJoin('seller', 'product_item.seller_id', 'seller.id')
                ->where('cart_checkout.buyer_id', $buyer->id)
                ->where('product_item.published_status', 1)
                ->where('product_item.approval_status', 1)
                ->whereNull('product_item.deleted_at')
                ->where('product_item_variant.status', 1)
                ->whereNull('product_item_variant.deleted_at')
                ->orderBy('cart_checkout.created_at')
                ->get();

            if (empty($db_data[0])) {
                return redirect()
                    ->route('web.buyer.cart');
            }
    
            // GROUPING DATA BY SELLER
            $data = [];
            foreach ($db_data as $item) {
                $data_tmp = [
                    'seller_name'       => $item->seller_name,
                    'seller_id'         => $item->seller_id,
                    'name'              => $item->name . ' - ' . $item->variant_name,
                    'image'             => empty($item->variant_image) ? $item->image : $item->variant_image,
                    'stock'             => $item->global_stock == 1 ? $item->qty : $item->variant_qty,
                    'campaign_start'    => $item->campaign_start,
                    'campaign_end'      => $item->campaign_end,
                    'slug'              => $item->variant_slug,
                    'price'             => $item->variant_price,
                    'weight'            => $item->variant_weight,
                    'qty'               => $item->cart_qty,
                    'need_insurance'               => $item->need_insurance,
                    'note'              => $item->note
                ];
    
                if (isset($data[$item->seller_name])) {
                    array_push($data[$item->seller_name], $data_tmp);
                } else {
                    $data[$item->seller_name][] = $data_tmp;
                }
            }

            $shipment_detail = [];
            // SHIPMENT OPTION
            if (isset($db_data[0])) {
                foreach ($data as $seller => $product) {
                    $detail                 = new \stdClass();
                    $detail->seller_id      = 0;
                    $detail->seller_name    = $seller;
                    $detail->total_product  = count($product);
                    $detail->total_quantity = 0;
                    $detail->total_weight   = 0;
                    $detail->calculate_weight   = 0;

                    foreach ($product as $item) {
                        $detail->total_quantity += $item['qty'];
                        $detail->total_weight   += $item['weight'];
                        $detail->seller_id       = $item['seller_id'];
                        $detail->calculate_weight += number_format(($item['qty'] * $item['weight'] / 1000), 1);
                    }

                    $shipment_detail[]          = $detail;
                }
            }

            if (isset($shipment_detail[0])) {
                foreach ($shipment_detail as $item) {
                    // CHECK SHIPMENT ANTERAJA
                    $origin_code        = null;
                    $destination_code   = null;
                    $weight             = $item->calculate_weight;
                    $seller_id_req      = $item->seller_id;
                    if (env('CRYPTOGRAPHY_MODE', false)) {
                        $seller_id_req  = Helper::generate_token($item->seller_id);
                    }
                    
                    $order_request  = new Request([
                        'seller_id'     => $seller_id_req,
                        'destination'   => $selected_address->postal_code,
                        'weight'        => $weight,
                    ]);

                    $anteraja_detail = (new OrderController)->ajax_check_shipment_anteraja($order_request)->getData();

                    // IF SUCCESS
                    if ($anteraja_detail->status) {
                        $item->shipment         = $anteraja_detail->data;
                        $item->origin_code      = $anteraja_detail->origin_code;
                        $item->destination_code = $anteraja_detail->destination_code;
                    } else { // IF FAILED
                        return back()->with('error', $anteraja_detail->message);
                    }
                }
            }
            
            return view('web.buyer.checkout', compact('buyer_address', 'data', 'shipment_detail', 'selected_address'));
        }
    }

    public function submit_voucher(Request $request)
    {
        if (!Session::get('buyer')) {
            // FAILED GET DATA
            $response = [
                'status'    => 'failed',
                'message'   => 'Silakan login untuk melanjutkan',
                'need_auth' => true
            ];

            if ($request->from && $request->from == 'cart.checkout') {
                $prev_url = Session::get('_previous');
                Session::put('from_page', $prev_url['url']);
            }

            return response()->json($response, 200);
        }
        $buyer = Session::get('buyer');

        // GET VOUCHER
        $voucher_code = Helper::validate_input_text($request->voucher_code, TRUE);
        
        $data = voucher::where('unique_code', $voucher_code)->first();

        // VOUCHER TIDAK DITEMUKAN
        if (!$data) {
            # FAILED
            return response()->json([
                'status'    => 'failed',
                'message'   => 'Kode voucher tidak terdaftar'
            ]);
        }

        // VALIDASI STATUS
        if (!$data->is_active) {
            # FAILED
            return response()->json([
                'status'    => 'failed',
                'message'   => 'Kode voucher tidak terdaftar'
            ]);
        }

        // VALIDASI PERIOD
        $now = Helper::convert_timestamp(date('Y-m-d H:i:s'), 'Y-m-d H:i:s', env('APP_TIMEZONE', 'UTC'));
        if ($now < $data->period_begin || $now > $data->period_end) {
            # FAILED
            return response()->json([
                'status'    => 'failed',
                'message'   => 'Periode voucher tidak sesuai'
            ]);
        }

        // VALIDASI MIN. TRANSACTION
        $cart = cart_checkout::select(
            'cart_checkout.qty',
            'product_item_variant.price'
        )
            ->leftJoin('product_item_variant', 'cart_checkout.product_item_variant_id', 'product_item_variant.id')
            ->where('cart_checkout.buyer_id', $buyer->id)
            ->get();
        
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item->price * $item->qty;
        }

        if ($data->min_transaction > $subtotal) {
            # FAILED
            return response()->json([
                'status'    => 'failed',
                'message'   => 'Anda belum memenuhi syarat minimal pembelian Rp' . number_format($data->min_transaction, 0, ',', '.')
            ]);
        }

        // hitung total penggunaan voucher ini (hanya hitung dari order yg paid & menunggu pembayaran)
        $total_used_voucher = invoice::where('voucher_code', $voucher_code)->where('is_cancelled', 0)->count();

        // validasi qty voucher
        if ($total_used_voucher >= $data->qty) {
            # FAILED
            return response()->json([
                'status'    => 'failed',
                'message'   => 'Kuota voucher telah habis.'
            ]);
        }

        // hitung total penggunaan voucher ini (hanya hitung dari order yg paid & menunggu pembayaran) oleh buyer ini
        $total_used_voucher_by_this_buyer = invoice::where('voucher_code', $voucher_code)->where('is_cancelled', 0)->where('buyer_id', $buyer->id)->count();

        // validasi qty voucher per user
        if ($total_used_voucher_by_this_buyer >= $data->qty_per_user) {
            # FAILED
            return response()->json([
                'status'    => 'failed',
                'message'   => 'Oops, kuota voucher kamu telah habis digunakan.'
            ]);
        }

        // VALIDATE VOUCHER TYPE
        if ($data->voucher_type == 'shipping') {
            $shipment_fee = (int) Helper::validate_input_text($request->shipment_fee, TRUE);
            $amount_calculate_voucher = $shipment_fee;
        }

        if ($data->voucher_type == 'transaction') {
            $amount_calculate_voucher = $subtotal;
        }

        if (!isset($amount_calculate_voucher)) {
            # FAILED
            return response()->json([
                'status'    => 'failed',
                'message'   => 'Kode voucher tidak terdaftar'
            ]);
        }

        // HITUNG DISKON
        $discount = 0;
        if ($data->discount_type == 'percentage') {
            $discount = $amount_calculate_voucher * ($data->discount_value / 100);
        }

        if ($data->discount_type == 'amount') {
            $discount = $data->discount_value;
        }

        // VALIDASI NOMINAL MAKSIMAL DISKON
        if (!empty($data->discount_max_amount) && $discount > $data->discount_max_amount) {
            $discount = $data->discount_max_amount;
        }

        if ($data->voucher_type == 'shipping' && $discount > $shipment_fee) {
            $discount = $shipment_fee;
        }

        // GENERATE TEXT PERIOD BEGIN AND PERIOD END
        $period_begin_text = Helper::convert_date_to_indonesian($data->period_begin);
        $period_end_text = Helper::convert_date_to_indonesian($data->period_end);

        $period_begin_text_tmp = explode(', ', $period_begin_text);
        $period_end_text_tmp = explode(', ', $period_end_text);

        // ASSIGN TO OUTPUT DATA
        $data->subtotal = $subtotal;
        $data->discount = (string) floor($discount);
        $data->total = $subtotal - $discount;
        $data->period_begin_text = $period_begin_text_tmp[1];
        $data->period_end_text = $period_end_text_tmp[1];

        # SUCCESS
        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapat voucher',
            'data'      => $data
        ]);
    }
}