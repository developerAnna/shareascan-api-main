<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetAccessTokenAsAuthorization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->hasHeader('X-Access-Token')) {
            // Set the Authorization header to use the Bearer token
            $request->headers->set('Authorization', 'Bearer ' . $request->header('X-Access-Token'));
        }

        // Continue with the next middleware
        return $next($request);
    }
}
