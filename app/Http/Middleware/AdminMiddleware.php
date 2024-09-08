<?php

namespace App\Http\Middleware;

use App\Http\Traits\GeneralTrait;
use App\Traits\GeneralTrait as TraitsGeneralTrait;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    use TraitsGeneralTrait;

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

            if (Auth::user()->role_id == 'admin') {

                return $next($request);
            } else {
                
                return $this->returnError(404,'Access Denied as you are not Admin');
                // return response()->json(['massage' => 'Access Denied as you are not Admin'], 403);
            }
        } else {
            return response()->json(['massage' => 'Access Denied as you are not Admin'], 403);
        }

        return $next($request);
    }
}
