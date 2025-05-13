<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use DB;

// LIBRARIES
use App\Libraries\Helper;
use App\Libraries\HelperWeb;

// MODELS
use App\Models\product_category;
use App\Models\product_faq;
use App\Models\product_item;
use App\Models\product_item_variant;
use App\Models\wishlist;

class ProductController extends Controller
{
    /**
     * SEARCH PRODUCT
     */
    public function search(Request $request)
    {
        $now = Helper::current_datetime();
        $navigation_menu = HelperWeb::get_nav_menu();
        $company_info = HelperWeb::get_company_info();
        $social_media = HelperWeb::get_social_media();
        $two_month_ago = date('Y-m-d h:i:s', (strtotime('-2 month' , strtotime (Helper::current_datetime()))));

        $perpage = 12;
        $page = 1;
        if ((int) $request->page) {
            $page = (int) $request->page;
        }

        $skipData = ($page - 1) * $perpage;

        $data_products  = null;
        $count_products = 0;
        $keyword        = null;
        if ($request->keyword) {
            $keyword = Helper::validate_input_text($request->keyword);
            if (!$keyword) {
                return redirect()->route('web.home');
            }

            $data_products = product_item::select(
                'product_item.id',
                'product_item.name',
                'product_item.summary',
                'product_item.image',
                'product_item.global_stock',
                'product_item.qty',
                'product_item.qty_booked',
                'product_item.qty_sold',
                'seller.store_name as seller_name',
                'default_variant.slug',
                'default_variant.price',
                DB::raw('SUM(order_details.qty) as order_qty'),
                DB::raw('SUM(product_item_variant.qty) as variant_qty'),
                DB::raw('SUM(product_item_variant.qty_booked) as variant_qty_booked'),
                DB::raw('SUM(product_item_variant.qty_sold) as variant_qty_sold')
            )
                ->leftJoin('seller', 'product_item.seller_id', 'seller.id')
                ->leftJoin('product_item_variant as default_variant', function ($join) {
                    $join->on('product_item.id', '=', 'default_variant.product_item_id')
                        ->where('default_variant.is_default', 1)
                        ->where('default_variant.status', 1)
                        ->whereNull('default_variant.deleted_at');
                })
                ->leftJoin('product_item_variant', function ($join) {
                    $join->on('product_item.id', '=', 'product_item_variant.product_item_id')
                        ->where('product_item_variant.status', 1)
                        ->whereNull('product_item_variant.deleted_at');
                })
                ->leftJoin('order_details', 'product_item_variant.id', 'order_details.product_id')
                ->leftJoin('order', function ($join) {
                    $join->on('order_details.order_id', '=', 'order.id')
                        ->where('order.progress_status', '!=', 4);
                })
                ->where('product_item.published_status', 1)
                ->where('product_item.approval_status', 1);
                // ->where('product_item.campaign_start', '<=', $now)
                // ->where('product_item.campaign_end', '>=', $two_month_ago);

            if (!empty($keyword)) {
                $keyword_arr = explode(' ', $keyword);

                // logic search lama
                // $data_products->where(function ($query_where) use ($keyword) {
                //     $query_where
                //         ->where('product_item.name', 'LIKE', '%' . $keyword . '%')
                //         ->orWhere('seller.store_name', 'LIKE', '%' . $keyword . '%');
                // });

                $data_products->where(function ($query_where) use ($keyword_arr) {
                    $query_where->where(function ($subquery_where) use ($keyword_arr) {
                        foreach ($keyword_arr as $keyword_item) {
                            $subquery_where
                                ->orWhere('product_item.name', 'LIKE', '%' . $keyword_item . '%');
                        }
                    });
                    $query_where->orWhere(function ($subquery_where) use ($keyword_arr) {
                        foreach ($keyword_arr as $keyword_item) {
                            $subquery_where
                                ->orWhere('seller.store_name', 'LIKE', '%' . $keyword_item . '%');
                        }
                    });
                });
            }

            $data_products->orderBy('default_variant.created_at', 'desc')
                ->groupBy(
                    'product_item.id',
                    'product_item.name',
                    'product_item.summary',
                    'product_item.image',
                    'product_item.qty',
                    'product_item.qty_booked',
                    'product_item.qty_sold',
                    'seller.store_name',
                    'default_variant.slug',
                    'default_variant.price',
                    'default_variant.created_at'
                );

            $count_products = $data_products;
            $count_products = count($count_products->get());

            $data_products  = $data_products->take($perpage)->skip($skipData)->get();
        } else {
            return redirect()->route('web.home');
        }

        return view('web.product.search', compact('navigation_menu', 'company_info', 'social_media', 'data_products', 'count_products', 'keyword', 'page', 'perpage'));
    }

