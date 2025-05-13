<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;

class AuthAdmin
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
        if (Session::has(env('SESSION_ADMIN_NAME', 'sysadmin'))) {
            return $next($request);
        } else {
            // get actual link
            $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

            // validate actual link is AJAX URL
            if (strpos($actual_link, '/get-data') !== false || strpos($actual_link, '/store') !== false || strpos($actual_link, '/update') !== false) {
                // if it is AJAX URL, then set redirect uri to admin homepage
                $actual_link = route('admin.home');
            }

            // store redirect uri to session
            Session::put('redirect_uri_admin', $actual_link);

            // if actual link is admin homepage, then redirect to admin login page
            if ($actual_link == route('admin.home')) {
                return redirect()->route('admin.login');
            }

            // redirect to admin login page with warning message
            return redirect()->route('admin.login')->with('warning', lang('You must login first!'));
        }
    }
}
