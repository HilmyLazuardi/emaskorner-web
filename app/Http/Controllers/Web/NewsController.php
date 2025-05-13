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
use App\Models\nav_menu;
use App\Models\social_media;
use App\Models\news_category;
use App\Models\news;

class NewsController extends Controller
{
	public function index()
	{
        $navigation_menu = HelperWeb::get_nav_menu();
        $company_info = HelperWeb::get_company_info();
        $social_media = HelperWeb::get_social_media();

        $news = news::select('news.*')
        	->where('news.status', 1)
        	->orderBy('news.posted_at', 'desc')
        	->take(6)
        	->get();

        return view('web.news.list', compact('navigation_menu', 'company_info', 'social_media', 'news'));
	}

	public function detail($product, Request $request)
	{
        $navigation_menu = HelperWeb::get_nav_menu();
        $company_info = HelperWeb::get_company_info();
        $social_media = HelperWeb::get_social_media();

        $slug = Helper::validate_input_text($product);
        if (!$slug) {
        	return redirect()->route('web.home');
        }

        $data = news::select('news.*')
        	->where('news.slug', $product)
        	->where('news.status', 1)
        	->first();

        if (!$data) {
        	return redirect()->route('web.home');
        }

        $news = news::select('news.*')
        	->where('news.slug', '!=', $product)
        	->where('news.status', 1)
        	// ->orderBy('news.posted_at', 'desc')
        	->orderBy('news.created_at', 'desc')
        	->get();

        return view('web.news.details', compact('navigation_menu', 'company_info', 'social_media', 'slug', 'data', 'news'));
	}

	public function news_data(Request $request)
	{
		if ($request->ajax()) {
			$data = news::select('news.*')
				->where('news.status', 1)
				->orderBy('news.posted_at', 'desc')
				->skip($request->skip)
				->take(6)
				->get();

			$html = '';
			if (!$data) {
				foreach ($data as $item) {

				}
			}

			$datas[] = [
				'data' => $data,
				'html' => $html
			];

			return response()->json($datas);
		} else {
			die('Not ajax request');
		}
	}
}