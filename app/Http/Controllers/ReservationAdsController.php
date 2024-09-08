<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileStudentAdsRequest;
use App\Jobs\EndAdsJob;
use App\Jobs\NotificationJobProfile;
use App\Jobs\NotificationJobUser;
use App\Models\Ads;
use App\Traits\GeneralTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationAdsController extends Controller
{
    use GeneralTrait;


    public function getMyAds()
    {

        try {
            $profile_student=auth()->user()->profile_student()->first();
            $reservation_ads=[];
            if($profile_student) {
                $reservation_ads = $profile_student->reservation_ads()->orderBy('created_at','desc')->get();
                if (count($reservation_ads) > 0)
                    $reservation_ads->loadMissing('ads');
            }

            return $this->returnData($reservation_ads, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            return $this->returnError("500",$ex->getMessage());
        }
    }


    public function store(ProfileStudentAdsRequest $request)
    {
        try {
            DB::beginTransaction();
            $user=auth()->user();

            $profile_student=$user->profile_student()->first();

            $ads=Ads::find($request->ads_id);


            if(!$ads)
                return $this->returnError("404", 'Ads not found');
            $is_exist=$profile_student->reservation_ads()->where('ads_id',$request->ads_id)->first();
            if($is_exist)
                return $this->returnError("400", __('backend.ads already exist', [], app()->getLocale()));
            if ($ads->start_date <= now()) {
                return $this->returnError("400", __('backend.ads has begun', [], app()->getLocale()));
            }

            if ($ads->number_students ==0) {
                return $this->returnError("401", __('backend.The number is complete', [], app()->getLocale()));
            }

            if ($user->wallet->value < $ads->price)
                return $this->returnError("402", __('backend.not Enough money in wallet', [], app()->getLocale()));
            $user->wallet->update([
                'value' => $user->wallet->value - $ads->price
            ]);
            $reservation_ads=$profile_student->reservation_ads()->create([
                'ads_id'=>$request->ads_id,
                'reserved_at'=>Carbon::now()->format('Y-m-d H:i:s')
            ]);
            $ads->decrement('number_students');
            if($ads->number_students==0) {
                EndAdsJob::dispatch($ads)->delay(Carbon::now()->addSeconds(2));
            }
            NotificationJobProfile::dispatch($ads->profile_teacher,'تم التسجيل في الدورة', $user->name.' تم التسجيل في الدورة الخاصة بك من قبل ')->delay(Carbon::now()->addSeconds(2));
            DB::commit();
            return $this->returnData($reservation_ads, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", $ex->getMessage());
        }
    }


    public function show($id)
    {
        try {
            $profile_student=auth()->user()->profile_student()->first();
            if($profile_student) {
                $reservation_ads = $profile_student->reservation_ads()->where('id', $id)->first();
                if (!$reservation_ads)
                    return $this->returnError("404", 'not found');
                $reservation_ads->loadMissing('ads');
            }
            return $this->returnData($reservation_ads, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            return $this->returnError("500",$ex->getMessage());
        }
    }



    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $user=auth()->user();
            $profile_student= $user->profile_student()->first();
            if($profile_student) {
                $reservation_ads = $profile_student->reservation_ads()->where('id', $id)->first();
                if (!$reservation_ads)
                    return $this->returnError("404", 'not found');
                $reservation_ads->ads->increment('number_students');
                $reservation_ads->delete();
                $wallet=$user->wallet;
                $wallet->update(['value'=>$wallet->value+$reservation_ads->ads->price]);
            }

            DB::commit();
            return $this->returnSuccessMessage(__('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", $ex->getMessage());
        }
    }
}