    public function filter(Request $request)
    {
        $keyword = Helper::validate_input_text($request->keyword);
        $category = Helper::validate_input_text($request->slug);
        $navigation_menu = HelperWeb::get_nav_menu();
        $company_info = HelperWeb::get_company_info();
        $social_media = HelperWeb::get_social_media();

        $perpage = 12;
        $page = 1;
        if ((int) $request->page) {
            $page = (int) $request->page;
        }

        $skipData = ($page - 1) * $perpage;

        $product_filters  = null;
        $orderby        = null;
        $price_min        = null;
        $price_max       = null;

        if ($request->orderby || ($request->price_min || $request->price_max)) {
            $orderby = Helper::validate_input_text($request->orderby);
            $price_min = $request->price_min;
            $price_max = $request->price_max;

            $product_filters = product_item::select(
                'product_item.id',
                'product_item.name',
                'product_item.summary',
                'product_item.image',
                'product_item.global_stock',
                'product_item.qty',
                'product_item.qty_booked',
                'product_item.qty_sold',
                'seller.store_name as seller_name',
                'default_variant.slug',
                'default_variant.price',
                'default_variant.qty_sold as default_variant_qty_sold',
                'default_variant.created_at',
                // DB::raw('SUM(order_details.qty) as order_qty')
                DB::raw('SUM(product_item_variant.qty) as variant_qty'),
                DB::raw('SUM(product_item_variant.qty_booked) as variant_qty_booked'),
                DB::raw('SUM(product_item_variant.qty_sold) as variant_qty_sold')
            )
                ->leftJoin('seller', 'product_item.seller_id', 'seller.id')
                ->leftJoin('product_category', 'product_item.category_id', 'product_category.id')
                ->leftJoin('product_item_variant as default_variant', function ($join) {
                    $join->on('product_item.id', '=', 'default_variant.product_item_id')
                        ->where('default_variant.is_default', 1)
                        ->where('default_variant.status', 1)
                        ->whereNull('default_variant.deleted_at');
                })
                ->leftJoin('product_item_variant', function ($join) {
                    $join->on('product_item.id', '=', 'product_item_variant.product_item_id')
                        ->where('product_item_variant.status', 1)
                        ->whereNull('product_item_variant.deleted_at');
                })
                ->where('product_item.published_status', 1)
                ->where('product_item.approval_status', 1);
            if (!empty($category)) {
                $product_filters->where('product_category.slug', $category);
            }

            if (!empty($keyword)) {
                $keyword_arr = explode(' ', $keyword);

                // logic search lama
                // $data_products->where(function ($query_where) use ($keyword) {
                //     $query_where
                //         ->where('product_item.name', 'LIKE', '%' . $keyword . '%')
                //         ->orWhere('seller.store_name', 'LIKE', '%' . $keyword . '%');
                // });

                $product_filters->where(function ($query_where) use ($keyword_arr) {
                    $query_where->where(function ($subquery_where) use ($keyword_arr) {
                        foreach ($keyword_arr as $keyword_item) {
                            $subquery_where
                                ->orWhere('product_item.name', 'LIKE', '%' . $keyword_item . '%');
                        }
                    });
                    $query_where->orWhere(function ($subquery_where) use ($keyword_arr) {
                        foreach ($keyword_arr as $keyword_item) {
                            $subquery_where
                                ->orWhere('seller.store_name', 'LIKE', '%' . $keyword_item . '%');
                        }
                    });
                });
            }

            // Filter product based on order by
            if (!empty($orderby)) {
                if ($orderby == 'highestprice') {
                    $product_filters->orderBy('default_variant.price', 'desc');
                } else if ($orderby == 'lowestprice') {
                    $product_filters->orderBy('default_variant.price', 'asc');
                } else if ($orderby == 'newestproduct') {
                    $product_filters->orderBy('default_variant.created_at', 'desc');
                } else if ($orderby == 'oldestproduct') {
                    $product_filters->orderBy('default_variant.created_at', 'asc');
                } else {
                    $product_filters->orderByRaw('(IF(product_item.global_stock = 1, product_item.qty_sold, IFNULL(SUM(product_item_variant.qty_sold), 0))) desc');
                }
            }
            // Filter product based on price min - maks
            if (!empty($price_min) && !empty($price_max)) {
                $product_filters->where('default_variant.price', '>=', $price_min)
                    ->where('default_variant.price', '<=', $price_max);
            }

            $product_filters->orderBy('product_item.created_at', 'desc')
                ->groupBy(
                    'product_item.id',
                    'product_item.name',
                    'product_item.summary',
                    'product_item.slug',
                    'product_item.image',
                    'product_item.global_stock',
                    'product_item.qty',
                    'product_item.qty_booked',
                    'product_item.qty_sold',
                    'seller.store_name',
                    'default_variant.slug',
                    'default_variant.price',
                    'default_variant.qty_sold',
                    'default_variant.created_at'
                );
            $product_filters  = $product_filters->take($perpage)->skip($skipData)->get();
        } else {
            return redirect()->route('web.home');
        }

        return view('web.product.filter', compact('navigation_menu', 'company_info', 'social_media', 'product_filters', 'page', 'perpage', 'orderby', 'price_min', 'price_max', 'category', 'keyword'));
    }

