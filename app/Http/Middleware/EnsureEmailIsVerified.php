<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If user is not verified
        if ($request->user() && ! $request->user()->hasVerifiedEmail()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Your email address is not verified.'
            ], 403);
        }

        return $next($request);
    }
}
