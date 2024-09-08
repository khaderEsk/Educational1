<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAdsRequest;
use App\Jobs\DeleteAds;
use App\Jobs\EndDateAdsJob;
use App\Jobs\financialReportJob;
use App\Models\Ads;
use App\Models\ProfileTeacher;
use App\Models\ProfitRatio;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\AdsRequest;
use App\Traits\GeneralTrait;
use Illuminate\Support\Facades\DB;

class AdsController extends Controller
{
    use GeneralTrait;

    private $uploadPath = "assets/images/ads";

    public function getSimilar()
    {
        $user = auth()->user();
        $desiredData = Ads::whereRaw("INSTR(place, ?) > 0", [$user->governorate])
            ->orderBy('created_at', 'desc')->get();

        return $this->returnData($desiredData, __('backend.operation completed successfully', [], app()->getLocale()));
    }

    public function index()
    {
        try {
            $user = auth()->user();

            $ads = Ads::whereDoesntHave('profile_teacher.user.block')->orderByRaw("CASE WHEN place = '{$user->governorate}' THEN 0 ELSE 1 END, created_at DESC")
            ->join('profile_teachers', 'ads.profile_teacher_id', '=', 'profile_teachers.id')
                ->join('users', 'profile_teachers.user_id', '=', 'users.id')
                ->select('ads.*', 'users.name')
                ->get();

            return $this->returnData($ads, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            return $this->returnError("500", "Please try again later");
        }
    }

    public function getAdsTeacher($teacherId)
    {
        try {

            $profile_teacher = ProfileTeacher::find($teacherId);
            if (!$profile_teacher)
                return $this->returnError("404", "Not found");
            $ads = $profile_teacher->ads()->orderBy('created_at', 'desc')->get();

            return $this->returnData($ads, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            return $this->returnError("500", "Please try again later");
        }
    }


    public function store(AdsRequest $request)
    {
        try {
            DB::beginTransaction();
            $user = auth()->user();

            $file = null;
            $file = $this->saveImage($request->file, $this->uploadPath);

            $profile_teacher = $user->profile_teacher()->first();
            $ads = $profile_teacher->ads()->create([
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
                'number_students' => $request->number_students,
                'file' => $file,
                'place' => $request->place,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ]);
            $today = date('Y-m-d');
            $diff = (strtotime($ads->end_date) - strtotime($today)) / (60 * 60 * 24);

            $profit = ProfitRatio::where('type', 'file')->first();

            if ($user->wallet->value < $profit->value*$request->price)
                return $this->returnError("402", __('backend.not Enough money in wallet', [], app()->getLocale()));

            EndDateAdsJob::dispatch($user->id, $ads->id)->delay(Carbon::now()->addDays($diff));

            financialReportJob::dispatch('ads',$request->price,auth()->user())->delay(Carbon::now()->addSeconds(1));
            DB::commit();
            return $this->returnData($ads, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", $ex->getMessage());
        }
    }



    public function getById($id)
    {
        try {
            $data = Ads::where('id', $id)
                ->with([
                    'reservation_ads.profile_student' => function ($query) {
                        $query->select('id', 'phone', 'user_id');
                        $query->with([
                            'user:id,name,address'
                        ]);
                    }
                ,'profile_teacher'=>function($q){
                    $q->select('id','user_id','jurisdiction')
                    ->with('user:id,name');
            }])
                ->first();

            if (!$data) {
                return $this->returnError("404", "Not found");
            }
            return $this->returnData($data, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            return $this->returnError("500", 'Please try again later');
        }
    }



    public function update(UpdateAdsRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            $user = auth()->user();

            $profile_teacher = $user->profile_teacher()->first();
            $ads = $profile_teacher->ads()->find($id);

            if (!$ads)
                return $this->returnError("404", 'not found');

            $file = null;
            if (isset($request->file)) {
                $this->deleteImage($ads->file);
                $file = $this->saveImage($request->file, $this->uploadPath);
            }
            $ads->update([
                'title' => isset($request->title) ? $request->title : $ads->title,
                'description' => isset($request->description) ? $request->description : $ads->description,
                'price' => isset($request->price) ? $request->price : $ads->price,
                'number_students' => isset($request->number_students) ? $request->number_students : $ads->number_students,
                'file' => isset($request->file) ? $file : $ads->file,
                'place' => isset($request->place) ? $request->place : $ads->place,
                'start_date' => isset($request->start_date) ? $request->start_date : $ads->start_date,
                'end_date' => isset($request->end_date) ? $request->end_date : $ads->end_date,
            ]);
            if (isset($request->end_date)) {
                $today = date('Y-m-d');
                $diff = (strtotime($ads->end_date) - strtotime($today)) / (60 * 60 * 24);
                EndDateAdsJob::dispatch($user->id, $ads->id)->delay(Carbon::now()->addDays($diff));
            }

            DB::commit();
            return $this->returnData($ads, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", 'Please try again later');
        }
    }



    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $profile_teacher = auth()->user()->profile_teacher()->first();

            $ads = $profile_teacher->ads()->find($id);
            if (!$ads)
                return $this->returnError("404", 'not found');
            if (isset($ads->file)) {
                $this->deleteImage($ads->file);
            }

            DeleteAds::dispatch($id)->delay(Carbon::now()->addSeconds(2));
            DB::commit();
            return $this->returnSuccessMessage(__('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", 'Please try again later');
        }
    }

    public function getMyAds()
    {
        try {
            $profile_teacher = auth()->user()->profile_teacher()->first();

            $ads = [];
            if ($profile_teacher)
                $ads = $profile_teacher->ads()->get();

            return $this->returnData($ads, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            return $this->returnError("500", "Please try again later");
        }
    }

    public function deleteForAdmin($id)
    {
        try {
            DB::beginTransaction();

            $ads = Ads::find($id);
            if (!$ads)
                return $this->returnError("404", 'not found');
            if (isset($ads->file)) {
                $this->deleteImage($ads->file);
            }
            DeleteAds::dispatch($id)->delay(Carbon::now()->addSeconds(2));
            DB::commit();
            return $this->returnSuccessMessage(__('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", 'Please try again later');
        }
    }
}
