<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

// Libraries
use App\Libraries\Helper;
use App\Libraries\HelperWeb;

// Models
use App\Models\page;
use App\Models\faq;
use App\Models\banner;
use App\Models\banner_popup;
use App\Models\product_category;
use App\Models\product_item;
use App\Models\product_featured;

class SiteController extends Controller
{
    public function index()
    {
        $now                = Helper::current_datetime();
        $navigation_menu    = HelperWeb::get_nav_menu();
        $company_info       = HelperWeb::get_company_info();
        $social_media       = HelperWeb::get_social_media();
        $two_month_ago      = date('Y-m-d h:i:s', (strtotime('-2 month' , strtotime (Helper::current_datetime()))));

        $product_featured   = product_featured::select(
                'product_featured.ordinal',
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
                'default_variant.price',
                'default_variant.slug',
                DB::raw('SUM(order_details.qty) as order_qty'),
                DB::raw('SUM(order_details.qty) as order_qty'),
                DB::raw('SUM(product_item_variant.qty) as variant_qty'),
                DB::raw('SUM(product_item_variant.qty_booked) as variant_qty_booked'),
                DB::raw('SUM(product_item_variant.qty_sold) as variant_qty_sold')
            )
            ->leftJoin('product_item', 'product_featured.product_id', 'product_item.id')
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
            ->where('product_item.campaign_start', '<=', $now)
            ->orderBy('product_featured.ordinal')
            ->groupBy(
                'product_featured.ordinal',
                'product_item.id',
                'product_item.name',
                'product_item.summary',
                'product_item.image',
                'product_item.global_stock',
                'product_item.qty',
                'product_item.qty_booked',
                'product_item.qty_sold',
                'product_item.campaign_end',
                'seller.store_name',
                'default_variant.slug',
                'default_variant.price'
            )
            ->get();

        $banners            = banner::where('status', 1)->orderBy('ordinal')->get();
        $banner_popup       = banner_popup::where('show_popup', 1)->where('position', 'home')->first();

        $product_best_seller = product_item::select(
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
            DB::raw('SUM(product_item_variant.qty) as variant_qty'),
            DB::raw('SUM(product_item_variant.qty_booked) as variant_qty_booked'),
            DB::raw('SUM(product_item_variant.qty_sold) as variant_qty_sold'),
            DB::raw('
                IF(product_item.global_stock = 1,
                    (product_item.qty_sold + product_item.qty_booked) / (product_item.qty_sold + product_item.qty_booked + product_item.qty) * 100,
                    (SUM(product_item_variant.qty_sold) + SUM(product_item_variant.qty_booked)) / (SUM(product_item_variant.qty_sold) + SUM(product_item_variant.qty_booked) + SUM(product_item_variant.qty)) * 100
                ) as percentage
            ')
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
        ->where('product_item.approval_status', 1)
        ->where('product_item.campaign_start', '<=', $now)
        ->where('product_item.campaign_end', '>=', $two_month_ago)
        ->orderBy('percentage', 'desc')
        ->groupBy(
            'product_item.id',
            'product_item.name',
            'product_item.summary',
            'product_item.image',
            'product_item.global_stock',
            'product_item.qty',
            'product_item.qty_booked',
            'product_item.qty_sold',
            'product_item.campaign_end',
            'seller.store_name',
            'default_variant.slug',
            'default_variant.price'
        )
        ->limit(12)
        ->get();

        return view('web.home', compact('navigation_menu', 'company_info', 'social_media', 'product_featured', 'banners', 'product_best_seller', 'banner_popup'));
    }

    public function products_new(Request $request)
    {
        $now = Helper::current_datetime();
        $perpage = 6;
        $page = 1;

        if ((int) $request->page) {
            $page = (int) $request->page;
        }

        if ($page > 4) {
            $page = 4;
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
            ->where('product_item.campaign_start', '<=', $now)
            ->orderBy('product_item.created_at', 'desc')
            ->groupBy(
                'product_item.id',
                'product_item.name',
                'product_item.summary',
                'product_item.image',
                'product_item.global_stock',
                'product_item.qty',
                'product_item.qty_booked',
                'product_item.qty_sold',
                'product_item.campaign_end',
                'seller.store_name',
                'default_variant.slug',
                'default_variant.price'
            );

        $data = $data->take($perpage)->skip($skipData)->get();

        $count_new = product_item::where('published_status', 1)
            ->where('approval_status', 1)
            ->where('product_item.campaign_start', '<=', $now)
            ->count();
        
        if ($count_new > 24) {
            $count_new = 24;
        }

        // Generate Data HTML Code
        $html = '';
        $persentase = 0;
        if (isset($data[0])) {
            $html .= '<div class="container">';
            $html .= '<h2>Yang Terbaru</h2>';
            $html .= '</div>';
            $html .= '<div class="row_clear container">';
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

                // Set Persentase
                // format persentase lama
                // $persentase = ($item->order_qty / $item->qty) * 100;

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
                    $persentase = (($item->variant_qty_sold + $item->variant_qty_booked) / ($item->variant_qty_sold + $item->variant_qty_booked + $item->variant_qty)) * 100;

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
                $persentase = ceil($persentase) . '%';

                // Set Summary
                $summary = Helper::read_more($item->summary, 150);

                $html .= '<div class="product_list_box custom_one">';
                $html .= '<div class="product_list">';
                if ($item->flag_soldout) {
                    $html .= '<div class="pl_img"><span class="flag_soldout"></span><a href="' . route('web.product.detail', $item->slug) . '"><img src="' . $image . '"></a></div>';
                } else if ($item->flag_campaign_end) {
                    $html .= '<div class="pl_img"><span class="flag_ended"></span><a href="' . route('web.product.detail', $item->slug) . '"><img src="' . $image . '"></a></div>';
                } else {
                    $html .= '<div class="pl_img"><a href="' . route('web.product.detail', $item->slug) . '"><img src="' . $image . '"></a></div>';
                }

                $html .= '<div class="pl_info">';
                $html .= '<div class="pl_stock_view">';
                $html .= '<div class="sv_track"><span class="sv_bar" style="width: ' . $persentase . ';"></span></div>';
                $html .= '<div class="sv_info">' . $persentase . ' Dipesan</div>';
                $html .= '</div>';
                $html .= '</div>';
                $html .= '</div>';
                $html .= '<div class="product_list">';
                $html .= '<div class="pl_name"><h4><a href="' . route('web.product.detail', $item->slug) . '">' . $item->name . '</a></h4></div>';
                $html .= '<div class="pl_owner">by: <a href="' . route('web.product.detail', $item->slug) . '">' . $item->seller_name . '</a></div>';
                $html .= '<div class="pl_price">' . $harga . '</div>';
                $html .= '<div class="pl_desc"><p>' . $summary . '</p></div>';
                $html .= '<a href="' . route('web.product.detail', $item->slug) . '" class="detail_btn"><span>Cek Produknya</span></a>';
                $html .= '</div>';
                $html .= '</div>';
            }
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

            $pagination .= '<div class="paging_box" ><ul>';
            if ($page > 10) {
                // Go to FIRST PAGE
                $pagination .= '<li style="cursor: pointer;" onclick="open_page(1)" class="click-able"><a class="first">Awal</a></li>';
            }
            if ($page > 1) {
                // Go to PREVIOUS PAGE
                $this_page = $page - 1;
                $pagination .= '<li style="cursor: pointer;" onclick="open_page(' . $this_page . ')" class="click-able"><a class="prev_btn"></a></li>';
            }
            if ($page > 10) {
                // generate page(-2) selection as a signal to generates selection of 10 previous page
                $this_page = $starting - 11;
                $pagination .= '<li style="cursor: pointer;" onclick="open_page(' . $this_page . ')" class="click-able"><a>' . $this_page . '</a></li><li>...</li>';
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
                $pagination .= '<li style="cursor: pointer;" onclick="open_page(' . $i . ')" class="click-able' . $selected . '"><a class="' . $selected . '">' . $i . '</a></li>';
            }

            $ceil_pages = ceil($pages / 10);
            $ceil_page = ceil($page / 10);
            if ($ceil_page < $ceil_pages) {
                $pagination .= '<li>...</li>';
            }

            if ($page < $pages) {
                // Go to NEXT PAGE
                $this_page = $page + 1;
                $pagination .= '<li style="cursor: pointer;" onclick="open_page(' . $this_page . ')" class="click-able"><a class="next_btn"></a></li>';
            }
            if ($pages > 11) {
                // Go to LAST PAGE
                $this_page = $pages;
                $pagination .= '<li style="cursor: pointer;" onclick="open_page(' . $this_page . ')" class="click-able"><a class="last">Akhir</a></li>';
            }
            $pagination .= '</ul></div>';
            $html .= '</div>';
        }

        // SUCCESSFULLY GET DATA
        $responses = [
            'status'     => 'success',
            'message'    => 'SUCCESSFULLY GET DATA',
            'html'       => $html,
            'pagination' => $pagination
        ];

        return response()->json($responses, 200);
    }

    public function page($slug, Request $request)
    {
        $slug_safe = Helper::validate_input_text($slug);

        $query = Page::where('slug', $slug_safe);

        if (!$request->preview) {
            $query->where('status', 1);
        }

        $data = $query->first();

        if (!$data) {
            return abort(404);
        }

        $navigation_menu = HelperWeb::get_nav_menu();
        $company_info = HelperWeb::get_company_info();
        $social_media = HelperWeb::get_social_media();

        return view('web.page', compact('data', 'navigation_menu', 'company_info', 'social_media'));
    }

    public function faq()
    {
        $data = faq::where('status', 1)
            ->orderBy('level')
            ->orderBy('parent_id')
            ->orderBy('ordinal')
            ->get();

        if (!isset($data[0])) {
            // NO DATA
            return;
        }

        $array_object = $data;
        $params_child = [
            'id',
            'text_1',
            'text_2',
            'level',
            'parent_id'
        ];
        $parent = 'level';
        $data_per_level = Helper::generate_parent_child_data_array($array_object, $parent, $params_child);

        $arr = [];
        foreach ($data_per_level as $level => $menulist) {
            foreach ($menulist as $menu) {
                // level_id : lvl1_id2
                $var_name = 'lvl' . $level . '_id' . $menu['id'];

                $parent_level = $menu['level'] - 1;
                $var_name_parent = 'lvl' . $parent_level . '_id' . $menu['parent_id'];

                // convert array to object
                $obj = new \stdClass();
                foreach ($menu as $key => $value) {
                    $obj->$key = $value;
                }
                // dd($menu, $obj);

                if (isset($arr[$var_name_parent])) {
                    $var_name_sub = 'level_' . $menu['level'];
                    $arr[$var_name_parent]->$var_name_sub[] = $obj;
                }
                $arr[$var_name] = $obj;
            }
        }

        $data_faq = [];
        foreach ($arr as $key => $value) {
            if (Helper::is_contains('lvl1', $key)) {
                $data_faq[] = $value;
            }
        }

        $data = $data_faq;

        $navigation_menu    = HelperWeb::get_nav_menu();
        $company_info       = HelperWeb::get_company_info();
        $social_media       = HelperWeb::get_social_media();
        return view('web.faq', compact('data', 'navigation_menu', 'company_info', 'social_media'));
    }

    public function comingsoon()
    {
        $launching_datetime = env('LAUNCHING_DATETIME');
        $comingsoon = false;
        if (!empty($launching_datetime) && date('Y-m-d H:i:s') < $launching_datetime) {
            $comingsoon = true;
        }

        if (!$comingsoon) {
            return redirect()->route('web.home');
        }

        $countdown_datetime = env('COUNTDOWN_DATETIME');
        if (!empty($countdown_datetime) && date('Y-m-d H:i:s') >= $countdown_datetime) {
            return view('web.countdown');
        }

        return view('web.comingsoon');
    }

    public function countdown()
    {
        return view('web.countdown');
    }

    public function seller_register()
    {
        return view('web.seller_register');
    }
}
