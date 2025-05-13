<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// WEBSITE
Route::group(['namespace' => 'Web'], function () {
    // COMING SOON
    Route::get('/comingsoon', 'SiteController@comingsoon')->name('web.comingsoon');

    // COUNT DOWN
    Route::get('/countdown', 'SiteController@countdown')->name('web.countdown');

    // PRODUCT DETAIL
    Route::get('/product-detail/{product}', 'ProductController@detail')->name('web.product.detail');
    Route::post('/product-detail-ajax', 'ProductController@detail_ajax')->name('web.product.detail_ajax');

    Route::get('/seller-register', 'SiteController@seller_register')->name('web.seller_register');

    // USED BY XENDIT
    Route::post('/payment-webhook', 'TransactionController@payment_webhook_process');

    Route::group(['middleware' => 'coming.soon'], function () {
        // HOME
        Route::get('/', 'SiteController@index')->name('web.home');

        // FAQ
        Route::get('/faq', 'SiteController@faq')->name('web.faq');

        // PRODUCT
        Route::get('/product-list/{category}', 'ProductController@list')->name('web.product.list');
        Route::post('/product-list-ajax/{category}', 'ProductController@list_ajax')->name('web.product.list_ajax');
        Route::get('/search', 'ProductController@search')->name('web.product.search');
        Route::get('/filter', 'ProductController@filter')->name('web.product.filter');
        Route::post('/search-ajax', 'ProductController@search_ajax')->name('web.product.search_ajax');
        Route::post('/new-product', 'SiteController@products_new')->name('web.home.products_new');

        // WISHLIST
        Route::post('/add-wishlist-ajax', 'BuyerController@add_wishlist_ajax')->name('web.buyer.add_wishlist_ajax');

        // CART
        Route::post('/add-cart', 'CartController@add_cart')->name('web.buyer.add_cart');

        // REGISTER
        Route::get('/register', 'AuthController@register')->name('web.auth.register');
        Route::post('/thanks-register', 'AuthController@register_submit')->name('web.auth.register.submit');

        // VERIFICATION AFTER REGISTER
        Route::get('/verification/{token}', 'AuthController@verification')->name('web.auth.verification');

        // LOGIN
        Route::get('/login', 'AuthController@login')->name('web.auth.login');
        Route::post('/submit-login', 'AuthController@login_submit')->name('web.auth.login.submit');

        // PROVIDER
        Route::get('/auth/{social}', 'AuthController@redirect_to_provider')->name('web.auth.provider');
        Route::get('/auth/{social}/callback', 'AuthController@handle_provider_callback')->name('web.auth.provider.callback');

        // LOGOUT
        Route::get('/logout', 'AuthController@logout')->name('web.auth.logout');

        // FORGET PASSWORD
        Route::get('/forgot-password', 'AuthController@forgot_password')->name('web.auth.forgot_password');
        Route::post('/submit-forgot-password', 'AuthController@forgot_password_submit')->name('web.auth.forgot_password.submit');

        // RESET PASSWORD
        Route::get('/reset-password/{token}', 'AuthController@reset_password')->name('web.auth.reset_password');
        Route::post('/submit-reset-password', 'AuthController@reset_password_submit')->name('web.auth.reset_password.submit');

        // NEWS
        Route::group(['prefix' => 'news'], function () {
            Route::get('/', 'NewsController@index')->name('web.news');
            Route::get('/detail/{product}', 'NewsController@detail')->name('web.news.detail');
            Route::get('/get-data', 'NewsController@news_data')->name('web.news.get_data');
        });

        // NEED AUTH
        Route::group(['middleware' => 'auth.user'], function () {
            // ORDER
            Route::post('/order-summary', 'OrderController@summary')->name('web.order.summary');
            Route::match(['get', 'post'], '/process-order', 'OrderController@process')->name('web.order.process');
            Route::post('/confirm-order', 'OrderController@confirm')->name('web.order.confirm');
            Route::post('/create-order', 'OrderController@create')->name('web.order.create');
            Route::get('/order-history', 'OrderController@history')->name('web.order.history');
            Route::post('/order-history-data', 'OrderController@history_data')->name('web.order.history_data');
            Route::get('/order-detail/{transaction_id}', 'OrderController@order_detail')->name('web.order.order_detail');
            // Route::get('/order-success/{transaction_id}', 'OrderController@order_success')->name('web.order.success');
            Route::get('/order-success/{transaction_id}', 'TransactionController@transaction_success')->name('web.order.success');
            // Route::get('/order-failed/{transaction_id}', 'OrderController@order_failed')->name('web.order.failed');
            Route::get('/order-failed/{transaction_id}', 'TransactionController@transaction_failed')->name('web.order.failed');
            Route::post('/ajax-check-shipment-jne', 'OrderController@ajax_check_shipment_jne')->name('web.order.ajax_check_shipment_jne');
            Route::post('/ajax-check-shipment-anteraja', 'OrderController@ajax_check_shipment_anteraja')->name('web.order.ajax_check_shipment_anteraja');
            Route::get('/invoice-detail/{invoice_id}', 'OrderController@invoice_detail')->name('web.order.invoice_detail');

            // GET PROFILE
            Route::get('/profile', 'BuyerController@profile')->name('web.buyer.profile');
            Route::post('/profile-update', 'BuyerController@profile_update')->name('web.buyer.profile_update');

            // CHANGE PASSWORD
            Route::get('/change-password', 'AuthController@change_password')->name('web.auth.change_password');
            Route::post('/submit-change-password', 'AuthController@change_password_submit')->name('web.auth.change_password.submit');

            // CHANGE EMAIL
            Route::get('/change-email', 'AuthController@change_email')->name('web.auth.change_email');
            Route::post('/ajax-check-password', 'AuthController@ajax_check_password')->name('web.auth.ajax_check_password');
            Route::post('/ajax-change-email', 'AuthController@ajax_change_email')->name('web.auth.ajax_change_email');

            // ADDRESS
            Route::get('/list-address', 'BuyerController@list_address')->name('web.buyer.list_address');
            Route::get('/add-address', 'BuyerController@add_address')->name('web.buyer.add_address');
            Route::post('/store-address', 'BuyerController@store_address')->name('web.buyer.store_address');
            Route::get('/address/{id}', 'BuyerController@edit_address')->name('web.buyer.edit_address');
            Route::post('/update-address', 'BuyerController@update_address')->name('web.buyer.update_address');
            Route::post('/set-default-address', 'BuyerController@set_default_address')->name('web.buyer.set_default_address');
            Route::post('/detail-buyer-address', 'BuyerController@detail_buyer_address')->name('web.buyer.detail_buyer_address');

            // WISHLIST
            Route::get('/wishlist', 'BuyerController@wishlist')->name('web.buyer.wishlist');
            Route::post('/wishlist-ajax', 'BuyerController@wishlist_ajax')->name('web.buyer.wishlist_ajax');
            Route::post('/delete-wishlist-ajax', 'BuyerController@delete_wishlist_ajax')->name('web.buyer.delete_wishlist_ajax');
            
            // CART
            Route::get('/cart', 'CartController@cart')->name('web.buyer.cart');
            Route::post('/update-cart', 'CartController@update_cart')->name('web.buyer.update_cart');
            Route::post('/delete-cart-ajax', 'CartController@delete_cart_ajax')->name('web.buyer.delete_cart_ajax');
            Route::post('/total-cart', 'CartController@total_cart')->name('web.buyer.total_cart');

            // CHECKOUT
            Route::match(['get', 'post'], '/cart/shipment', 'CartCheckoutController@checkout')->name('web.cart.checkout');
            Route::post('/cart/submit_voucher', 'CartCheckoutController@submit_voucher')->name('web.cart.submit_voucher');
            Route::match(['get', 'post'], '/cart/create-order', 'TransactionController@create_transaction')->name('web.cart.create_order');
            
            // DUMMY PAGE
            Route::get('/checkout', 'BuyerController@checkout')->name('web.buyer.checkout');

            // ORDER LIST
            Route::get('/order-list', 'OrderController@order_list')->name('web.order.order_list');
        });

        Route::get('/change-email-success', 'AuthController@change_email_success')->name('web.auth.change_email_success');
        Route::get('/confirm-change-email/{token}', 'AuthController@confirm_change_email')->name('web.auth.confirm_change_email');
        Route::post('/resend-change-email', 'AuthController@resend_change_email')->name('web.auth.resend_change_email');
        Route::post('/resend-email-register', 'AuthController@resend_email_register')->name('web.auth.resend_email_register');
    });

    // CRON
    Route::group(['prefix' => 'cron'], function () {
        Route::get('/set-expired-order', 'OrderController@set_expired')->name('web.order.set_expired');
    });
});

