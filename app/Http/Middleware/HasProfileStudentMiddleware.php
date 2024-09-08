<?php

namespace App\Http\Middleware;

use App\Traits\GeneralTrait;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HasProfileStudentMiddleware
{
    use GeneralTrait;

    public function handle(Request $request, Closure $next): Response
    {

        try {
            $user = auth()->user();
            $profile_student=$user->profile_student()->first();
            if (!$profile_student) {
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