    public function search_ajax(Request $request)
    {
        $now = Helper::current_datetime();
        $perpage = $request->perpage;
        $page = $request->page;
        $keyword = Helper::validate_input_text($request->keyword);
        $category = Helper::validate_input_text($request->slug);
        $orderby = $request->orderby;
        $two_month_ago = date('Y-m-d h:i:s', (strtotime('-2 month' , strtotime (Helper::current_datetime()))));

        $price_min = 0;
        if ($request->price_min) {
            $price_min = $request->price_min;
        }
        $price_max = 0;
        if ($request->price_max) {
            $price_max = $request->price_max;
        }
        $skipData = ($page - 1) * $perpage;

        $data = product_item::select(
            'product_item.id',
            'product_item.name',
            'product_item.summary',
            'product_item.image',
            'product_item.global_stock',
            'product_item.qty',
            'product_item.qty_booked',
            'product_item.qty_sold',
            'product_item.campaign_end',
            'seller.store_name as seller_name',
            'default_variant.slug',
            'default_variant.price',
            'default_variant.qty_sold as default_variant_qty_sold',
            'default_variant.created_at',
            DB::raw('SUM(product_item_variant.qty) as variant_qty'),
            DB::raw('SUM(product_item_variant.qty_booked) as variant_qty_booked'),
            DB::raw('SUM(product_item_variant.qty_sold) as variant_qty_sold')
        )
            ->leftJoin('seller', 'product_item.seller_id', 'seller.id')
            ->leftJoin('product_item_variant as default_variant', function ($join) {
                $join->on('product_item.id', '=', 'default_variant.product_item_id')
                    ->where('default_variant.is_default', 1)
                    ->where('default_variant.status', 1)
                    ->whereNull('default_variant.deleted_at');
            })
            ->leftJoin('product_item_variant', function ($join) {
                $join->on('product_item.id', '=', 'product_item_variant.product_item_id')
                    ->where('product_item_variant.status', 1)
                    ->whereNull('product_item_variant.deleted_at');
            })
            ->where('product_item.published_status', 1)
            ->where('product_item.approval_status', 1);
            // ->where('product_item.campaign_start', '<=', $now)
            // ->where('product_item.campaign_end', '>=', $two_month_ago);

        if ($request->slug) {
            $data->leftJoin('product_category', 'product_item.category_id', 'product_category.id')
                ->where('product_category.slug', $category);
        }

        if (!empty($keyword)) {
            $keyword_arr = explode(' ', $keyword);

            // logic search lama
            // $data_products->where(function ($query_where) use ($keyword) {
            //     $query_where
            //         ->where('product_item.name', 'LIKE', '%' . $keyword . '%')
            //         ->orWhere('seller.store_name', 'LIKE', '%' . $keyword . '%');
            // });

            $data->where(function ($query_where) use ($keyword_arr) {
                $query_where->where(function ($subquery_where) use ($keyword_arr) {
                    foreach ($keyword_arr as $keyword_item) {
                        $subquery_where
                            ->orWhere('product_item.name', 'LIKE', '%' . $keyword_item . '%');
                    }
                });
                $query_where->orWhere(function ($subquery_where) use ($keyword_arr) {
                    foreach ($keyword_arr as $keyword_item) {
                        $subquery_where
                            ->orWhere('seller.store_name', 'LIKE', '%' . $keyword_item . '%');
                    }
                });
            });
        }

        if ($price_min > 0) {
            $data->where('default_variant.price', '>=', $price_min);
        }
        if ($price_max > 0) {
            $data->where('default_variant.price', '<=', $price_max);
        }

        switch ($orderby) {
            case 'highestprice':
                $data->orderBy('default_variant.price', 'desc');
                break;
            case 'lowestprice':
                $data->orderBy('default_variant.price', 'asc');
                break;
            case 'newestproduct':
                $data->orderBy('default_variant.created_at', 'desc');
                break;
            case 'oldestproduct':
                $data->orderBy('default_variant.created_at', 'asc');
                break;
            case 'bestseller':
                $data->orderByRaw('(IF(product_item.global_stock = 1, product_item.qty_sold, IFNULL(SUM(product_item_variant.qty_sold), 0))) desc');
                break;

            default:
                $data->orderBy('product_item.created_at', 'desc');
                break;
        }

        $data->groupBy(
            'product_item.id',
            'product_item.name',
            'product_item.summary',
            'product_item.slug',
            'product_item.image',
            'product_item.global_stock',
            'product_item.qty',
            'product_item.qty_booked',
            'product_item.qty_sold',
            'product_item.campaign_end',
            'seller.store_name',
            'default_variant.slug',
            'default_variant.price',
            'default_variant.qty_sold',
            'default_variant.created_at'
        );
        $count_new = $data;
        $count_new = count($count_new->get());

        if (isset($_COOKIE['devon'])) {
            dd($count_new);
        }

        $data = $data->take($perpage)->skip($skipData)->get();

        // Generate Data HTML Code
        $html = '';
        $persentase = 0;
        if (isset($data[0])) {
            foreach ($data as $key => $item) {
                // Set Image
                if (!empty($item->image)) {
                    $image = asset($item->image);
                } else {
                    $image = '';
                }

                // Set Harga
                if (isset($item->price)) {
                    $harga = 'Rp' . number_format($item->price, 0, ',', '.');
                }

                // Jika global stock, ambil dari product.qty
                if ($item->global_stock) {
                    // Hitung persentase
                    $persentase = (($item->qty_sold + $item->qty_booked) / ($item->qty_sold + $item->qty_booked + $item->qty)) * 100;

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
                } else { // Jika bukan global stock, ambil total stock seluruh variant
                    // 0 sebagai default value, jadi tidak menghitung data null
                    $variant_qty = 0;
                    if ($item->variant_qty > 0) {
                        $variant_qty = $item->variant_qty;
                    }
                    $variant_qty_booked = 0;
                    if ($item->variant_qty_booked > 0) {
                        $variant_qty_booked = $item->variant_qty_booked;
                    }
                    $variant_qty_sold = 0;
                    if ($item->variant_qty_sold > 0) {
                        $variant_qty_sold = $item->variant_qty_sold;
                    }
                    
                    // Hitung persentase
                    $persentase = (($variant_qty_sold + $variant_qty_booked) / ($variant_qty_sold + $variant_qty_booked + $variant_qty)) * 100;

                    // Ribbon sold out dan campaign end
                    if ($variant_qty < 1) {
                        $item->flag_soldout         = true;
                        $item->flag_campaign_end    = false;
                    } else if ($variant_qty > 0 && (date('Y-m-d H:i:s') > $item->campaign_end)) {
                        $item->flag_soldout         = false;
                        $item->flag_campaign_end    = true;
                    } else {
                        $item->flag_soldout         = false;
                        $item->flag_campaign_end    = false;
                    }
                }
                $persentase = ceil($persentase) . '%';

                $html .= '<div class="product_list_box custom_three">';
                $html .= '<div class="product_list">';
                if ($item->flag_soldout) {
                    $html .= '<div class="pl_img"><span class="flag_soldout"></span><a href="' . route('web.product.detail', $item->slug) . '"><img src="' . $image . '"></a></div>';
                } else if ($item->flag_campaign_end) {
                    $html .= '<div class="pl_img"><span class="flag_ended"></span><a href="' . route('web.product.detail', $item->slug) . '"><img src="' . $image . '"></a></div>';
                } else {
                    $html .= '<div class="pl_img"><a href="' . route('web.product.detail', $item->slug) . '"><img src="' . $image . '"></a></div>';
                }

                $html .= '<div class="pl_info_box">';
                $html .= '<div class="pl_info">';
                $html .= '<div class="pl_name"><h4><a href="' . route('web.product.detail', $item->slug) . '">' . $item->name . '</a></h4></div>';
                $html .= '<div class="pl_owner">by: <a href="' . route('web.product.detail', $item->slug) . '">' . $item->seller_name . '</a></div>';
                $html .= '<div class="pl_price">' . $harga . '</div>';

                $html .= '<div class="pl_diagram_box">';
                $html .= '<div id="chartContainer' . $item->id . '" style="height: 120px; width: 100%;"></div>';
                $html .= '<div class="info_stock"><span>' . $persentase . '</span> Dipesan</div>';
                $html .= '</div>';
                $html .= '<div class="pl_stock_view">';
                $html .= '<div class="sv_track"><span class="sv_bar" style="width: ' . $persentase . ';"></span></div>';
                $html .= '<div class="sv_info">' . $persentase . ' Dipesan</div>';
                $html .= '</div>';
                $html .= '</div>';
                $html .= '<a href="' . route('web.product.detail', $item->slug) . '" class="detail_btn"><span>Cek Produknya</span></a>';
                $html .= '</div>';
                $html .= '</div>';
                $html .= '</div>';
            }
        } else {
            $html .= '<center><h2 class="alert-not-found">PRODUK TIDAK ADA</h2></center>';
        }

        // Generate Pagination HTML Code
        $pagination = '';
        if (isset($data[0])) {
            $set_pagination = Helper::set_pagination($count_new, $page, $perpage);
            $pages = (int) $set_pagination->pages;
            // get length of selected page
            $length = strlen($page);
            // get the last number of selected page
            $last = substr($page, $length - 1, $length);
            if ($last > 0) {
                // get first numbers (-last) then +10
                $first = substr($page, 0, $length - 1);
                $starting = $first . '0' . +10;
            } else {
                $starting = $page;
            }

            $pagination .= '<div class="paging_box"><ul>';
            // if ($page > 10) {
            //     // Go to FIRST PAGE
            //     $pagination .= '<li onclick="open_page(1)" class="click-able"><a class="first">Awal</a></li>';
            // }
            if ($page > 1) {
                // Go to PREVIOUS PAGE
                $this_page = $page - 1;
                $pagination .= '<li onclick="open_page(' . $this_page . ')" class="click-able"><a class="prev_btn"></a></li>';
            }
            if ($page > 10) {
                // generate page(-2) selection as a signal to generates selection of 10 previous page
                $this_page = $starting - 11;
                $pagination .= '<li onclick="open_page(' . $this_page . ')" class="click-able"><a>' . $this_page . '</a></li><li>...</li>';
            }

            // generate 10 page selections
            if ($pages > 0 && $page < $pages) {
                if ($starting + 1 < $pages) {
                    $endOptPage = $starting + 1;
                } else {
                    $endOptPage = $pages;
                }
            } else {
                $page = $pages;
                $endOptPage = $pages;
            }
            for ($i = $starting - 9; $i <= $endOptPage; $i++) {
                $selected = '';
                if ($i == $page) {
                    $selected = "selected";
                }
                $pagination .= '<li onclick="open_page(' . $i . ')" class="click-able' . $selected . '"><a class="' . $selected . '">' . $i . '</a></li>';
            }

            $ceil_pages = ceil($pages / 10);
            $ceil_page = ceil($page / 10);
            if ($ceil_page < $ceil_pages) {
                $pagination .= '<li>...</li>';
            }

            if ($page < $pages) {
                // Go to NEXT PAGE
                $this_page = $page + 1;
                $pagination .= '<li onclick="open_page(' . $this_page . ')" class="click-able"><a class="next_btn"></a></li>';
            }
            // if ($pages > 11) {
            // Go to LAST PAGE
            // $this_page = $pages;
            // $pagination .= '<li onclick="open_page(' . $this_page . ')" class="click-able"><a class="last">Akhir</a></li>';
            // }
            $pagination .= '</ul></div>';
        }

        // SUCCESSFULLY GET DATA
        $responses = [
            'status'     => 'success',
            'message'    => 'SUCCESSFULLY GET DATA',
            'html'       => $html,
            'pagination' => $pagination,
            'product'    => $data,
            'total_product' => $count_new
        ];

        return response()->json($responses, 200);
    }

