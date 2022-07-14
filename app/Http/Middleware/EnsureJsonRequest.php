<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureJsonRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if( $request->header('Content-Type') != 'application/json' ) {
            return response()->json([
                'success' => false,
                'message' => 'Only accepted `application/json`'
            ], 400);
        }
        return $next($request);
    }
}
