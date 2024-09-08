<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserMiddleware
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
        if (Auth::check()) {

            if (Auth::user()->role_id == 'student') {

                return $next($request);
            } else {
                return response()->json(['massage' => 'Access Denied as you are not Login User'], 403);
            }
        } else {
            return response()->json(['massage' => 'Access Denied as you are not Login User'], 403);
        }

        return $next($request);
    }
}
