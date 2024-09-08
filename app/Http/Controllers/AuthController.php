<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Wallet;
use App\Traits\GeneralTrait;
use Illuminate\Support\Facades\Http;
use Tymon\JWTAuth\Facades\JWTAuth;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    use GeneralTrait;

    private $uploadPath = "assets/images/users";


    public function login(LoginRequest $request)
    {
        $credentials = $request->only(['email', 'password']);
        $token = JWTAuth::attempt($credentials);

        $exist=User::where('email',$request->email)->first();
        if($exist && !$token)
            return $this->returnError(401,__('backend.The password is wrong', [], app()->getLocale()));

        if (!$token)
            return $this->returnError(401,__('backend.Account Not found', [], app()->getLocale()));

        if (isset($exist->block))
            return $this->returnError(401,__('backend.You are block', [], app()->getLocale()));

        $user = auth()->user();
        $user->token = $token;
        $user->loadMissing(['roles']);

        return $this->returnData($user, __('backend.operation completed successfully', [], app()->getLocale()));
    }



    public function register(RegisterRequest $request)
    {

        $user = User::create([
            'name'           => $request->name,
            'email'          => $request->email,
            'password'       => $request->password,
            'address'         => $request->address,
            'governorate'    => $request->governorate,
            'birth_date'     => $request->birth_date
        ]);

        $credentials = ['email' => $user->email, 'password' => $request->password];
        $token = JWTAuth::attempt($credentials);
        $user->token = $token;

        $role = Role::where('id', '=', $request->role_id)->first();
        if(!$role)
            return $this->returnError(404,'Role Not found');
        $user->assignRole($role);
        $user->loadMissing(['roles']);
        if (!$token)
            return $this->returnError(401, 'Unauthorized');
        $wallet = Wallet::create([
            'user_id' => $user->id,
            'number' => random_int(1000000000000, 9000000000000),
            'value' => 0,
        ]);
        return $this->returnData($user, __('backend.operation completed successfully', [], app()->getLocale()));
    }


    public function logout(Request $request)
    {
        $token = $request->bearerToken();
        if ($token) {
            try {
                JWTAuth::setToken($token)->invalidate();
                return $this->returnSuccessMessage("Logged out successfully", "200");
            } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
                return $this->returnError($e->getCode(), 'some thing went wrongs');
            }
        } else {
            return $this->returnError("400", 'some thing went wrongs');
        }
    }


    public function deleteMyAccount()
    {
        try {
            $user = auth()->user();
            if($user->image)
                $this->deleteImage($user->image);
            $user->delete();

        } catch (\Exception $ex) {
            return $this->returnError("500",'Please try again later');
        }
    }

    public function refreshToken(Request $request)
    {
        try {
            $user_id = $request->user_id;
            $fcm_token = $request->fcm_token;
            $user = User::find($user_id);
            if(!$user)
                return $this->returnError('404', 'Not found');
            $user->update([
                'fcm_token' => $fcm_token
            ]);

            return $this->returnData($user, __('backend.operation completed successfully', [], app()->getLocale()));
        }
        catch (\Exception $e)
        {
            return $this->returnError("500",'Please try again later');
        }
    }

    public function test()
    {
        $user=auth()->user();
        //dispatch(new SendFcmNotification($user->id,"message","title"));
//        $SERVER_KEY=env('FCM_SERVER_KEY');
//        $fcm=Http::acceptJson()->withToken($SERVER_KEY)
//            ->post('https://fcm.googleapis.com/fcm/send',
//                [
//                    'to'=>$user->fcm_token,
//                    'notification'=>
//                        [
//                            'title'=>"title",
//                            'body'=>"message"
//                        ]
//                ]);
        $fcm=$this->send($user,"title","message",'basic');
        return $fcm;
        //return $this->returnSuccessMessage('operation completed successfully');
    }

}