    /**
     * PRODUCT LIST
     */
    public function list($category)
    {
        $navigation_menu = HelperWeb::get_nav_menu();
        $company_info = HelperWeb::get_company_info();
        $social_media = HelperWeb::get_social_media();

        // CHECK CATEGORY TO CATEGORY PRODUCT
        $category = Helper::validate_input_text($category);
        if (!$category) {
            return redirect()->route('web.home')->with('error', 'Format tidak sesuai');
        }

        $data_category = product_category::where('slug', $category)->where('status', 1)->first();
        if (empty($data_category)) {
            return redirect()->route('web.home')->with('error', 'Kategori produk tidak tersedia');
        }

        $data_product = product_item::select(
            'product_item.id',
            'product_item.name',
            'product_item.summary',
            'product_item.image',
            'product_item.qty',
            'product_item.qty_booked',
            'product_item.qty_sold',
            'seller.store_name as seller_name',
            'default_variant.slug',
            'default_variant.price',
            DB::raw('SUM(order_details.qty) as order_qty')
        )
            ->leftJoin('seller', 'product_item.seller_id', 'seller.id')
            ->leftJoin('product_item_variant as default_variant', function ($join) {
                $join->on('product_item.id', '=', 'default_variant.product_item_id')
                    ->where('default_variant.is_default', 1)
                    ->where('default_variant.status', 1)
                    ->whereNull('default_variant.deleted_at');
            })
            ->leftJoin('product_item_variant', function ($join) {
                $join->on('product_item.id', '=', 'product_item_variant.product_item_id')
                    ->where('product_item_variant.status', 1)
                    ->whereNull('product_item_variant.deleted_at');
            })
            ->leftJoin('order_details', 'product_item_variant.id', 'order_details.product_id')
            ->leftJoin('order', function ($join) {
                $join->on('order_details.order_id', '=', 'order.id')
                    ->where('order.progress_status', '!=', 4);
            })
            ->where('product_item.published_status', 1)
            ->where('product_item.approval_status', 1)
            ->where('product_item.category_id', $data_category->id)
            ->orderBy('default_variant.created_at', 'desc')
            ->groupBy(
                'product_item.id',
                'product_item.name',
                'product_item.summary',
                'product_item.image',
                'product_item.qty',
                'product_item.qty_booked',
                'product_item.qty_sold',
                'seller.store_name',
                'default_variant.slug',
                'default_variant.price',
                'default_variant.created_at'
            );

        $data_product   = $data_product->get();
        $count_product  = product_item::where('published_status', 1)
            ->where('approval_status', 1)
            ->where('category_id', $data_category->id)
            ->count();

        return view('web.product.list', compact('navigation_menu', 'company_info', 'social_media', 'data_category', 'data_product', 'count_product', 'category'));
    }

