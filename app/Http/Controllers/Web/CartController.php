<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

// LIBRARIES
use App\Libraries\Helper;
use App\Libraries\HelperWeb;

// MODELS
use App\Models\buyer;
use App\Models\buyer_address;
use App\Models\buyer_phone;
use App\Models\buyer_provider;
use App\Models\wishlist;
use App\Models\shopping_cart;
use App\Models\seller;
use App\Models\product_item;
use App\Models\product_item_variant;

class CartController extends Controller
{
    public function cart()
    {
        if (!Session::get('buyer')) {
            return redirect()->route('web.auth.login')->with('error', 'Silahkan login untuk melanjutkan');
        }

        $buyer = Session::get('buyer');
        $navigation_menu = HelperWeb::get_nav_menu();

        $query = shopping_cart::select(
                'shopping_cart.buyer_id',
                'shopping_cart.qty as cart_qty',
                'shopping_cart.note',
                'product_item.id',
                'product_item.name',
                'product_item.slug',
                'product_item.seller_id',
                'product_item.image',
                'product_item.global_stock',
                'product_item.qty',
                'product_item.price',
                // 'product_item.campaign_start',
                // 'product_item.campaign_end',
                'product_item_variant.id as variant_id',
                'product_item_variant.name as variant_name',
                'product_item_variant.variant_image',
                'product_item_variant.slug as variant_slug',
                'product_item_variant.qty as variant_qty',
                'product_item_variant.weight as variant_weight',
                'product_item_variant.price as variant_price',
                'seller.store_name as seller_name'
            )
                ->leftJoin('product_item_variant', 'shopping_cart.product_item_variant_id', 'product_item_variant.id')
                ->leftJoin('product_item', 'product_item_variant.product_item_id', 'product_item.id')
                ->leftJoin('seller', 'product_item.seller_id', 'seller.id')
                ->where('shopping_cart.buyer_id', $buyer->id)
                ->where('product_item.published_status', 1)
                ->where('product_item.approval_status', 1)
                ->whereNull('product_item.deleted_at')
                ->where('product_item_variant.status', 1)
                ->whereNull('product_item_variant.deleted_at')
                ->orderBy('shopping_cart.created_at');
                
        $query  = $query->limit(10)->get();
        
        $cart_active = [];
        $cart_nonactive = [];
        $count_nonactive = 0;
        if(!empty($query))
        {
            foreach ($query as $item) {
                // Jika global stock, ambil dari product qty
                if ($item->global_stock) {
                    // Ribbon sold out dan campaign end
                    if ($item->qty < 1) {
                        $item->flag_soldout         = true;
                        $item->flag_campaign_end    = false;
                    } else if ($item->qty > 0 && (date('Y-m-d H:i:s') > $item->campaign_end)) {
                        $item->flag_soldout         = false;
                        $item->flag_campaign_end    = true;
                    } else {
                        $item->flag_soldout         = false;
                        $item->flag_campaign_end    = false;
                    }
                } else { 
                    // Jika bukan global stock, ambil total stock seluruh variant
                    // 0 sebagai default value, jadi tidak menghitung data null
                    // Ribbon sold out dan campaign end
                    if ($item->variant_qty < 1) {
                        $item->flag_soldout         = true;
                        $item->flag_campaign_end    = false;
                    } else if ($item->variant_qty > 0 && (date('Y-m-d H:i:s') > $item->campaign_end)) {
                        $item->flag_soldout         = false;
                        $item->flag_campaign_end    = true;
                    } else {
                        $item->flag_soldout         = false;
                        $item->flag_campaign_end    = false;
                    }
                }

                // CHECK PRODUCT WISHLIST
                $wishlist = wishlist::where('buyer_id', $item->buyer_id)->where('product_item_variant_id', $item->variant_id)->first();
                $item->wishlist = false;
                if ($wishlist) {
                    $item->wishlist = true;
                }

                if ($item->flag_soldout == false && $item->flag_campaign_end == false) {
                    if(!isset($cart_active[$item['seller_id']]))
                    {
                        // $cart[$item['seller_id']]['seller_id'] = $item['seller_id'];
                        $cart_active[$item['seller_id']]['seller_name'] = $item['seller_name'];
                        $cart_active[$item['seller_id']]['global_stock'] = $item['global_stock'];
                        $cart_active[$item['seller_id']]['qty'] = $item['qty'];
                        $cart_active[$item['seller_id']]['variant_id'] = $item['variant_id'];
                        $cart_active[$item['seller_id']]['variant_qty'] = $item['variant_qty'];
                        $cart_active[$item['seller_id']]['campaign_end'] = $item['campaign_end'];
                        $cart_active[$item['seller_id']]['data'][] = $item;
                    }
                    else
                    {
                        $cart_active[$item['seller_id']]['data'][] = $item;
                    }
                } else {
                    $count_nonactive += 1;
                    if(!isset($cart_nonactive[$item['seller_id']]))
                    {
                        $cart_nonactive[$item['seller_id']]['seller_name'] = $item['seller_name'];
                        $cart_nonactive[$item['seller_id']]['global_stock'] = $item['global_stock'];
                        $cart_nonactive[$item['seller_id']]['qty'] = $item['qty'];
                        $cart_nonactive[$item['seller_id']]['variant_id'] = $item['variant_id'];
                        $cart_nonactive[$item['seller_id']]['variant_qty'] = $item['variant_qty'];
                        $cart_nonactive[$item['seller_id']]['campaign_end'] = $item['campaign_end'];
                        $cart_nonactive[$item['seller_id']]['data'][] = $item;
                    }
                    else
                    {
                        $cart_nonactive[$item['seller_id']]['data'][] = $item;
                    }
                }
            }
        }
        // dd($cart_active, $cart_nonactive);

        // Section 'Wujudkan Wishlist Kamu!' start
        $now = Helper::current_datetime();
        $wishlists = wishlist::select(
            'wishlist.id as wishlist_id',

            'product_item.id',
            'product_item.name',
            'product_item.image',
            'product_item.global_stock',
            'product_item.qty',
            // 'product_item.campaign_start',
            'product_item.campaign_end',

            'product_item_variant.id as variant_id',
            'product_item_variant.name as variant_name',
            'product_item_variant.slug',
            'product_item_variant.variant_image',
            'product_item_variant.qty as variant_qty',
            'product_item_variant.price as variant_price',

            'seller.store_name as seller_name',
        )

            ->leftJoin('product_item_variant', function ($join) {
                $join->on('wishlist.product_item_variant_id', '=', 'product_item_variant.id')
                    ->where('product_item_variant.status', 1)
                    ->whereNull('product_item_variant.deleted_at');
            })
            ->leftJoin('product_item', 'product_item_variant.product_item_id', 'product_item.id')
            ->leftJoin('seller', 'product_item.seller_id', 'seller.id')

            ->where('wishlist.buyer_id', $buyer->id)

            ->where('product_item.published_status', 1)
            ->where('product_item.approval_status', 1)
            // ->where('product_item.campaign_start', '<=', $now)
            // ->where('product_item.campaign_end', '>=', $now)
            ->whereNull('product_item.deleted_at')

            ->where(function($query) {
                $query->where('product_item.global_stock', '1')
                    ->where('product_item.qty', '>', 0);
                $query->orWhere('product_item.global_stock', '0')
                    ->where('product_item_variant.qty', '>', 0);
            })

            ->orderBy('wishlist.created_at', 'desc')
            ->limit(9)
            ->get();
        // Section 'Wujudkan Wishlist Kamu!' end

        // Section 'Yang Menarik Perhatian!' start (9 produk terbaru yang available)
        $interisting_products = product_item::select(
            'product_item.id',
            'product_item.name',
            'product_item.image',
            'product_item.global_stock',
            'product_item.qty',
            // 'product_item.campaign_start',
            // 'product_item.campaign_end',

            'product_item_variant.id as variant_id',
            'product_item_variant.name as variant_name',
            'product_item_variant.slug',
            'product_item_variant.variant_image',
            'product_item_variant.qty as variant_qty',
            'product_item_variant.price as variant_price',

            'seller.store_name as seller_name',
        )
            ->leftJoin('seller', 'product_item.seller_id', 'seller.id')
            ->leftJoin('product_item_variant', function ($join) {
                $join->on('product_item.id', '=', 'product_item_variant.product_item_id')
                    ->where('product_item_variant.is_default', 1)
                    ->where('product_item_variant.status', 1)
                    ->whereNull('product_item_variant.deleted_at');
            })

            ->where('product_item.published_status', 1)
            ->where('product_item.approval_status', 1)
            
            // ->where('product_item.campaign_start', '<=', $now)
            // ->where('product_item.campaign_end', '>=', $now)

            ->where(function($query) {
                $query->where('product_item.global_stock', '1')
                    ->where('product_item.qty', '>', 0);
                $query->orWhere('product_item.global_stock', '0')
                    ->where('product_item_variant.qty', '>', 0);
            })

            ->orderBy('product_item.created_at', 'desc')
            ->limit(9)
            ->get();
        // Section 'Yang Menarik Perhatian!' end

        return view('web.buyer.cart', compact('buyer', 'navigation_menu', 'cart_active', 'cart_nonactive', 'count_nonactive', 'wishlists', 'interisting_products'));
    }

