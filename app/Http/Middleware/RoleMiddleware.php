<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Traits\GeneralTrait;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Spatie\Permission\Contracts\Role;

class RoleMiddleware
{
    Use GeneralTrait;


    public function handle(Request $request, Closure $next,$role)
    {
        try {

            $user=Auth::user();

            $roles = is_array($role)
                ? $role
                : explode('|', $role);

            foreach ($roles as $role) {
                if($user->hasRole($role))
                    return $next($request);
            }


        }catch (\Exception $e){
            return $this->returnError('512', "you dont have the right role");
        }
        return $this->returnError('512', "you dont have the right role");
    }
}
