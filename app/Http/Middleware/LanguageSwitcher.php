<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class LanguageSwitcher
{

    public function handle(Request $request, Closure $next): Response
    {
        App::setLocale($request->hasHeader("lang") ?  $request->header("lang") : Config::get('app.locale'));
        return $next($request);
    }
}