// ADMIN
Route::group([
    'prefix' => env('ADMIN_DIR'),
    'namespace' => 'Admin'
], function () {
    if (env('ADMIN_CMS', false) == true) {
        Route::group(['namespace' => 'Core'], function () {
            // AUTH
            Route::get('/login', 'AuthController@login')->name('admin.login');
            Route::post('/login-auth', 'AuthController@login_auth')->name('admin.login.auth');
            Route::get('/logout', 'AuthController@logout')->name('admin.logout');
            Route::get('/logout-all', 'AuthController@logout_all')->name('admin.logout.all');
        });

        // NEED AUTH
        Route::group(['middleware' => 'auth.admin'], function () {

            ### SIORENSYS CORE (PLEASE DO NOT MODIFY THE CODE BELOW, UNLESS YOU UNDERSTAND WHAT YOU ARE DOING) ###
            Route::group(['namespace' => 'Core'], function () {
                // NOTIFICATION
                Route::group(['prefix' => 'notification'], function () {
                    Route::get('/', 'NotificationController@index')->name('admin.notif.list');
                    Route::get('/get-data', 'NotificationController@get_data')->name('admin.notif.get_data');
                    Route::get('/open/{id}', 'NotificationController@open')->name('admin.notif.open');
                    Route::get('/get-data/bar', 'NotificationController@get_notif')->name('admin.notif');
                });

                // CONFIG APP
                Route::group(['prefix' => 'config'], function () {
                    Route::match(['get', 'post'], '/', 'ConfigController@index')->name('admin.config');
                    Route::get('/export', 'ConfigController@export')->name('admin.config.export');
                    Route::post('/import', 'ConfigController@import')->name('admin.config.import');
                });

                // CHANGE COUNTRY
                Route::get('change-country/{alias}', 'AdminController@change_country')->name('admin.change_country');

                // CHANGE LANGUAGE
                Route::get('change-language/{alias}', 'AdminController@change_language')->name('admin.change_language');

                // PROFILE
                Route::match(['get', 'post'], '/profile', 'AdminController@profile')->name('admin.profile');

                // CHANGE PASSWORD
                Route::post('/change-password', 'AdminController@change_password')->name('admin.change_password');

                // SYSTEM LOGS
                Route::group(['prefix' => 'system-logs'], function () {
                    Route::get('/', 'SystemLogController@index')->name('admin.system_logs');
                    Route::get('/get-data', 'SystemLogController@get_data')->name('admin.system_logs.get_data');
                    Route::get('/{id}', 'SystemLogController@view')->name('admin.system_logs.view');
                });

                // MODULE
                Route::group(['prefix' => 'module'], function () {
                    Route::get('/', 'ModuleController@index')->name('admin.module');
                    Route::get('/get-data', 'ModuleController@get_data')->name('admin.module.get_data');
                    Route::get('/create', 'ModuleController@create')->name('admin.module.create');
                    Route::post('/store', 'ModuleController@store')->name('admin.module.store');
                    Route::get('/edit/{id}', 'ModuleController@edit')->name('admin.module.edit');
                    Route::post('/update/{id}', 'ModuleController@update')->name('admin.module.update');
                    Route::post('/delete', 'ModuleController@delete')->name('admin.module.delete');
                    Route::get('/deleted-data', 'ModuleController@deleted_data')->name('admin.module.deleted_data');
                    Route::get('/get-deleted-data', 'ModuleController@get_deleted_data')->name('admin.module.get_deleted_data');
                    Route::post('/restore', 'ModuleController@restore')->name('admin.module.restore');
                });

                // MODULE RULES
                Route::group(['prefix' => 'rules'], function () {
                    Route::get('/', 'ModuleRuleController@index')->name('admin.module_rule');
                    Route::get('/get-data', 'ModuleRuleController@get_data')->name('admin.module_rule.get_data');
                    Route::get('/create', 'ModuleRuleController@create')->name('admin.module_rule.create');
                    Route::post('/store', 'ModuleRuleController@store')->name('admin.module_rule.store');
                    Route::get('/edit/{id}', 'ModuleRuleController@edit')->name('admin.module_rule.edit');
                    Route::post('/update/{id}', 'ModuleRuleController@update')->name('admin.module_rule.update');
                    Route::post('/delete', 'ModuleRuleController@delete')->name('admin.module_rule.delete');
                    Route::get('/deleted-data', 'ModuleRuleController@deleted_data')->name('admin.module_rule.deleted_data');
                    Route::get('/get-deleted-data', 'ModuleRuleController@get_deleted_data')->name('admin.module_rule.get_deleted_data');
                    Route::post('/restore', 'ModuleRuleController@restore')->name('admin.module_rule.restore');
                });

                // PHRASE
                Route::group(['prefix' => 'phrase'], function () {
                    Route::get('/', 'PhraseController@index')->name('admin.phrase');
                    Route::get('/get-data', 'PhraseController@get_data')->name('admin.phrase.get_data');
                    Route::get('/create', 'PhraseController@create')->name('admin.phrase.create');
                    Route::post('/store', 'PhraseController@store')->name('admin.phrase.store');
                    Route::get('/edit/{id}', 'PhraseController@edit')->name('admin.phrase.edit');
                    Route::post('/update/{id}', 'PhraseController@update')->name('admin.phrase.update');
                    Route::post('/delete', 'PhraseController@delete')->name('admin.phrase.delete');
                    Route::get('/deleted-data', 'PhraseController@deleted_data')->name('admin.phrase.deleted_data');
                    Route::get('/get-deleted-data', 'PhraseController@get_deleted_data')->name('admin.phrase.get_deleted_data');
                    Route::post('/restore', 'PhraseController@restore')->name('admin.phrase.restore');
                });

                // OFFICE
                Route::group(['prefix' => 'office'], function () {
                    Route::get('/', 'OfficeController@index')->name('admin.office');
                    Route::get('/get-data', 'OfficeController@get_data')->name('admin.office.get_data');
                    Route::get('/create', 'OfficeController@create')->name('admin.office.create');
                    Route::post('/store', 'OfficeController@store')->name('admin.office.store');
                    Route::get('/edit/{id}', 'OfficeController@edit')->name('admin.office.edit');
                    Route::post('/update/{id}', 'OfficeController@update')->name('admin.office.update');
                    Route::post('/delete', 'OfficeController@delete')->name('admin.office.delete');
                    Route::get('/deleted-data', 'OfficeController@deleted_data')->name('admin.office.deleted_data');
                    Route::get('/get-deleted-data', 'OfficeController@get_deleted_data')->name('admin.office.get_deleted_data');
                    Route::post('/restore', 'OfficeController@restore')->name('admin.office.restore');
                    Route::post('/sorting', 'OfficeController@sorting')->name('admin.office.sorting');

                    // BRANCH
                    Route::group(['prefix' => 'branch/{office_id}'], function () {
                        Route::get('/', 'OfficeBranchController@index')->name('admin.office_branch');
                        Route::get('/get-data', 'OfficeBranchController@get_data')->name('admin.office_branch.get_data');
                        Route::get('/create', 'OfficeBranchController@create')->name('admin.office_branch.create');
                        Route::post('/store', 'OfficeBranchController@store')->name('admin.office_branch.store');
                        Route::get('/edit/{id}', 'OfficeBranchController@edit')->name('admin.office_branch.edit');
                        Route::post('/update/{id}', 'OfficeBranchController@update')->name('admin.office_branch.update');
                        Route::post('/delete', 'OfficeBranchController@delete')->name('admin.office_branch.delete');
                        Route::get('/deleted-data', 'OfficeBranchController@deleted_data')->name('admin.office_branch.deleted_data');
                        Route::get('/get-deleted-data', 'OfficeBranchController@get_deleted_data')->name('admin.office_branch.get_deleted_data');
                        Route::post('/restore', 'OfficeBranchController@restore')->name('admin.office_branch.restore');
                        Route::post('/sorting', 'OfficeBranchController@sorting')->name('admin.office_branch.sorting');
                    });
                });

                // ADMIN GROUP
                Route::group(['prefix' => 'group'], function () {
                    Route::get('/', 'AdminGroupController@index')->name('admin.group');
                    Route::get('/get-data', 'AdminGroupController@get_data')->name('admin.group.get_data');
                    Route::get('/create', 'AdminGroupController@create')->name('admin.group.create');
                    Route::post('/store', 'AdminGroupController@store')->name('admin.group.store');
                    Route::get('/edit/{id}', 'AdminGroupController@edit')->name('admin.group.edit');
                    Route::post('/update/{id}', 'AdminGroupController@update')->name('admin.group.update');
                    Route::post('/delete', 'AdminGroupController@delete')->name('admin.group.delete');
                    Route::get('/deleted-data', 'AdminGroupController@deleted_data')->name('admin.group.deleted_data');
                    Route::get('/get-deleted-data', 'AdminGroupController@get_deleted_data')->name('admin.group.get_deleted_data');
                    Route::post('/restore', 'AdminGroupController@restore')->name('admin.group.restore');
                });

                // ADMIN
                Route::group(['prefix' => 'administrator'], function () {
                    Route::get('/', 'AdminController@index')->name('admin.user_admin');
                    Route::get('/get-data', 'AdminController@get_data')->name('admin.user_admin.get_data');
                    Route::get('/create', 'AdminController@create')->name('admin.user_admin.create');
                    Route::post('/store', 'AdminController@store')->name('admin.user_admin.store');
                    Route::get('/edit/{id}', 'AdminController@edit')->name('admin.user_admin.edit');
                    Route::post('/update/{id}', 'AdminController@update')->name('admin.user_admin.update');
                    Route::post('/reset-password/{id}', 'AdminController@reset_password')->name('admin.user_admin.reset_password');
                    Route::post('/delete', 'AdminController@delete')->name('admin.user_admin.delete');
                    Route::get('/deleted-data', 'AdminController@deleted_data')->name('admin.user_admin.deleted_data');
                    Route::get('/get-deleted-data', 'AdminController@get_deleted_data')->name('admin.user_admin.get_deleted_data');
                    Route::post('/restore', 'AdminController@restore')->name('admin.user_admin.restore');
                });

                // COUNTRY
                Route::group(['prefix' => 'country'], function () {
                    Route::get('/', 'CountryController@index')->name('admin.country');
                    Route::get('/get-data', 'CountryController@get_data')->name('admin.country.get_data');
                    Route::get('/create', 'CountryController@create')->name('admin.country.create');
                    Route::post('/store', 'CountryController@store')->name('admin.country.store');
                    Route::get('/edit/{id}', 'CountryController@edit')->name('admin.country.edit');
                    Route::post('/update/{id}', 'CountryController@update')->name('admin.country.update');
                    Route::post('/delete', 'CountryController@delete')->name('admin.country.delete');
                    Route::get('/deleted-data', 'CountryController@deleted_data')->name('admin.country.deleted_data');
                    Route::get('/get-deleted-data', 'CountryController@get_deleted_data')->name('admin.country.get_deleted_data');
                    Route::post('/restore', 'CountryController@restore')->name('admin.country.restore');

                    // LANGUAGE
                    Route::group(['prefix' => '{parent_id}/language'], function () {
                        Route::get('/', 'LanguageController@index')->name('admin.language');
                        Route::get('/get-data', 'LanguageController@get_data')->name('admin.language.get_data');
                        Route::get('/create', 'LanguageController@create')->name('admin.language.create');
                        Route::post('/store', 'LanguageController@store')->name('admin.language.store');
                        Route::get('/edit/{id}', 'LanguageController@edit')->name('admin.language.edit');
                        Route::post('/update/{id}', 'LanguageController@update')->name('admin.language.update');
                        Route::post('/delete', 'LanguageController@delete')->name('admin.language.delete');
                        Route::get('/deleted-data', 'LanguageController@deleted_data')->name('admin.language.deleted_data');
                        Route::get('/get-deleted-data', 'LanguageController@get_deleted_data')->name('admin.language.get_deleted_data');
                        Route::post('/restore', 'LanguageController@restore')->name('admin.language.restore');
                        Route::post('/sorting', 'LanguageController@sorting')->name('admin.language.sorting');
                        Route::get('/dictionary/{id}', 'LanguageController@dictionary')->name('admin.language.dictionary');
                        Route::post('/dictionary/{id}/save', 'LanguageController@dictionary_save')->name('admin.language.dictionary.save');
                    });
                });

                // BLOCKED IP
                Route::group(['prefix' => 'blocked-ip'], function () {
                    Route::get('/', 'BlockedIpController@index')->name('admin.blocked_ip');
                    Route::get('/get-data', 'BlockedIpController@get_data')->name('admin.blocked_ip.get_data');
                    Route::get('/create', 'BlockedIpController@create')->name('admin.blocked_ip.create');
                    Route::post('/store', 'BlockedIpController@store')->name('admin.blocked_ip.store');
                    Route::get('/edit/{id}', 'BlockedIpController@edit')->name('admin.blocked_ip.edit');
                    Route::post('/update/{id}', 'BlockedIpController@update')->name('admin.blocked_ip.update');
                    Route::post('/delete', 'BlockedIpController@delete')->name('admin.blocked_ip.delete');
                    Route::get('/deleted-data', 'BlockedIpController@deleted_data')->name('admin.blocked_ip.deleted_data');
                    Route::get('/get-deleted-data', 'BlockedIpController@get_deleted_data')->name('admin.blocked_ip.get_deleted_data');
                    Route::post('/restore', 'BlockedIpController@restore')->name('admin.blocked_ip.restore');
                });

                // ERROR LOGS
                Route::group(['prefix' => 'error-logs'], function () {
                    Route::get('/', 'ErrorLogController@index')->name('admin.error_logs');
                    Route::get('/get-data', 'ErrorLogController@get_data')->name('admin.error_logs.get_data');
                    Route::get('/view/{id}', 'ErrorLogController@view')->name('admin.error_logs.view');
                    Route::post('/update/{id}', 'ErrorLogController@update')->name('admin.error_logs.update');
                });

                // NAV MENU
                Route::group(['prefix' => 'nav-menu/{position}'], function () {
                    Route::get('/', 'NavMenuController@index')->name('admin.nav_menu');
                    Route::get('/get-data', 'NavMenuController@get_data')->name('admin.nav_menu.get_data');
                    Route::post('/sorting', 'NavMenuController@sorting')->name('admin.nav_menu.sorting');
                    Route::get('/create', 'NavMenuController@create')->name('admin.nav_menu.create');
                    Route::post('/store', 'NavMenuController@store')->name('admin.nav_menu.store');
                    Route::get('/edit/{id}', 'NavMenuController@edit')->name('admin.nav_menu.edit');
                    Route::post('/update/{id}', 'NavMenuController@update')->name('admin.nav_menu.update');
                    Route::post('/delete', 'NavMenuController@delete')->name('admin.nav_menu.delete');
                    Route::get('/deleted-data', 'NavMenuController@deleted_data')->name('admin.nav_menu.deleted_data');
                    Route::get('/get-deleted-data', 'NavMenuController@get_deleted_data')->name('admin.nav_menu.get_deleted_data');
                    Route::post('/restore', 'NavMenuController@restore')->name('admin.nav_menu.restore');

                    Route::group(['prefix' => '{parent}'], function () {
                        Route::get('/', 'NavMenuChildController@index')->name('admin.nav_menu_child');
                        Route::get('/get-data', 'NavMenuChildController@get_data')->name('admin.nav_menu_child.get_data');
                        Route::post('/sorting', 'NavMenuChildController@sorting')->name('admin.nav_menu_child.sorting');
                        Route::get('/create', 'NavMenuChildController@create')->name('admin.nav_menu_child.create');
                        Route::post('/store', 'NavMenuChildController@store')->name('admin.nav_menu_child.store');
                        Route::get('/edit/{id}', 'NavMenuChildController@edit')->name('admin.nav_menu_child.edit');
                        Route::post('/update/{id}', 'NavMenuChildController@update')->name('admin.nav_menu_child.update');
                    });
                });

                // PAGE
                Route::group(['prefix' => 'page'], function () {
                    Route::get('/', 'PageController@index')->name('admin.page');
                    Route::get('/get-data', 'PageController@get_data')->name('admin.page.get_data');
                    Route::get('/create', 'PageController@create')->name('admin.page.create');
                    Route::post('/store', 'PageController@store')->name('admin.page.store');
                    Route::get('/edit/{id}', 'PageController@edit')->name('admin.page.edit');
                    Route::post('/update/{id}', 'PageController@update')->name('admin.page.update');
                    Route::post('/delete', 'PageController@delete')->name('admin.page.delete');
                    Route::get('/deleted-data', 'PageController@deleted_data')->name('admin.page.deleted_data');
                    Route::get('/get-deleted-data', 'PageController@get_deleted_data')->name('admin.page.get_deleted_data');
                    Route::post('/restore', 'PageController@restore')->name('admin.page.restore');
                });

                // SOCIAL MEDIA
                Route::group(['prefix' => 'social-media'], function () {
                    Route::get('/', 'SocialMediaController@index')->name('admin.social_media');
                    Route::get('/get-data', 'SocialMediaController@get_data')->name('admin.social_media.get_data');
                    Route::get('/create', 'SocialMediaController@create')->name('admin.social_media.create');
                    Route::post('/store', 'SocialMediaController@store')->name('admin.social_media.store');
                    Route::get('/edit/{id}', 'SocialMediaController@edit')->name('admin.social_media.edit');
                    Route::post('/update/{id}', 'SocialMediaController@update')->name('admin.social_media.update');
                    Route::post('/delete', 'SocialMediaController@delete')->name('admin.social_media.delete');
                    Route::get('/deleted-data', 'SocialMediaController@deleted_data')->name('admin.social_media.deleted_data');
                    Route::get('/get-deleted-data', 'SocialMediaController@get_deleted_data')->name('admin.social_media.get_deleted_data');
                    Route::post('/restore', 'SocialMediaController@restore')->name('admin.social_media.restore');
                    Route::post('/sorting', 'SocialMediaController@sorting')->name('admin.social_media.sorting');
                });

                // FAQ
                Route::group(['prefix' => 'faq'], function () {
                    Route::get('/', 'FaqController@index')->name('admin.faq');
                    Route::get('/get-data', 'FaqController@get_data')->name('admin.faq.get_data');
                    Route::post('/sorting', 'FaqController@sorting')->name('admin.faq.sorting');
                    Route::get('/create', 'FaqController@create')->name('admin.faq.create');
                    Route::post('/store', 'FaqController@store')->name('admin.faq.store');
                    Route::get('/edit/{id}', 'FaqController@edit')->name('admin.faq.edit');
                    Route::post('/update/{id}', 'FaqController@update')->name('admin.faq.update');
                    Route::post('/delete', 'FaqController@delete')->name('admin.faq.delete');
                    Route::get('/deleted-data', 'FaqController@deleted_data')->name('admin.faq.deleted_data');
                    Route::get('/get-deleted-data', 'FaqController@get_deleted_data')->name('admin.faq.get_deleted_data');
                    Route::post('/restore', 'FaqController@restore')->name('admin.faq.restore');

                    Route::group(['prefix' => '{parent}'], function () {
                        Route::get('/', 'FaqItemController@index')->name('admin.faq_item');
                        Route::get('/get-data', 'FaqItemController@get_data')->name('admin.faq_item.get_data');
                        Route::post('/sorting', 'FaqItemController@sorting')->name('admin.faq_item.sorting');
                        Route::get('/create', 'FaqItemController@create')->name('admin.faq_item.create');
                        Route::post('/store', 'FaqItemController@store')->name('admin.faq_item.store');
                        Route::get('/edit/{id}', 'FaqItemController@edit')->name('admin.faq_item.edit');
                        Route::post('/update/{id}', 'FaqItemController@update')->name('admin.faq_item.update');
                    });
                });

                // NOTE
                Route::group(['prefix' => 'note'], function () {
                    Route::get('/', 'NoteController@index')->name('admin.note');
                    Route::get('/get-data', 'NoteController@get_data')->name('admin.note.get_data');
                    Route::get('/create', 'NoteController@create')->name('admin.note.create');
                    Route::post('/store', 'NoteController@store')->name('admin.note.store');
                    Route::get('/edit/{id}', 'NoteController@edit')->name('admin.note.edit');
                    Route::post('/update/{id}', 'NoteController@update')->name('admin.note.update');
                    Route::post('/delete', 'NoteController@delete')->name('admin.note.delete');
                    Route::get('/deleted-data', 'NoteController@deleted_data')->name('admin.note.deleted_data');
                    Route::get('/get-deleted-data', 'NoteController@get_deleted_data')->name('admin.note.get_deleted_data');
                    Route::post('/restore', 'NoteController@restore')->name('admin.note.restore');
                });

                // FORM
                Route::group(['prefix' => 'form'], function () {
                    Route::get('/', 'FormController@index')->name('admin.form');
                    Route::get('/get-data', 'FormController@get_data')->name('admin.form.get_data');
                    Route::group(['prefix' => '{type}'], function () {
                        Route::get('/create', 'FormController@create')->name('admin.form.create');
                        Route::post('/store', 'FormController@store')->name('admin.form.store');
                    });
                    Route::get('/edit/{id}', 'FormController@edit')->name('admin.form.edit');
                    Route::post('/update/{id}', 'FormController@update')->name('admin.form.update');
                    Route::post('/delete', 'FormController@delete')->name('admin.form.delete');
                    Route::get('/deleted-data', 'FormController@deleted_data')->name('admin.form.deleted_data');
                    Route::get('/get-deleted-data', 'FormController@get_deleted_data')->name('admin.form.get_deleted_data');
                    Route::post('/restore', 'FormController@restore')->name('admin.form.restore');
                });
            });
            ### SIORENSYS CORE - END ###

            /**
             * ******************* ADD ANOTHER CUSTOM ROUTES BELOW *******************
             */

            // HOME
            Route::get('/', 'HomeController@index')->name('admin.home');

            // BANNER
            Route::group(['prefix' => 'banner/{position}'], function () {
                Route::get('/', 'BannerController@index')->name('admin.banner');
                Route::get('/get-data', 'BannerController@get_data')->name('admin.banner.get_data');
                Route::post('/sorting', 'BannerController@sorting')->name('admin.banner.sorting');
                Route::get('/create', 'BannerController@create')->name('admin.banner.create');
                Route::post('/store', 'BannerController@store')->name('admin.banner.store');
                Route::get('/edit/{id}', 'BannerController@edit')->name('admin.banner.edit');
                Route::post('/update/{id}', 'BannerController@update')->name('admin.banner.update');
                Route::post('/delete', 'BannerController@delete')->name('admin.banner.delete');
                Route::get('/deleted-data', 'BannerController@deleted_data')->name('admin.banner.deleted_data');
                Route::get('/get-deleted-data', 'BannerController@get_deleted_data')->name('admin.banner.get_deleted_data');
                Route::post('/restore', 'BannerController@restore')->name('admin.banner.restore');
            });

            // PRODUCT CATEGORY
            Route::group(['prefix' => 'product-category'], function () {
                Route::get('/', 'ProductCategoryController@index')->name('admin.product_category');
                Route::get('/get-data', 'ProductCategoryController@get_data')->name('admin.product_category.get_data');
                Route::get('/create', 'ProductCategoryController@create')->name('admin.product_category.create');
                Route::post('/store', 'ProductCategoryController@store')->name('admin.product_category.store');
                Route::get('/edit/{id}', 'ProductCategoryController@edit')->name('admin.product_category.edit');
                Route::post('/update/{id}', 'ProductCategoryController@update')->name('admin.product_category.update');
                Route::post('/delete', 'ProductCategoryController@delete')->name('admin.product_category.delete');
                Route::get('/deleted-data', 'ProductCategoryController@deleted_data')->name('admin.product_category.deleted_data');
                Route::get('/get-deleted-data', 'ProductCategoryController@get_deleted_data')->name('admin.product_category.get_deleted_data');
                Route::post('/restore', 'ProductCategoryController@restore')->name('admin.product_category.restore');
                Route::post('/sorting', 'ProductCategoryController@sorting')->name('admin.product_category.sorting');
            });

            // PRODUCT ITEM
            Route::group(['prefix' => 'product-item'], function () {
                Route::get('/', 'ProductItemController@index')->name('admin.product_item');
                Route::get('/get-data', 'ProductItemController@get_data')->name('admin.product_item.get_data');
                Route::get('/create', 'ProductItemController@create')->name('admin.product_item.create');
                Route::post('/store', 'ProductItemController@store')->name('admin.product_item.store');
                Route::get('/edit/{id}', 'ProductItemController@edit')->name('admin.product_item.edit');
                Route::post('/update/{id}', 'ProductItemController@update')->name('admin.product_item.update');
                Route::post('/delete', 'ProductItemController@delete')->name('admin.product_item.delete');
                Route::post('/featured', 'ProductItemController@featured')->name('admin.product_item.featured');
                Route::get('/deleted-data', 'ProductItemController@deleted_data')->name('admin.product_item.deleted_data');
                Route::get('/get-deleted-data', 'ProductItemController@get_deleted_data')->name('admin.product_item.get_deleted_data');
                Route::post('/restore', 'ProductItemController@restore')->name('admin.product_item.restore');
                Route::get('/finish/{id}', 'ProductItemController@finish')->name('admin.product_item.finish');
                Route::post('/ajax-validate-preview-item', 'ProductItemController@ajax_validate_preview_item')->name('web.product.ajax_validate_preview_item');
                Route::get('/export', 'ProductItemController@export')->name('admin.product_item.export');
            });

            // PRODUCT FEATURED
            Route::group(['prefix' => 'product-featured'], function () {
                Route::get('/', 'ProductFeaturedController@index')->name('admin.product_featured');
                Route::get('/get-data', 'ProductFeaturedController@get_data')->name('admin.product_featured.get_data');
                Route::post('/sorting', 'ProductFeaturedController@sorting')->name('admin.product_featured.sorting');
                Route::post('/update-status', 'ProductFeaturedController@update_status')->name('admin.product_featured.update_status');
            });

            // PRODUCT FAQ
            Route::group(['prefix' => 'product-faq'], function () {
                Route::get('/{product_item_id}', 'ProductFaqController@index')->name('admin.product_faq');
                Route::get('/get-data/{product_item_id}', 'ProductFaqController@get_data')->name('admin.product_faq.get_data');
                Route::get('/create/{product_item_id}', 'ProductFaqController@create')->name('admin.product_faq.create');
                Route::post('/store/{product_item_id}', 'ProductFaqController@store')->name('admin.product_faq.store');
                Route::get('/edit/{product_item_id}/{id}', 'ProductFaqController@edit')->name('admin.product_faq.edit');
                Route::post('/update/{product_item_id}/{id}', 'ProductFaqController@update')->name('admin.product_faq.update');
                Route::post('/delete/{product_item_id}', 'ProductFaqController@delete')->name('admin.product_faq.delete');
                Route::get('/deleted-data/{product_item_id}', 'ProductFaqController@deleted_data')->name('admin.product_faq.deleted_data');
                Route::get('/get-deleted-data/{product_item_id}', 'ProductFaqController@get_deleted_data')->name('admin.product_faq.get_deleted_data');
                Route::post('/restore/{product_item_id}', 'ProductFaqController@restore')->name('admin.product_faq.restore');
            });

            // PRODUCT VARIANT
            Route::group(['prefix' => 'product-variant'], function () {
                Route::get('/{product_item_id}', 'ProductVariantController@index')->name('admin.product_variant');
                Route::get('/get-data/{product_item_id}', 'ProductVariantController@get_data')->name('admin.product_variant.get_data');
                Route::get('/create/{product_item_id}', 'ProductVariantController@create')->name('admin.product_variant.create');
                Route::post('/store/{product_item_id}', 'ProductVariantController@store')->name('admin.product_variant.store');
                // Route::get('/edit/{product_item_id}/{id}', 'ProductVariantController@edit')->name('admin.product_variant.edit');
                Route::post('/update/{product_item_id}', 'ProductVariantController@update')->name('admin.product_variant.update');
                // Route::post('/delete/{product_item_id}', 'ProductVariantController@delete')->name('admin.product_variant.delete');
                // Route::get('/deleted-data/{product_item_id}', 'ProductVariantController@deleted_data')->name('admin.product_variant.deleted_data');
                // Route::get('/get-deleted-data/{product_item_id}', 'ProductVariantController@get_deleted_data')->name('admin.product_variant.get_deleted_data');
                // Route::post('/restore/{product_item_id}', 'ProductVariantController@restore')->name('admin.product_variant.restore');
            });

            // PRODUCT CONTENT
            Route::group(['prefix' => 'product-content'], function () {
                Route::get('/{product_item_id}', 'ProductContentController@view')->name('admin.product_content');
                Route::post('/update/{product_item_id}', 'ProductContentController@update')->name('admin.product_content.update');
            });

            // SELLER
            Route::group(['prefix' => 'seller'], function () {
                Route::get('/', 'SellerController@index')->name('admin.seller');
                Route::get('/get-data', 'SellerController@get_data')->name('admin.seller.get_data');
                Route::get('/create', 'SellerController@create')->name('admin.seller.create');
                Route::post('/store', 'SellerController@store')->name('admin.seller.store');
                Route::get('/edit/{id}', 'SellerController@edit')->name('admin.seller.edit');
                Route::post('/update/{id}', 'SellerController@update')->name('admin.seller.update');
                Route::post('/delete', 'SellerController@delete')->name('admin.seller.delete');
                Route::get('/deleted-data', 'SellerController@deleted_data')->name('admin.seller.deleted_data');
                Route::get('/get-deleted-data', 'SellerController@get_deleted_data')->name('admin.seller.get_deleted_data');
                Route::post('/restore', 'SellerController@restore')->name('admin.seller.restore');
                Route::post('/resend-email', 'SellerController@resend_email')->name('admin.seller.resend_email');
                Route::get('/export', 'SellerController@export')->name('admin.seller.export');
            });

            // BUYER
            Route::group(['prefix' => 'buyer'], function () {
                Route::get('/', 'BuyerController@index')->name('admin.buyer');
                Route::get('/get-data', 'BuyerController@get_data')->name('admin.buyer.get_data');
                Route::get('/create', 'BuyerController@create')->name('admin.buyer.create');
                Route::post('/store', 'BuyerController@store')->name('admin.buyer.store');
                Route::get('/edit/{id}', 'BuyerController@edit')->name('admin.buyer.edit');
                Route::post('/update/{id}', 'BuyerController@update')->name('admin.buyer.update');
                Route::post('/delete', 'BuyerController@delete')->name('admin.buyer.delete');
                Route::get('/deleted-data', 'BuyerController@deleted_data')->name('admin.buyer.deleted_data');
                Route::get('/get-deleted-data', 'BuyerController@get_deleted_data')->name('admin.buyer.get_deleted_data');
                Route::post('/restore', 'BuyerController@restore')->name('admin.buyer.restore');
                Route::get('/export', 'BuyerController@export')->name('admin.buyer.export');
            });

            // ORDER
            Route::group(['prefix' => 'order'], function () {
                Route::get('/', 'OrderController@index')->name('admin.order');
                Route::get('/get-data', 'OrderController@get_data')->name('admin.order.get_data');
                Route::get('/detail/{id}', 'OrderController@detail')->name('admin.order.detail');
                Route::post('/delete', 'OrderController@delete')->name('admin.order.delete');
                Route::get('/deleted-data', 'OrderController@deleted_data')->name('admin.order.deleted_data');
                Route::get('/get-deleted-data', 'OrderController@get_deleted_data')->name('admin.order.get_deleted_data');
                Route::post('/restore', 'OrderController@restore')->name('admin.order.restore');
                Route::get('/export', 'OrderController@export')->name('admin.order.export');
            });

            // GLOBAL CONFIG
            Route::group(['prefix' => 'global-config'], function () {
                Route::match(['get', 'post'], '/', 'GlobalConfigController@index')->name('admin.global.config');
            });

            // SHIPPER
            Route::group(['prefix' => 'shipper'], function () {
                Route::get('/', 'ShipperController@index')->name('admin.shipper');
                Route::get('/get-data', 'ShipperController@get_data')->name('admin.shipper.get_data');
                Route::post('/change-status', 'ShipperController@change_status')->name('admin.shipper.change_status');
            });

            // NEWS CATEGORY
            Route::group(['prefix' => 'news-category'], function () {
                Route::get('/', 'NewsCategoryController@index')->name('admin.news_category');
                Route::get('/get-data', 'NewsCategoryController@get_data')->name('admin.news_category.get_data');
                Route::get('/create', 'NewsCategoryController@create')->name('admin.news_category.create');
                Route::post('/store', 'NewsCategoryController@store')->name('admin.news_category.store');
                Route::get('/edit/{id}', 'NewsCategoryController@edit')->name('admin.news_category.edit');
                Route::post('/update/{id}', 'NewsCategoryController@update')->name('admin.news_category.update');
                Route::post('/delete', 'NewsCategoryController@delete')->name('admin.news_category.delete');
                Route::get('/deleted-data', 'NewsCategoryController@deleted_data')->name('admin.news_category.deleted_data');
                Route::get('/get-deleted-data', 'NewsCategoryController@get_deleted_data')->name('admin.news_category.get_deleted_data');
                Route::post('/restore', 'NewsCategoryController@restore')->name('admin.news_category.restore');
            });

            // NEWS
            Route::group(['prefix' => 'news'], function () {
                Route::get('/', 'NewsController@index')->name('admin.news');
                Route::get('/get-data', 'NewsController@get_data')->name('admin.news.get_data');
                Route::get('/create', 'NewsController@create')->name('admin.news.create');
                Route::post('/store', 'NewsController@store')->name('admin.news.store');
                Route::get('/edit/{id}', 'NewsController@edit')->name('admin.news.edit');
                Route::post('/update/{id}', 'NewsController@update')->name('admin.news.update');
                Route::post('/delete', 'NewsController@delete')->name('admin.news.delete');
                Route::get('/deleted-data', 'NewsController@deleted_data')->name('admin.news.deleted_data');
                Route::get('/get-deleted-data', 'NewsController@get_deleted_data')->name('admin.news.get_deleted_data');
                Route::post('/restore', 'NewsController@restore')->name('admin.news.restore');
            });

            // ELECTRONIC CONTRACT
            Route::group(['prefix' => 'e-contract'], function () {
                Route::get('/', 'EContractController@index')->name('admin.econtract');
                Route::get('/get-data', 'EContractController@get_data')->name('admin.econtract.get_data');
                Route::get('/edit/{id}', 'EContractController@edit')->name('admin.econtract.edit');
                Route::post('/update/{id}', 'EContractController@update')->name('admin.econtract.update');
            });

            // BANNER POPUP
            Route::group(['prefix' => 'banner-popup/{position}'], function () {
                Route::get('/', 'BannerPopupController@index')->name('admin.banner_popup');
                Route::get('/get-data', 'BannerPopupController@get_data')->name('admin.banner_popup.get_data');
                Route::get('/create', 'BannerPopupController@create')->name('admin.banner_popup.create');
                Route::post('/store', 'BannerPopupController@store')->name('admin.banner_popup.store');
                Route::get('/edit/{id}', 'BannerPopupController@edit')->name('admin.banner_popup.edit');
                Route::post('/update/{id}', 'BannerPopupController@update')->name('admin.banner_popup.update');
                Route::post('/delete', 'BannerPopupController@delete')->name('admin.banner_popup.delete');
                Route::get('/deleted-data', 'BannerPopupController@deleted_data')->name('admin.banner_popup.deleted_data');
                Route::get('/get-deleted-data', 'BannerPopupController@get_deleted_data')->name('admin.banner_popup.get_deleted_data');
                Route::post('/restore', 'BannerPopupController@restore')->name('admin.banner_popup.restore');
                Route::post('/show-popup', 'BannerPopupController@show_popup')->name('admin.banner_popup.show_popup');
            });

            // BLOG
            Route::group(['prefix' => 'blog'], function () {
                Route::get('/', 'BlogController@index')->name('admin.blog');
                Route::get('/get-data', 'BlogController@get_data')->name('admin.blog.get_data');
                Route::get('/create', 'BlogController@create')->name('admin.blog.create');
                Route::post('/store', 'BlogController@store')->name('admin.blog.store');
                Route::get('/edit/{id}', 'BlogController@edit')->name('admin.blog.edit');
                Route::post('/update/{id}', 'BlogController@update')->name('admin.blog.update');
                Route::post('/delete', 'BlogController@delete')->name('admin.blog.delete');
                Route::get('/deleted-data', 'BlogController@deleted_data')->name('admin.blog.deleted_data');
                Route::get('/get-deleted-data', 'BlogController@get_deleted_data')->name('admin.blog.get_deleted_data');
                Route::post('/restore', 'BlogController@restore')->name('admin.blog.restore');
            });

            // VOUCHER
            Route::group(['prefix' => 'voucher'], function () {
                Route::get('/', 'VoucherController@index')->name('admin.voucher');
                Route::get('/get-data', 'VoucherController@get_data')->name('admin.voucher.get_data');
                Route::get('/create', 'VoucherController@create')->name('admin.voucher.create');
                Route::post('/store', 'VoucherController@store')->name('admin.voucher.store');
                Route::get('/edit/{id}', 'VoucherController@edit')->name('admin.voucher.edit');
                Route::post('/update/{id}', 'VoucherController@update')->name('admin.voucher.update');
                Route::post('/delete', 'VoucherController@delete')->name('admin.voucher.delete');
                Route::get('/deleted-data', 'VoucherController@deleted_data')->name('admin.voucher.deleted_data');
                Route::get('/get-deleted-data', 'VoucherController@get_deleted_data')->name('admin.voucher.get_deleted_data');
                Route::post('/restore', 'VoucherController@restore')->name('admin.voucher.restore');
            });

            // INVOICE
            Route::group(['prefix' => 'invoice'], function () {
                Route::get('/', 'InvoiceController@index')->name('admin.invoice');
                Route::get('/get-data', 'InvoiceController@get_data')->name('admin.invoice.get_data');
                Route::get('/detail/{id}', 'InvoiceController@detail')->name('admin.invoice.detail');
                Route::get('/get-data-detail/{id}', 'InvoiceController@get_data_detail')->name('admin.invoice.get_data_detail');
                Route::get('/export', 'InvoiceController@export')->name('admin.invoice.export');
            });

            // BLOG CATEGORY
            Route::group(['prefix' => 'blog-category'], function () {
                Route::get('/', 'BlogCategoryController@index')->name('admin.blog_category');
                Route::get('/get-data', 'BlogCategoryController@get_data')->name('admin.blog_category.get_data');
                Route::get('/create', 'BlogCategoryController@create')->name('admin.blog_category.create');
                Route::post('/store', 'BlogCategoryController@store')->name('admin.blog_category.store');
                Route::get('/edit/{id}', 'BlogCategoryController@edit')->name('admin.blog_category.edit');
                Route::post('/update/{id}', 'BlogCategoryController@update')->name('admin.blog_category.update');
                Route::post('/delete', 'BlogCategoryController@delete')->name('admin.blog_category.delete');
                Route::get('/deleted-data', 'BlogCategoryController@deleted_data')->name('admin.blog_category.deleted_data');
                Route::get('/get-deleted-data', 'BlogCategoryController@get_deleted_data')->name('admin.blog_category.get_deleted_data');
                Route::post('/restore', 'BlogCategoryController@restore')->name('admin.blog_category.restore');
                Route::post('/sorting', 'BlogCategoryController@sorting')->name('admin.blog_category.sorting');
            });

            // BLOG SUBSCRIPTION
            Route::group(['prefix' => 'blog-subscription'], function () {
                Route::get('/', 'BlogSubscriptionController@index')->name('admin.blog_subscription');
                Route::get('/get-data', 'BlogSubscriptionController@get_data')->name('admin.blog_subscription.get_data');
                Route::get('/export', 'BlogSubscriptionController@export')->name('admin.blog_subscription.export');
            });
        });
    }
});