    public function add_cart(Request $request)
    {
        if (!Session::get('buyer')) {
            // FAILED GET DATA
            $response = [
                'status'    => 'failed',
                'message'   => 'Silahkan login untuk melanjutkan',
                'need_auth' => true
            ];

            if ($request->from && $request->from == 'product-detail') {
                $prev_url = Session::get('_previous');
                Session::put('from_page', $prev_url['url']);
            }

            return response()->json($response, 200);
        }

        $buyer = Session::get('buyer');

        // CONVERT TO ID
        $real_item_id = Helper::validate_token($request->variant);
            
        $sku_id = $request->sku_id;

        // CHECK ITEM PRODUCT VARIANT
        $item = product_item_variant::select(
                'product_item_variant.id as variant_id',
                'product_item_variant.sku_id',
                'product_item.id as item_id',
                'product_item.qty',
                'product_item.global_stock',
                'product_item_variant.qty as variant_qty'
            )
            ->leftJoin('product_item', 'product_item_variant.product_item_id', 'product_item.id')
            ->where('product_item_variant.id', (int) $real_item_id)
            ->where('product_item_variant.sku_id', $sku_id)
            ->first();

        if (!$item) {
            // FAILED GET DATA
            $response = [
                'status'    => 'failed',
                'message'   => 'Data varian tidak ditemukan'
            ];

            return response()->json($response, 200);
        }

        if (is_null($item->item_id)) {
            // FAILED GET DATA
            $response = [
                'status'    => 'failed',
                'message'   => 'Data produk tidak ditemukan'
            ];

            return response()->json($response, 200);
        }

        DB::beginTransaction();
        try {
            // GET LAST QTY PRODUCT CART BY BUYER & PRODUCT
            $last = shopping_cart::select('qty')
                ->where('buyer_id', (int) $buyer->id)
                ->where('product_item_variant_id', (int) $real_item_id)
                ->where('sku_id', $sku_id)
                ->orderBy('created_at', 'desc')
                ->first();

            $qty = 1;
            if ($request->qty) {
                $qty = $request->qty;
            }

            if ($last) {
                $qty = $last->qty + $qty;
            }

            $max_qty = $item->qty;
            if (!$item->global_stock) {
                $max_qty = $item->variant_qty;
            }
            
            if ($qty > $max_qty) {
                // FAILED GET DATA
                $response = [
                    'status'    => 'failed',
                    'message'   => 'Maksimum beli '.$max_qty.' barang, ya.'
                ];
    
                return response()->json($response, 200);
            }
            
            // PROCESS ADD DATA TO SHOPPING BY BUYER ID
            // shopping_cart::updateOrCreate([
            //     'buyer_id' => (int) $buyer->id,
            //     'product_item_variant_id' => (int) $real_item_id,
            //     'sku_id' => $sku_id,
            // ], [
            //     'qty' => (int) $qty
            // ]);
            if ($last) { // update
                $cart = shopping_cart::where('buyer_id', $buyer->id)
                    ->where('product_item_variant_id', $real_item_id)
                    ->where('sku_id', $sku_id)
                    ->update([
                        'qty' => (int) $qty
                    ]);
            } else { // insert
                $cart = new shopping_cart();
                $cart->buyer_id = $buyer->id;
                $cart->product_item_variant_id = $real_item_id;
                $cart->sku_id = $sku_id;
                $cart->qty = (int) $qty;
                $cart->save();
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
            'message'   => 'Berhasil menambahkan barang ke keranjang'
        ]);
    }

