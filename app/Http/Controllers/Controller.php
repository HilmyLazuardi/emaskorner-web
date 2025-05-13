<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use DB;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;

// Models
use App\Models\country;
use App\Models\language;
use App\Models\phrase;
use App\Models\config;
use App\Models\product_category;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    // variable to store the available countries
    public $sio_countries;
    // variable to store the available languages
    public $sio_languages;
    // variable to store translations
    public $translations;
    // variable to store global configuration
    public $global_config;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {

            // set IP Address
            $ip_address = $request->ip();
            Session::put('user_ip_address', $ip_address);

            // get the language used
            $language_used = Session::get('language_used');
            if (empty($language_used)) {
                // if not set, then use the default data from .env
                $language_used = env('DEFAULT_LANGUAGE', 'EN');
                Session::put('language_used', $language_used);
            }

            // get the country used
            $country_used = Session::get('country_used');
            if (empty($country_used)) {
                // if not set, then use the default data from .env
                $country_used = env('DEFAULT_COUNTRY', 'US');
                Session::put('country_used', $country_used);
            }

            // set translations data
            $countries = [];
            $languages = [];
            $translations = [];
            if (env('APP_BACKEND', 'MODEL') != 'API' && env('MULTILANG_MODULE', false)) {
                $countries = Session::get('sio_countries');
                if (empty($countries)) {
                    $get_countries = country::where('status', 1)->orderBy('country_alias')->get();
                    if (isset($get_countries[0])) {
                        foreach ($get_countries as $list) {
                            $obj = new \stdClass();
                            $obj->country_alias = $list->country_alias;
                            $obj->country_name = $list->country_name;
                            $countries[$list->id] = $obj;
                        }
                    } else {
                        $countries = [];
                    }
                }

                $languages = Session::get('sio_languages');
                if (empty($languages)) {
                    // get table name
                    $language_table = (new language())->getTable();
                    $country_table = (new country())->getTable();

                    $get_languages = language::select(
                        $language_table . '.*'
                    )
                        ->leftJoin($country_table, $language_table . '.country_id', $country_table . '.id')
                        ->where($country_table . '.country_alias', $country_used)
                        ->orderBy($language_table . '.ordinal')
                        ->get();
                    if (isset($get_languages[0])) {
                        foreach ($get_languages as $list) {
                            $obj = new \stdClass();
                            $obj->name = $list->name;
                            $obj->alias = $list->alias;
                            $languages[$list->id] = $obj;

                            if ($language_used == $list->alias) {
                                $translations = json_decode($list->translations, true);
                            }
                        }
                    } else {
                        $languages = [];
                    }
                }
                if (empty($languages)) {
                    // set default languages
                    $obj = new \stdClass();
                    $obj->name = 'English';
                    $obj->alias = 'EN';
                    $languages[] = $obj;
                }

                if (empty($translations)) {
                    $translations = Session::get('sio_translations');
                    // using the phrases as default translations
                    if (empty($translations)) {
                        $translations = [];
                        $phrases = phrase::orderBy('content')->get();
                        foreach ($phrases as $list) {
                            $translations[$list->content] = $list->content;
                        }
                    }
                }
            }

            // share the variable to all Views
            View::share('sio_countries', $countries);

            // store the available countries data to this class attribute
            $this->sio_countries = $countries;

            // store the available countries data to session
            Session::put('sio_countries', $countries);

            // share the variable to all Views
            View::share('sio_languages', $languages);

            // store the available languages data to this class attribute
            $this->sio_languages = $languages;

            // store the available languages data to session
            Session::put('sio_languages', $languages);

            // share the variable to all Views
            View::share('translations', $translations);

            // store the translations data to this class attribute
            $this->translations = $translations;

            // store the available translations data to session
            Session::put('sio_translations', $translations);

            if (env('APP_BACKEND', 'MODEL') != 'API') {
                // set global config data from database
                $global_config = Config::first();
            } else {
                // set global config data from .env as default data
                $global_config = new \stdClass();
                $global_config->app_name = env('APP_NAME');
                $global_config->app_version = env('APP_VERSION');
                $global_config->app_url_site = env('APP_URL_SITE');
                $global_config->app_favicon = env('APP_FAVICON');
                $global_config->app_logo = env('APP_LOGO');
                $global_config->app_copyright_year = env('APP_COPYRIGHT_YEAR');
                $global_config->app_skin = env('APP_SKIN');
                $global_config->app_info = env('APP_INFO');
                $global_config->powered_by = env('POWERED_BY');
                $global_config->powered_by_url = env('POWERED_BY_URL');

                $global_config->meta_title = env('META_TITLE');
                $global_config->meta_description = env('META_DESCRIPTION');
                $global_config->meta_keywords = env('META_KEYWORDS');
                $global_config->meta_author = env('META_AUTHOR');

                $global_config->og_type = 'website';
                $global_config->og_site_name = env('APP_NAME');
                $global_config->og_title = env('META_TITLE');
                $global_config->og_image = env('APP_LOGO');
                $global_config->og_description = env('META_DESCRIPTION');
                $global_config->fb_app_id = '';
                $global_config->twitter_card = 'summary_large_image';
                $global_config->twitter_site = '';
                $global_config->twitter_site_id = '';
                $global_config->twitter_creator = '';
                $global_config->twitter_creator_id = '';

                $global_config->recaptcha_site_key_admin = '';
                $global_config->recaptcha_secret_key_admin = '';
            }

            // share the variable to all Views
            View::share('global_config', $global_config);

            // store the global configuration to this class attribute
            $this->global_config = $global_config;

            $product_category   = product_category::select(
                    'product_category.*',
                    DB::raw('COUNT(product_item.category_id) as products')
                )
                ->leftJoin('product_item', function($join) {
                    $join->on('product_category.id', '=', 'product_item.category_id')
                        ->where('product_item.published_status', 1)
                        ->where('product_item.approval_status', 1);
                })
                ->where('product_category.status', 1)
                ->orderBy('product_category.ordinal')
                ->groupBy('product_category.id')
                ->get();

            // share the variable to all Views
            View::share('product_category', $product_category);

            // store the global configuration to this class attribute
            $this->product_category = $product_category;

            return $next($request);
        });
    }
}
