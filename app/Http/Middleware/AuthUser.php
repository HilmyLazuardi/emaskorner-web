<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;

class AuthUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Session::has('buyer')) {
            return $next($request);
        } else {
            // get actual link
            $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

            // store redirect uri to session
            Session::put('redirect_uri', $actual_link);

            // if actual link is homepage, then redirect to login page
            if ($actual_link == route('web.home')) {
                return redirect()->route('web.auth.login');
            }

            // jika ingin buat order tp blm login
            $current_route_name = \Illuminate\Support\Facades\Route::currentRouteName();
            if ($current_route_name == 'web.order.summary') {
                Session::put('redirect_uri', url()->previous());
            }

            // redirect to login page with warning message
            return redirect()->route('web.auth.login')->with('error', 'Silahkan login terlebih dahulu.');
        }
    }
}
