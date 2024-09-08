<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TeacherMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {

            if (Auth::user()->role_id == 'teacher') {

                return $next($request);
            } else {
                return response()->json(['massage' => 'Access Denied as you are not Login Teacher'], 403);
            }
        } else {
            return response()->json(['massage' => 'Access Denied as you are not Login Teacher'], 403);
        }
        return $next($request);
    }
}
