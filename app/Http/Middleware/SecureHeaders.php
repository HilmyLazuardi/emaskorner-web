<?php

namespace App\Http\Middleware;

use Closure;

class SecureHeaders
{
    // Enumerate headers which you do not want in your application's responses.
    // Great starting point would be to go check out @Scott_Helme's:
    // https://securityheaders.com/

    private $unwantedHeaderList = [
        'X-Powered-By',
        'Server',
    ];
    public function handle($request, Closure $next)
    {
        $secure_mode = env('APP_SECURE', false);
        $strict_mode = env('APP_SECURE_STRICT', false);

        if ($secure_mode) {
            $this->removeUnwantedHeaders($this->unwantedHeaderList);

            $response = $next($request);
            $response->headers->set('Referrer-Policy', 'same-origin');
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->headers->set('X-XSS-Protection', '1; mode=block');
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

            if ($strict_mode) {
                // Register your CSS links
                $response->headers->set('Content-Security-Policy', "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com");

                // Register your script links
                $response->headers->set('Content-Security-Policy', "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.datatables.net https://cdnjs.cloudflare.com");
            } else {
                $response->headers->set('Content-Security-Policy', "upgrade-insecure-requests");
            }
        } else {
            $response = $next($request);
        }

        return $response;
    }

    private function removeUnwantedHeaders($headerList)
    {
        foreach ($headerList as $header) {
            header_remove($header);
        }
    }
}