    public function list_ajax(Request $request, $category)
    {
        $now = Helper::current_datetime();
        $perpage = 12;
        $page = 1;

        if ((int) $request->page) {
            $page = (int) $request->page;
        }

        // CHECK CATEGORY TO CATEGORY PRODUCT
        $category = Helper::validate_input_text($category);
        if (!$category) {
            return redirect()->route('web.home');
        }

        $data_category = product_category::where('slug', $category)->where('status', 1)->first();
        if (empty($data_category)) {
            return redirect()->route('web.home');
        }

        $skipdata = ($page - 1) * $perpage;

        $data_product = product_item::select(
            'product_item.id',
            'product_item.name',
            'product_item.summary',
            'product_item.image',
            'product_item.global_stock',
            'product_item.campaign_end',
            'product_item.qty',
            'product_item.qty_booked',
            'product_item.qty_sold',
            'seller.store_name as seller_name',
            'default_variant.slug',
            'default_variant.price',
            'default_variant.created_at',
            // 'order_details.qty as order_qty'
            DB::raw('SUM(order_details.qty) as order_qty'),
            DB::raw('SUM(product_item_variant.qty) as variant_qty'),
            DB::raw('SUM(product_item_variant.qty_booked) as variant_qty_booked'),
            DB::raw('SUM(product_item_variant.qty_sold) as variant_qty_sold')
        )
            ->leftJoin('seller', 'product_item.seller_id', 'seller.id')
            ->leftJoin('product_item_variant as default_variant', function ($join) {
                $join->on('product_item.id', '=', 'default_variant.product_item_id')
                    ->where('default_variant.is_default', 1)
                    ->where('default_variant.status', 1)
                    ->whereNull('default_variant.deleted_at');
            })
            ->leftJoin('product_item_variant', function ($join) {
                $join->on('product_item.id', '=', 'product_item_variant.product_item_id')
                    ->where('product_item_variant.status', 1)
                    ->whereNull('product_item_variant.deleted_at');
            })
            ->leftJoin('order_details', 'product_item_variant.id', 'order_details.product_id')
            ->leftJoin('order', function ($join) {
                $join->on('order_details.order_id', '=', 'order.id')
                    ->where('order.progress_status', '!=', 4);
            })
            ->where('product_item.published_status', 1)
            ->where('product_item.approval_status', 1)
            // ->where('product_item.campaign_start', '<=', $now)
            ->where('product_item.category_id', $data_category->id)
            ->orderBy('default_variant.created_at', 'desc')
            ->groupBy(
                'product_item.id',
                'product_item.name',
                'product_item.summary',
                'product_item.image',
                'product_item.global_stock',
                'product_item.qty',
                'product_item.qty_booked',
                'product_item.qty_sold',
                'seller.store_name',
                'default_variant.slug',
                'default_variant.price',
                'default_variant.created_at'
            );

        $data_product   = $data_product->take($perpage)->skip($skipdata)->get();

        $count_product = product_item::where('published_status', 1)
            ->where('approval_status', 1)
            // ->where('product_item.campaign_start', '<=', $now)
            ->where('category_id', $data_category->id)
            ->count();

        // Generate Data HTML Code
        $html = '';
        $persentase = 0;
        if (isset($data_product[0])) {
            foreach ($data_product as $key => $item) {
                // Jika global stock, ambil dari product.qty
                if ($item->global_stock) {
                    // Hitung persentase
                    $persentase = (($item->qty_sold + $item->qty_booked) / ($item->qty_sold + $item->qty_booked + $item->qty)) * 100;

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
                } else { // Jika bukan global stock, ambil total stock seluruh variant
                    // 0 sebagai default value, jadi tidak menghitung data null
                    $variant_qty = 0;
                    if ($item->variant_qty > 0) {
                        $variant_qty = $item->variant_qty;
                    }
                    $variant_qty_booked = 0;
                    if ($item->variant_qty_booked > 0) {
                        $variant_qty_booked = $item->variant_qty_booked;
                    }
                    $variant_qty_sold = 0;
                    if ($item->variant_qty_sold > 0) {
                        $variant_qty_sold = $item->variant_qty_sold;
                    }

                    // Hitung persentase
                    $persentase = (($variant_qty_sold + $variant_qty_booked) / ($variant_qty_sold + $variant_qty_booked + $variant_qty)) * 100;

                    // Ribbon sold out dan campaign end
                    if ($variant_qty < 1) {
                        $item->flag_soldout         = true;
                        $item->flag_campaign_end    = false;
                    } else if ($variant_qty > 0 && (date('Y-m-d H:i:s') > $item->campaign_end)) {
                        $item->flag_soldout         = false;
                        $item->flag_campaign_end    = true;
                    } else {
                        $item->flag_soldout         = false;
                        $item->flag_campaign_end    = false;
                    }
                }
                $persentase = ceil($persentase) . '%';

                // Set Image
                if (!empty($item->image)) {
                    $image = asset($item->image);
                } else {
                    $image = '';
                }

                // Set Harga
                if (isset($item->price)) {
                    $harga = 'Rp' . number_format($item->price, 0, ',', '.');
                }


                $html .= '<div class="product_list_box custom_three">';
                $html .= '<div class="product_list">';
                // $html .= '<div class="pl_img"><a href="'. route('web.product.detail', $item->slug) .'"><img src="'. $image .'"></a></div>';

                if ($item->flag_soldout) {
                    // $html .= '';
                    $html .= '<div class="pl_img"><span class="flag_soldout"></span><a href="' . route('web.product.detail', $item->slug) . '"><img src="' . $image . '"></a></div>';
                } elseif ($item->flag_campaign_end) {
                    // $html .= '';
                    $html .= '<div class="pl_img"><span class="flag_ended"></span><a href="' . route('web.product.detail', $item->slug) . '"><img src="' . $image . '"></a></div>';
                } else {
                    $html .= '<div class="pl_img"></span><a href="' . route('web.product.detail', $item->slug) . '"><img src="' . $image . '"></a></div>';
                }

                $html .= '<div class="pl_info_box">';
                $html .= '<div class="pl_info">';
                $html .= '<div class="pl_name"><h4><a href="' . route('web.product.detail', $item->slug) . '">' . $item->name . '</a></h4></div>';
                $html .= '<div class="pl_owner">by: <a href="' . route('web.product.detail', $item->slug) . '">' . $item->seller_name . '</a></div>';
                $html .= '<div class="pl_price">' . $harga . '</div>';

                $html .= '<div class="pl_diagram_box">';
                $html .= '<div id="chartContainer' . $item->id . '" style="height: 120px; width: 100%;"></div>';
                $html .= '<div class="info_stock"><span>' . $persentase . '</span> Dipesan</div>';
                $html .= '</div>';
                $html .= '<div class="pl_stock_view">';
                $html .= '<div class="sv_track"><span class="sv_bar" style="width: ' . $persentase . ';"></span></div>';
                $html .= '<div class="sv_info">' . $persentase . ' Dipesan</div>';
                $html .= '</div>';
                $html .= '</div>';
                $html .= '<a href="' . route('web.product.detail', $item->slug) . '" class="detail_btn"><span>Cek Produknya</span></a>';
                $html .= '</div>';
                $html .= '</div>';
                $html .= '</div>';
            }
        } else {
            $html .= '<center><h2 class="alert-not-found">PRODUK TIDAK ADA</h2></center>';
        }

        // Generate Pagination HTML Code
        $pagination = '';
        if (isset($data_product[0])) {
            $set_pagination = Helper::set_pagination($count_product, $page, $perpage);
            $pages = (int) $set_pagination->pages;
            // get length of selected page
            $length = strlen($page);
            // get the last number of selected page
            $last = substr($page, $length - 1, $length);
            if ($last > 0) {
                // get first numbers (-last) then +10
                $first = substr($page, 0, $length - 1);
                $starting = $first . '0' . +10;
            } else {
                $starting = $page;
            }

            $pagination .= '<div class="paging_box"><ul>';
            // if ($page > 10) {
            //     // Go to FIRST PAGE
            //     $pagination .= '<li onclick="open_page(1)" class="click-able"><a class="first">Awal</a></li>';
            // }
            if ($page > 1) {
                // Go to PREVIOUS PAGE
                $this_page = $page - 1;
                $pagination .= '<li onclick="open_page(' . $this_page . ')" class="click-able"><a class="prev_btn"></a></li>';
            }
            if ($page > 10) {
                // generate page(-2) selection as a signal to generates selection of 10 previous page
                $this_page = $starting - 11;
                $pagination .= '<li onclick="open_page(' . $this_page . ')" class="click-able"><a>' . $this_page . '</a></li><li>...</li>';
            }

            // generate 10 page selections
            if ($pages > 0 && $page < $pages) {
                if ($starting + 1 < $pages) {
                    $endOptPage = $starting + 1;
                } else {
                    $endOptPage = $pages;
                }
            } else {
                $page = $pages;
                $endOptPage = $pages;
            }
            for ($i = $starting - 9; $i <= $endOptPage; $i++) {
                $selected = '';
                if ($i == $page) {
                    $selected = "selected";
                }
                $pagination .= '<li onclick="open_page(' . $i . ')" class="click-able' . $selected . '"><a class="' . $selected . '">' . $i . '</a></li>';
            }

            $ceil_pages = ceil($pages / 10);
            $ceil_page = ceil($page / 10);
            if ($ceil_page < $ceil_pages) {
                $pagination .= '<li>...</li>';
            }

            if ($page < $pages) {
                // Go to NEXT PAGE
                $this_page = $page + 1;
                $pagination .= '<li onclick="open_page(' . $this_page . ')" class="click-able"><a class="next_btn"></a></li>';
            }
            // if ($pages > 11) {
            // Go to LAST PAGE
            // $this_page = $pages;
            // $pagination .= '<li onclick="open_page(' . $this_page . ')" class="click-able"><a class="last">Akhir</a></li>';
            // }
            $pagination .= '</ul></div>';
        }

        // SUCCESSFULLY GET DATA
        $response = [
            'status'     => 'success',
            'message'    => 'SUCCESSFULLY GET DATA',
            'html'       => $html,
            'pagination' => $pagination,
            'product'    => $data_product,
            'total_product' => $count_product
        ];
        return response()->json($response, 200);
    }

