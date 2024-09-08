<?php

namespace App\Http\Controllers;

use App\Http\Requests\CodeRequest;
use App\Http\Requests\LoginRequest;
use App\Jobs\DeleteCodeJob;
use App\Models\User;
use App\Traits\GeneralTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Mail;
use App\Mail\CodeEmail;


class AdminAuthController extends Controller
{
    use GeneralTrait;

    public function login_admin(LoginRequest $request)
    {
        $credentials = $request->only(['email', 'password']);
        $token = JWTAuth::attempt($credentials);
        $exist=User::where('email',$request->email)->whereHas('roles',function ($q){
            $q->where('id',3)->orWhere('id',4);
        })->first();
        if($exist && !$token)
            return $this->returnError(401,__('backend.The password is wrong', [], app()->getLocale()));

        if (!$token)
            return $this->returnError(401,__('backend.Account Not found', [], app()->getLocale()));
        if (isset($exist->block))
            return $this->returnError(401,__('backend.You are block', [], app()->getLocale()));
        //$code=mt_rand(100000, 999999);
        $exist->update([
            'code' => 444444,
        ]);
        $mailData = [

            'title' => 'Code login',

            'code' =>444444,

        ];

        Mail::to($exist->email)->send(new CodeEmail($mailData));
        DeleteCodeJob::dispatch($exist)->delay(Carbon::now()->addMinutes(6));
        return $this->returnSuccessMessage(__('backend.code send successfully', [], app()->getLocale()));
    }


    public function codeAdmin(CodeRequest $request)
    {
        try {
            $code = $request->code;

            $user = User::where('email', $request->email)->first();
            if (!$user)
                return $this->returnError('404', __('backend.The Email Not Found', [], app()->getLocale()));

            if (!$user->code)
                return $this->returnError("401", __('backend.Please request the code again', [], app()->getLocale()));

            if ($user->code != $code)
                return $this->returnError("400", __('backend.The entered verification code is incorrect', [], app()->getLocale()));

            $token = JWTAuth::fromUser($user);
            if (!$token) return $this->returnError('402', 'Unauthorized');
            $user->token=$token;
            $user->loadMissing(['roles']);

            return $this->returnData($user, __('backend.operation completed successfully', [], app()->getLocale()));


        } catch (\Exception $ex) {
            return $this->returnError("500", 'Please try again later');
        }

    }


}
