<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateTeachingMethodRequest;
use App\Jobs\financialReportJob;
use App\Models\ProfileTeacher;
use App\Models\ProfitRatio;
use App\Models\TeachingMethod;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\TeachingMethodRequest;
use App\Traits\GeneralTrait;
use Illuminate\Support\Facades\DB;

class TeachingMethodController extends Controller
{
    use GeneralTrait;

    private $uploadPath = "assets/images/teaching_methods";


    public function index($teacher_id,Request $request)
    {
        try {
            $profile_teacher = ProfileTeacher::find($teacher_id);
            if (!$profile_teacher) {
                return $this->returnError("404", 'Profile Teacher Not found');
            }
            $teaching_methods = $profile_teacher->teaching_methods()->whereDoesntHave('profile_teacher.user.block')->whereDoesntHave('series')->orderBy('created_at', 'desc')->filter($request)->get();
            return $this->returnData($teaching_methods, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            return $this->returnError("500", $ex->getMessage());
        }
    }



    public function show($id)
    {
        try {
            DB::beginTransaction();

            $teaching_method = TeachingMethod::find($id);
            if (!$teaching_method) {
                return $this->returnError("404", 'teaching_method Not found');
            }
            $teaching_method->increment('views');

            DB::commit();
            return $this->returnData($teaching_method, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", 'Please try again later');
        }
    }



    public function store(TeachingMethodRequest $request)
    {
        try {
            DB::beginTransaction();
            $user=auth()->user();
            $profile_teacher = $user->profile_teacher()->first();

            $file = $this->saveImage($request->file, $this->uploadPath);

            $teaching_method = $profile_teacher->teaching_methods()->create([
                'title' => $request->title,
                'type' => $request->type,
                'description' => $request->description,
                'file' => $file,
                'status' => $request->status,
                'price' => $request->price
            ]);
            $profit = ProfitRatio::where('type', 'file')->first();

            if ($user->wallet->value < $profit->value*$request->price)
                return $this->returnError("402", __('backend.not Enough money in wallet', [], app()->getLocale()));

            financialReportJob::dispatch('file',$request->price,$user)->delay(Carbon::now()->addSeconds(1));

            DB::commit();
            return $this->returnData($teaching_method, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", $ex->getMessage());
        }
    }




    public function update(UpdateTeachingMethodRequest $request, $id)
    {
        try {
            DB::beginTransaction();

            $profile_teacher = auth()->user()->profile_teacher()->first();

            $teaching_method = $profile_teacher->teaching_methods()->find($id);
            if (!$teaching_method)
                return $this->returnError("404", 'teaching_method Not found');

            $file = null;
            if (isset($request->file)) {
                $this->deleteImage($teaching_method->file);
                $file = $this->saveImage($request->file, $this->uploadPath);
            }

            $teaching_method->update([
                'title' => isset($request->title) ? $request->title : $teaching_method->title,
                'type' => isset($request->type) ? $request->type : $teaching_method->type,
                'description' => isset($request->description) ? $request->description : $teaching_method->description,
                'file' => isset($request->file) ? $file : $teaching_method->file,
                'status' => isset($request->status) ? $request->status : $teaching_method->status,
                'price' => isset($request->price) ? $request->price : $teaching_method->price,
            ]);

            DB::commit();
            return $this->returnData($teaching_method, __('backend.operation completed successfully', [], app()->getLocale()));
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
            $teaching_method = $profile_teacher->teaching_methods()->find($id);

            if (!$teaching_method) {
                return $this->returnError("404", 'teaching_method Not found');
            }
            $this->deleteImage($teaching_method->file);

            $teaching_method->delete();

            DB::commit();
            return $this->returnSuccessMessage(__('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", 'Please try again later');
        }
    }

    public function getMyTeachingMethod()
    {
        try {
            $profile_teacher = auth()->user()->profile_teacher()->first();
            $teaching_methods = [];
            if ($profile_teacher)
                $teaching_methods = $profile_teacher->teaching_methods()->whereDoesntHave('series')->orderBy('created_at', 'desc')->get();

            return $this->returnData($teaching_methods, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            return $this->returnError("500", "Please try again later");
        }
    }

    public function getAll(Request $request)
    {
        try {
            $teaching_methods = TeachingMethod::whereDoesntHave('series')->join('profile_teachers', 'teaching_methods.profile_teacher_id', '=', 'profile_teachers.id')->join('users', 'profile_teachers.user_id', '=', 'users.id')
                ->select('teaching_methods.*', 'users.name')->orderBy('created_at', 'desc')
                ->filter($request)->get();

            return $this->returnData($teaching_methods, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            return $this->returnError("500", "Please try again later");
        }
    }


    public function deleteForAdmin($id)
    {
        try {
            DB::beginTransaction();
            $teaching_method = TeachingMethod::find($id);

            if (!$teaching_method) {
                return $this->returnError("404", 'teaching_method Not found');
            }
            $this->deleteImage($teaching_method->file);

            $teaching_method->delete();

            DB::commit();
            return $this->returnSuccessMessage(__('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", 'Please try again later');
        }
    }
}
