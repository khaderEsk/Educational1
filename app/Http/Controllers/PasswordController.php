<?php

namespace App\Http\Controllers;

use App\Http\Requests\CodeRequest;
use App\Http\Requests\EmailRequest;
use App\Http\Requests\PasswordNewRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Jobs\DeleteCodeJob;
use App\Mail\ForgetPasswordMail;
use App\Models\User;
use App\Traits\GeneralTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Facades\JWTAuth;

class PasswordController extends Controller
{
    use GeneralTrait;

    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            $user = auth()->user();
            if (Hash::check($request->old_password, $user->password)) {
                $user->update([
                    'password' => $request->password,
                ]);
                return $this->returnSuccessMessage(__('backend.operation completed successfully', [], app()->getLocale()));
            } else {
                return $this->returnError("400",'failed');
            }
        } catch (\Exception $ex) {
            return $this->returnError($ex->getCode(),'Please try again later');
        }
    }


    public function forgetPassword(EmailRequest $request)
    {
        try {
            $user =User::where('email',$request->email)->first();
            if($user) {
                $code = mt_rand(100000, 999999);
                $user->update([
                    'code' => $code,
                ]);
                $mailData = [
                    'title' => 'Forget Password Email',
                    'code' => $code
                ];

//                ForgetPasswordJob::dispatch($mailData,$user)->delay(Carbon::now()->addSeconds(2));
                Mail::to($user->email)->send(new ForgetPasswordMail($mailData));
                DeleteCodeJob::dispatch($user)->delay(Carbon::now()->addMinutes(6));
                return $this->returnSuccessMessage(__('backend.operation completed successfully', [], app()->getLocale()));
            }
            else
            {
                return $this->returnError('404', __('backend.The Email Not Found', [], app()->getLocale()));
            }

        } catch (\Exception $ex) {
            return $this->returnError("500",'Please try again later');
        }
    }


    public function checkCode(CodeRequest $request)
    {
        try {
            $code = $request->code;

            $user = User::where('email',$request->email)->first();
            if(!$user)
                return $this->returnError('404', __('backend.The Email Not Found', [], app()->getLocale()));
            if (!$user->code)
                return $this->returnError("401", __('backend.Please request the code again', [], app()->getLocale()));

            if ($user->code != $code)
                return $this->returnError("400", __('backend.The entered verification code is incorrect', [], app()->getLocale()));

            return $this->returnSuccessMessage(__('backend.operation completed successfully', [], app()->getLocale()));


        } catch (\Exception $ex) {
            return $this->returnError("500", 'Please try again later');
        }
    }


    public function passwordNew(PasswordNewRequest $request)
    {
        try {

            $user = User::where('email', $request->email)->first();
            if (!$user)
                return $this->returnError('404', __('backend.The Email Not Found', [], app()->getLocale()));

            $user->update([
                'password' => $request->password,
            ]);

            $token = JWTAuth::fromUser($user);
            if (!$token) return $this->returnError('401', 'Unauthorized');

            $user->token = $token;
            return $this->returnData($user, __('backend.Logged in successfully', [], app()->getLocale()));

        } catch (\Exception $ex) {
            return $this->returnError("500", 'Please try again later');
        }
    }
}