    /**
     * PRODUCT DETAIL
     */
    public function detail($product, Request $request)
    {
        $now                = Helper::current_datetime();
        $navigation_menu    = HelperWeb::get_nav_menu();
        $company_info       = HelperWeb::get_company_info();
        $social_media       = HelperWeb::get_social_media();
        $slug               = Helper::validate_input_text($product);

        if (!$slug) {
            return redirect()->route('web.home');
        }

        $data = product_item::select(
                'product_item.id',
                'product_item.seller_id',
                'product_item.name',
                'product_item.slug as product_item_slug',
                'product_item.summary',
                'product_item.image',
                'product_item.image_2',
                'product_item.image_3',
                'product_item.image_4',
                'product_item.image_5',
                'product_item.details',
                'product_item.global_stock',
                'product_item.qty',
                'product_item.qty_booked',
                'product_item.qty_sold',
                'product_item.variant_1',
                'product_item.variant_1_list',
                'product_item.variant_2',
                'product_item.variant_2_list',
                // 'product_item.campaign_start',
                // 'product_item.campaign_end',
                'product_item.featured',
                'product_item.approval_status',
                'product_item.published_status',
                'product_item.created_at',
                'seller.store_name as seller_store_name',
                'seller.avatar as seller_avatar',
                'seller.description as seller_desc',
                'id_cities.name as seller_city',
                'product_item_variant.slug',
                'product_item_variant.price',
                'product_item_variant.name as variant_name',
                'product_item_variant.variant_1 as variant_1_name',
                'product_item_variant.variant_2 as variant_2_name',
                'product_item_variant.sku_id as variant_sku_id',
                DB::raw('SUM(order_details.qty) as order_qty'),
                'product_item_variant.qty as variant_qty',
                'product_item_variant.qty_booked as variant_qty_booked',
                'product_item_variant.qty_sold as variant_qty_sold'
            )
            ->leftJoin('seller', 'product_item.seller_id', 'seller.id')
            ->leftJoin('product_item_variant', 'product_item.id', 'product_item_variant.product_item_id')
            ->leftJoin('order_details', 'product_item_variant.id', 'order_details.product_id')
            ->leftJoin('order', function ($join) {
                $join->on('order_details.order_id', '=', 'order.id')
                    ->where('order.progress_status', '!=', 4);
            })
            ->leftJoin('id_cities', 'seller.district_code', 'id_cities.full_code')
            ->where('product_item_variant.slug', $slug)
            ->whereNull('product_item_variant.deleted_at');

        $preview = true;
        if (!$request->preview) {
            $preview    = false;
            // $data       = $data->where('product_item.approval_status', 1)->where('product_item.published_status', 1)->where('product_item.campaign_start', '<=', $now);
            $data       = $data->where('product_item.approval_status', 1)->where('product_item.published_status', 1);
        }

        $data = $data->where('product_item_variant.slug', $slug)
            ->groupBy(
                'product_item.id',
                'product_item.seller_id',
                'product_item.name',
                'product_item.slug',
                'product_item.summary',
                'product_item.image',
                'product_item.image_3',
                'product_item.image_4',
                'product_item.image_5',
                'product_item.details',
                'product_item.global_stock',
                'product_item.qty',
                'product_item.qty_booked',
                'product_item.qty_sold',
                'product_item.variant_1',
                'product_item.variant_1_list',
                'product_item.variant_2',
                'product_item.variant_2_list',
                // 'product_item.campaign_start',
                // 'product_item.campaign_end',
                'product_item.featured',
                'product_item.approval_status',
                'product_item.published_status',
                'product_item.created_at',
                'seller.store_name',
                'seller.avatar',
                'seller.description',
                'id_cities.name',
                'product_item_variant.slug',
                'product_item_variant.price',
                'product_item_variant.name',
                'product_item_variant.variant_1',
                'product_item_variant.variant_2',
                'product_item_variant.sku_id',
                'product_item_variant.qty',
                'product_item_variant.qty_booked',
                'product_item_variant.qty_sold'
            )
            ->first();

        if (empty($data)) { 
            return redirect()->route('web.home');
        }

        if ($request->preview) {
            // IF PREVIEW, CHECK PRODUCT ITEM DETAILS
            if (is_null($data->details) || $data->details == '') {
                return redirect()->route('web.home');
            }
        }

        // SISA CAMPAIGN
        $data->campaign_days_left = Helper::get_diff_dates($data->campaign_end);

        if (date('Y-m-d H:i:s') > $data->campaign_end) {
            $data->campaign_days_left = 0;
        }

        // Jika global stock, ambil dari product.qty
        $total_qty = 0;
        if ($data->global_stock) {
            // SET FLAG PRODUCT SOLDOUT AND CAMPAIGN END
            if ($data->qty < 1) {
                $data->flag_soldout         = true;
                $data->flag_campaign_end    = false;
            } else if ($data->qty > 0 && (date('Y-m-d H:i:s') > $data->campaign_end)) {
                $data->flag_soldout         = false;
                $data->flag_campaign_end    = true;
            } else {
                $data->flag_soldout         = false;
                $data->flag_campaign_end    = false;
            }

            // Qty tersedia
            $data->qty_available = $data->qty;  // for 1 variant only
            $data->total_qty = $data->qty; // for all variant
        } else { // Jika bukan global stock, ambil total stock seluruh variant
            // SET FLAG PRODUCT SOLDOUT AND CAMPAIGN END
            $variant_qty = 0;
            if ($data->variant_qty > 0) {
                $variant_qty = $data->variant_qty;
            }
            $variant_qty_booked = 0;
            if ($data->variant_qty_booked > 0) {
                $variant_qty_booked = $data->variant_qty_booked;
            }
            $variant_qty_sold = 0;
            if ($data->variant_qty_sold > 0) {
                $variant_qty_sold = $data->variant_qty_sold;
            }

            if ($variant_qty < 1) {
                $data->flag_soldout         = true;
                $data->flag_campaign_end    = false;
            } else if ($variant_qty > 0 && (date('Y-m-d H:i:s') > $data->campaign_end)) {
                $data->flag_soldout         = false;
                $data->flag_campaign_end    = true;
            } else {
                $data->flag_soldout         = false;
                $data->flag_campaign_end    = false;
            }

            $data->qty_available = $variant_qty;
            $total_qty = 0;
            $variants = product_item_variant::where('product_item_id', $data->id)
                ->where('status', 1)
                ->orderBy('is_default', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($variants as $variant) {
                $total_qty += $variant->qty;
            }
            $data->total_qty = $total_qty;
        }

        // VARIANT
        $variants = product_item_variant::where('product_item_id', $data->id)
            ->where('status', 1)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        if (empty($variants[0])) { return redirect()->route('web.home'); }

        // ENCRYPT ALL VARIANT ID
        foreach ($variants as $variant) {
            if (!$data->global_stock) {
                $total_qty += $variant->qty;
            }
            $variant->raw_id = $variant->id;
            if (env('CRYPTOGRAPHY_MODE', false)) {
                $variant->id = Helper::generate_token($variant->id);
            }

            // DEFAULT VARIANT
            if ($variant->is_default == 1) {
                $data->default_variant_id = $variant->id;
                if (env('CRYPTOGRAPHY_MODE', false)) {
                    $data->default_variant_id = Helper::generate_token($variant->id);
                }
            }
        }
        $data->total_qty = $total_qty;

        // NORMALIZE VARIANT LIST DATA (GENERATE SLUG, VARIANT LIST, VARIANT NAME)
        $variant_1_slug = [];
        $variant_1_slug_tmp = json_decode($data->variant_1_list);
        foreach ($variant_1_slug_tmp as $item) {
            array_push($variant_1_slug, Helper::generate_slug($item));
        }
        $data->variant_1_slug = $variant_1_slug;
        $data->variant_1_list = json_decode($data->variant_1_list);
        $data->variant_1_name = Helper::generate_slug($data->variant_1_name);
        
        if (!empty($data->variant_2_list)) {
            $variant_2_slug = [];
            $variant_2_slug_tmp = json_decode($data->variant_2_list);
            foreach ($variant_2_slug_tmp as $item) {
                array_push($variant_2_slug, Helper::generate_slug($item));
            }
            $data->variant_2_slug = $variant_2_slug;
            $data->variant_2_list = json_decode($data->variant_2_list);
            $data->variant_2_name = Helper::generate_slug($data->variant_2_name);
            $data->variant_2_exist = true;
        } else {
            $data->variant_2_exist = false;
        }

        if ($request->preview) {
            // IF PREVIEW, CHECK PRODUCT VARIANT
            if (count($variants) < 1) {
                return redirect()->route('web.home');
            }
        }

        $product_faq = product_faq::where('product_item_id', $data->id)->orderBy('ordinal', 'asc')->get();

        // cek apakah sedang comingsoon
        $launching_datetime = env('LAUNCHING_DATETIME');
        $comingsoon = false;
        if (!empty($launching_datetime) && date('Y-m-d H:i:s') < $launching_datetime) {
            $comingsoon = true;
        }

        if ($comingsoon && !$request->preview && !Session::has(env('SESSION_ADMIN_NAME', 'sysadmin'))) {
            // tampilkan landing page coming soon
            return redirect()->route('web.comingsoon');
        }

        // ENCRYPT ID DATA
        $object_id = $data->id;
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $object_id = Helper::generate_token($data->id);
        }
        
        return view('web.product.detail', compact('navigation_menu', 'company_info', 'social_media', 'slug', 'data', 'variants', 'product_faq', 'preview', 'object_id'));
    }