// HELPER
Route::group(['prefix' => 'helper'], function () {
    Route::post('/filter-district', 'HelperController@filter_district')->name('helper.filter_district');
    Route::post('/filter-sub-district', 'HelperController@filter_sub_district')->name('helper.filter_sub_district');
    Route::post('/filter-village', 'HelperController@filter_village')->name('helper.filter_village');
    Route::post('/filter-postal-code', 'HelperController@filter_postal_code')->name('helper.filter_postal_code');
});

// CRON
Route::group(['prefix' => 'cron'], function () {
    Route::get('/reminder', 'CronController@reminder');
});

// DEVELOPMENT TESTER
Route::group(['prefix' => 'dev'], function () {
    // SANDBOX
    Route::get('/', 'DevController@sandbox');

    // PHPINFO
    Route::get('/phpinfo', function () {
        return phpinfo();
    })->name('dev.phpinfo');

    // SAMPLE STRUCTURE OF NAV MENU
    Route::get('/nav-menu-structure', 'DevController@nav_menu_structure')->name('dev.nav_menu');

    // CUSTOM PAGES
    Route::get('/custom-pages/{name}', 'DevController@custom_pages')->name('dev.custom_pages');

    // NEED AUTH
    Route::group(['middleware' => 'auth.admin'], function () {
        // CHEATSHEET FORM
        Route::get('/cheatsheet-form', 'DevController@cheatsheet_form')->name('dev.cheatsheet_form');

        // CRYPT TOOLS
        Route::match(['get', 'post'], '/encrypt', 'DevController@encrypt')->name('dev.encrypt');
        Route::match(['get', 'post'], '/decrypt', 'DevController@decrypt')->name('dev.decrypt');

        // EMAIL
        Route::group(['prefix' => 'email'], function () {
            // Send Email using SMTP - sample: "{URL}/dev/email?send=true&email=username@domain.com"
            // Preview Email - sample: "{URL}/dev/email"
            Route::get('/', 'DevController@email_send');
        });
    });

    // PRODUCT MIGRATION TO NEW STRUCTURE
    Route::get('/product-migration', 'DevController@product_migration')->name('dev.product_migration');

    // INVOICE MIGRATION
    Route::get('/order-migration', 'DevController@order_migration')->name('dev.order_migration');

    // ORDINAL MIGRATION
    Route::get('/ordinal-migration', 'DevController@ordinal_migration')->name('dev.ordinal_migration');
});

Route::get('/{slug}', 'Web\SiteController@page')->name('web.page');
