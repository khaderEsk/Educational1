<?php

namespace App\Http\Middleware;

use App\Traits\GeneralTrait;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HasProfileTeacherMiddleware
{
    use GeneralTrait;

    public function handle(Request $request, Closure $next): Response
    {

        try {
            $user = auth()->user();
            $profile_teacher=$user->profile_teacher()->where('status',1)->first();
            if (!$profile_teacher) {
                return $this->returnError('511', "Please complete all personal information");
            }
            else{
                return $next($request);
            }

        }catch (\Exception $e) {
            return $this->returnError('511', 'Unauthorized User');
        }
    }
}