    public function detail_ajax(Request $request)
    {
        $variant_slug = Helper::validate_input_text($request->variant_slug);
        $variant_slug = strtolower($variant_slug);

        $query = product_item::select(
            'product_item.name',
            'product_item.global_stock',
            'product_item.campaign_end',
            'product_item.qty',
            'product_item.qty_booked',
            'product_item.qty_sold',
            'product_item_variant.id',
            'product_item_variant.product_item_id',
            'product_item_variant.name as variant_name',
            'product_item_variant.price',
            'product_item_variant.slug',
            'product_item_variant.sku_id as variant_sku_id',
            'product_item_variant.status',
            DB::raw('SUM(product_item_variant.qty) as variant_qty'),
            DB::raw('SUM(product_item_variant.qty_booked) as variant_qty_booked'),
            DB::raw('SUM(product_item_variant.qty_sold) as variant_qty_sold')
        )
        ->leftJoin('product_item_variant', 'product_item.id', 'product_item_variant.product_item_id')
        ->where('product_item_variant.slug', $variant_slug)
        ->whereNull('product_item_variant.deleted_at');

        $data = $query->groupBy(
                'product_item.name',
                'product_item.global_stock',
                'product_item.campaign_end',
                'product_item.qty',
                'product_item.qty_booked',
                'product_item.qty_sold',
                'product_item_variant.id',
                'product_item_variant.product_item_id',
                'product_item_variant.name',
                'product_item_variant.price',
                'product_item_variant.slug',
                'product_item_variant.sku_id',
                'product_item_variant.status'
            )
            ->first();

        // FAILED. DATA EMPTY
        if (empty($data)) {
            $responses = [
                'status'     => 'failed',
                // 'message'    => 'DATA EMPTY',
                'message'    => $request->variant_slug,
                'data'       => null
            ];

            return response()->json($responses, 200);
        }

        // CEK WISHLIST
        $data->is_wishlist = false;
        if (Session::get('buyer')) {
            $buyer = Session::get('buyer');
            $wishlist = wishlist::where('buyer_id', (int) $buyer->id)
                ->where('product_item_variant_id', (int) $data->id)
                ->first();
            
            if ($wishlist) {
                $data->is_wishlist = true;
            }
        }

        // ENCRYPT ID
        $data->object_id = $data->id;
        if (env('CRYPTOGRAPHY_MODE', false)) {
            $data->object_id = Helper::generate_token($data->id);
        }

        // SISA CAMPAIGN
        $data->campaign_days_left = Helper::get_diff_dates($data->campaign_end);

        if (date('Y-m-d H:i:s') > $data->campaign_end) {
            $data->campaign_days_left = 0;
        }

        // Jika global stock, ambil dari product.qty
        if ($data->global_stock) {
            // Hitung total qty
            $total_qty = $data->qty_sold + $data->qty_booked + $data->qty;

            // Hitung total order
            $total_order = $data->qty_sold + $data->qty_booked;

            // Hitung persentase
            $persentase = ($total_order / $total_qty) * 100;

            // Qty tersedia
            $data->qty_available = $data->qty;

            // All qty
            $data->all_qty = $data->qty;

            // Ribbon sold out dan campaign end
            if ($data->qty < 1) {
                $data->flag_soldout         = true;
                $data->flag_campaign_end    = false;
            } else if ($data->qty > 0 && (date('Y-m-d H:i:s') > $data->campaign_end)) {
                $data->flag_soldout         = false;
                $data->flag_campaign_end    = true;
            } else {
                $data->flag_soldout         = false;
                $data->flag_campaign_end    = false;
            }
        } else { // Jika bukan global stock, ambil total stock seluruh variant
            // 0 sebagai default value, jadi tidak menghitung data null
            $variant_qty = 0;
            if ($data->variant_qty > 0) {
                $variant_qty = $data->variant_qty;
            }
            $variant_qty_booked = 0;
            if ($data->variant_qty_booked > 0) {
                $variant_qty_booked = $data->variant_qty_booked;
            }
            $variant_qty_sold = 0;
            if ($data->variant_qty_sold > 0) {
                $variant_qty_sold = $data->variant_qty_sold;
            }

            // Hitung total qty
            $total_qty = $variant_qty_sold + $variant_qty_booked + $variant_qty;

            // Hitung total order
            $total_order = $variant_qty_sold + $variant_qty_booked;

            // Hitung persentase
            $persentase = ($total_order / $total_qty) * 100;

            // Qty variant tersedia
            $data->qty_available = $variant_qty;

            // All qty
            $all_qty = 0;
            $variants = product_item_variant::where('product_item_id', $data->product_item_id)
                ->where('status', 1)
                ->orderBy('is_default', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($variants as $variant) {
                $all_qty += $variant->qty;
            }
            $data->all_qty = $all_qty;
            
            // Ribbon sold out dan campaign end
            if ($variant_qty < 1) {
                $data->flag_soldout         = true;
                $data->flag_campaign_end    = false;
            } else if ($variant_qty > 0 && (date('Y-m-d H:i:s') > $data->campaign_end)) {
                $data->flag_soldout         = false;
                $data->flag_campaign_end    = true;
            } else {
                $data->flag_soldout         = false;
                $data->flag_campaign_end    = false;
            }
        }

        // ASSIGN DATA
        $data->total_qty = $total_qty;
        $data->total_order = $total_order;
        $data->persentase = ceil($persentase);

        // SUCCESSFULLY GET DATA
        $responses = [
            'status'     => 'success',
            'message'    => 'SUCCESSFULLY GET DATA',
            'data'       => $data
        ];

        return response()->json($responses, 200);
    }
}