    public function update_cart(Request $request)
    {
        if (!Session::get('buyer')) {
            // FAILED GET DATA
            $response = [
                'status'    => 'failed',
                'message'   => 'Silahkan login untuk melanjutkan'
            ];

            return response()->json($response, 200);
        }

        $buyer = Session::get('buyer');

        // CONVERT TO ID
        $real_item_id = Helper::validate_token($request->id);
        // dd($real_item_id);
        
        // CHECK ITEM PRODUCT VARIANT
        $item = shopping_cart::select(
                'product_item_variant.id as variant_id',
                'product_item.id as item_id'
            )
            ->leftJoin('product_item_variant', 'shopping_cart.product_item_variant_id', 'product_item_variant.id')
            ->leftJoin('product_item', 'product_item_variant.product_item_id', 'product_item.id')
            ->where('shopping_cart.buyer_id', (int) $buyer->id)
            ->where('shopping_cart.product_item_variant_id', (int) $real_item_id)
            ->first();

        if (!$item) {
            // FAILED GET DATA
            $response = [
                'status'    => 'failed',
                'message'   => 'Produk item tidak ditemukan'
            ];

            return response()->json($response, 200);
        }

        if (is_null($item->item_id)) {
            // FAILED GET DATA
            $response = [
                'status'    => 'failed',
                'message'   => 'Data produk tidak ditemukan'
            ];

            return response()->json($response, 200);
        }

        DB::beginTransaction();
        try {
            // GET LAST QTY PRODUCT CART BY BUYER & PRODUCT
            $last = shopping_cart::select('qty')
                ->where('buyer_id', (int) $buyer->id)
                ->where('product_item_variant_id', (int) $real_item_id)
                ->orderBy('created_at', 'desc')
                ->first();
 
            // QUANTITY
            $qty = 1;
            if ($request->new_qty) {
                $qty = $request->new_qty;
            }

            if (!$last) {
                $qty = $last->qty + $qty;
            }
            
            $sku_id = $item->sku_id;

            // NOTES
            $notes = Helper::validate_input_text($request->notes);
            
            // PROCESS ADD DATA TO SHOPPING BY BUYER ID
            shopping_cart::where('buyer_id', (int) $buyer->id)
                ->where('product_item_variant_id', (int) $real_item_id)
                ->where('sku_id', $sku_id)
                ->update([
                    'qty'   => (int) $qty,
                    'note'  => $notes
                ]);

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
            'message'   => 'Berhasil menambahkan barang ke keranjang'
        ]);
    }

    public function delete_cart_ajax(Request $request)
    {
        if (!Session::get('buyer')) {
            return redirect()->route('web.auth.login')->with('error', 'Silahkan login untuk melanjutkan');
        }

        $buyer = Session::get('buyer');

        if(!$request->params) {
            // FAILED GET DATA
            $response = [
                'status'    => 'failed',
                'message'   => 'Data not found'
            ];

            return response()->json($response, 200);
        }

        // GET ITEMS
        $items = explode(',', $request->params);

        // CONVERT TO ID
        $real_items_id = [];
        if (isset($items[0])) {
            foreach ($items as $value) {
                if ($value != '') {
                    $real_items_id[] = Helper::validate_token($value);
                }
            }
        }

        DB::beginTransaction();
        try {
            // PROCESS DELETE FROM SHOPPING BY BUYER ID
            shopping_cart::where('shopping_cart.buyer_id', $buyer->id)->whereIn('shopping_cart.product_item_variant_id', $real_items_id)->delete();
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
            'message'   => 'Berhasil menghapus barang dari keranjang'
        ]);
    }

    public function total_cart(Request $request)
    {
        if (!Session::get('buyer')) {
            return redirect()->route('web.auth.login')->with('error', 'Silahkan login untuk melanjutkan');
        }

        $buyer = Session::get('buyer');
        
        $data = shopping_cart::select(
                'shopping_cart.buyer_id',
                'shopping_cart.qty as cart_qty',
                'product_item.id',
                'product_item.name',
                'product_item.slug',
                'product_item.seller_id',
                'product_item.image',
                'product_item.global_stock',
                'product_item.qty',
                'product_item.price',
                // 'product_item.campaign_start',
                // 'product_item.campaign_end',
                'product_item_variant.id as variant_id',
                'product_item_variant.name as variant_name',
                'product_item_variant.variant_image',
                'product_item_variant.slug as variant_slug',
                'product_item_variant.qty as variant_qty',
                'product_item_variant.weight as variant_weight',
                'product_item_variant.price as variant_price',
                'seller.store_name as seller_name'
            )
                ->leftJoin('product_item_variant', 'shopping_cart.product_item_variant_id', 'product_item_variant.id')
                ->leftJoin('product_item', 'product_item_variant.product_item_id', 'product_item.id')
                ->leftJoin('seller', 'product_item.seller_id', 'seller.id')
                ->where('shopping_cart.buyer_id', $buyer->id)
                ->where('product_item.published_status', 1)
                ->where('product_item.approval_status', 1)
                ->whereNull('product_item.deleted_at')
                ->where('product_item_variant.status', 1)
                ->whereNull('product_item_variant.deleted_at')
                ->orderBy('shopping_cart.created_at');

        $count_cart = $data->count();
        $data = $data->get();
        
        $html = '';
        if (isset($data[0])) {
            $html .= '<a href="'.route('web.buyer.cart').'" class="cart_btn"><span>'. $count_cart .'</span></a>';
            $html .= '<div class="cart_box">';
                $html .= '<div class="cart_title">';
                    $html .= '<span>Keranjang ('. $count_cart .')</span>';
                    $html .= '<a href="'.route('web.buyer.cart') .'">Lihat Sekarang</a>';
                $html .= '</div>';
                $html .= '<div class="cart_wrapper">';
                foreach ($data as $item) {
                    // ENCRIPT VARIANT ID
                    $object_id = $item->variant_id;
                    if (env('CRYPTOGRAPHY_MODE', false)) {
                        $object_id = Helper::generate_token($item->variant_id);
                    }
                                            
                    // SET FLAG PRODUCT SOLDOUT AND CAMPAIGN END
                    // Jika global stock, ambil dari product qty
                    if ($item->global_stock) {
                        // Ribbon sold out dan campaign end
                        if ($item->qty < 1) {
                            $item->flag_soldout         = true;
                            $item->flag_campaign_end    = false;
                        // } else if ($item->qty > 0 && (date('Y-m-d H:i:s') > $item->campaign_end)) {
                        //     $item->flag_soldout         = false;
                        //     $item->flag_campaign_end    = true;
                        } else {
                            $item->flag_soldout         = false;
                            $item->flag_campaign_end    = false;
                        }
                    } else { 
                        // Jika bukan global stock, ambil total stock seluruh variant
                        // 0 sebagai default value, jadi tidak menghitung data null
                        // Ribbon sold out dan campaign end
                        if ($item->variant_qty < 1) {
                            $item->flag_soldout         = true;
                            $item->flag_campaign_end    = false;
                        // } else if ($item->variant_qty > 0 && (date('Y-m-d H:i:s') > $item->campaign_end)) {
                        //     $item->flag_soldout         = false;
                        //     $item->flag_campaign_end    = true;
                        } else {
                            $item->flag_soldout         = false;
                            $item->flag_campaign_end    = false;
                        }
                    }

                    // SET NAME
                    $item->set_name = $item->name . ' - ' . $item->variant_name;

                    // SET IMAGE
                    $item->set_image = $item->image;
                    if (!is_null($item->variant_image)) {
                        $item->set_image = $item->variant_image;
                    }

                    // SET QTY
                    $item->set_qty = $item->qty;
                    if (!$item->global_stock) {
                        $item->set_qty = $item->variant_qty;
                    }

                    // SET PRICE
                    if (isset($item->variant_price)) {
                        $harga = 'Rp' . number_format($item->variant_price, 0, ',', '.');
                    }

                    if ($item->flag_soldout == false && $item->flag_campaign_end == false) {
                        $html .= '<div class="cart_list">';
                            $html .= '<div class="cart_img">';
                                $html .= '<img src="'. asset(''. $item->set_image .'') .'">';
                            $html .= '</div>';
                            $html .= '<div class="cart_desc">';
                                $html .= '<h4><a href="'. route('web.product.detail', $item->variant_slug) .'">'. $item->set_name .'</a></h4>';
                                $html .= 'Jumlah : '. $item->cart_qty .'';
                            $html .= '</div>';
                            $html .= '<div class="cart_price">';
                                $html .= ''. $harga .'';
                            $html .= '</div>';
                        $html .= '</div>';
                    }
                }
                $html .= '</div>';
            $html .= '</div>';
        } else {
            $html .= '<a href="javascript:void(0);" class="cart_btn"><span>0</span></a>';
            $html .= '<div class="cart_box">';
            $html .= '<h3>ISI YUK !</h3>';
            $html .= '<p>Keranjang Belanjamu Masih Kosong</p>';
            $html .= '</div>';
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mengambil data total cart',
            'data'      => $data,
            'html'      => $html,
            'total'     => (int) $count_cart
        ]);
    }

    // function buildTree(array $elements, $parentId = 0)
    // {
    //     $branch = array();
    
    //     foreach ($elements as $element) {
    //         if ($element['parent_id'] == $parentId) {
    //             $children = buildTree($elements, $element['id']);
    //             if ($children) {
    //                 $element['children'] = $children;
    //             }
    //             $branch[] = $element;
    //         }
    //     }
    
    //     return $branch;
    // }
    function buildTree($elements = array()) {
        $branch = array();
        if(!empty($elements))
        {
            foreach ($elements as $element) {
                if(!isset($branch[$element['seller_id']]))
                {
                    // $branch['seller_id'][]=$element['seller_id'];
                    // $branch[$element['seller_id']]=array();
                    $branch[$element['seller_id']]['qty'] = $element['qty'];
                    $branch[$element['seller_id']]['data'][] = $element;
                }
                else
                {
                    $branch[$element['seller_id']]['data'][] = $element;
                }
            }
        }
        return $branch;
    }
}