<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;

class ComingSoon
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
        $launching_datetime = env('LAUNCHING_DATETIME');
        $comingsoon = false;
        if (!empty($launching_datetime) && date('Y-m-d H:i:s') < $launching_datetime) {
            $comingsoon = true;
        }

        if ($comingsoon && !Session::has(env('SESSION_ADMIN_NAME', 'sysadmin'))) {
            // tampilkan landing page coming soon
            return redirect()->route('web.comingsoon');
        } else {
            // normal
            return $next($request);
        }
    }
}
