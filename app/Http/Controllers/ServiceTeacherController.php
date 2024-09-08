<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateServiceTeacherRequest;
use App\Models\ProfileTeacher;
use App\Models\ServiceTeacher;
use Illuminate\Http\Request;
use App\Traits\GeneralTrait;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ServiceTeacherRequest;
use Carbon\Carbon;

class ServiceTeacherController extends Controller
{
    use GeneralTrait;


    public function index($teacher_id)
    {
        try {
            $profile_teacher = ProfileTeacher::find($teacher_id);
            if (!$profile_teacher) {
                return $this->returnError("404", 'Profile Teacher Not found');
            }
            $service_teachers = $profile_teacher->service_teachers()->orderBy('created_at', 'desc')->get();
            return $this->returnData($service_teachers, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            return $this->returnError("500", "Please try again later");
        }
    }



    public function store(ServiceTeacherRequest $request)
    {
        try {
            DB::beginTransaction();

            $profile_teacher = auth()->user()->profile_teacher()->first();

            $services = $request->services;
            $list_services = [];
            foreach ($services as $value) {
                $service = [
                    'profile_teacher_id' => $profile_teacher->id,
                    'price' => $value['price'],
                    'type' => $value['type'],
                    'created_at' => date('Y-m-d H:i:s')
                ];
                array_push($list_services, $service);
            }
            ServiceTeacher::insert($list_services);


            DB::commit();
            return $this->returnData($list_services, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", $ex->getMessage());
        }
    }



    public function show($id)
    {
        try {
            DB::beginTransaction();

            $ServiceTeacher = ServiceTeacher::find($id);
            if (!$ServiceTeacher) {
                return $this->returnError("404", 'ServiceTeacher Not found');
            }

            DB::commit();
            return $this->returnData($ServiceTeacher, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", 'Please try again later');
        }
    }



    public function update(UpdateServiceTeacherRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            $profile_teacher = auth()->user()->profile_teacher()->first();

            $service_teacher = $profile_teacher->service_teachers()->find($id);

            if (!$service_teacher)
                return $this->returnError("404", 'not found');

            $service_teacher->update([
                'price' => isset($request->price) ? $request->price : $service_teacher->price,
                'type' => isset($request->type) ? $request->type : $service_teacher->type,
            ]);


            DB::commit();
            return $this->returnData($service_teacher, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", 'Please try again later');
        }
    }

    public function destroy($id)
    {
        try {
            $profile_teacher = auth()->user()->profile_teacher()->first();
            $service_teacher = $profile_teacher->service_teachers()->find($id);

            if (!$service_teacher) {
                return $this->returnError(404, 'not found');
            }
            $timeNow = Carbon::now()->setTimezone('Asia/Damascus')->toDateString();
            $services_lock = $service_teacher->hour_lock()->orderBy('status')->get();
            foreach ($services_lock as $service_lock) {
                if ($service_lock->status == 0) {
                    $value = 0;
                    if ($service_teacher->type == 'private lesson') {
                        $value = ($service_lock->value * 10) / 100;
                    } elseif ($service_teacher->type == 'video call') {
                        $value = $service_lock->value;
                    } else {
                        return $this->returnError(500, 'Unknown service type');
                    }

                    $wallet = $service_lock->student->user->wallet;

                    if ($wallet) {
                        $updateResult = $wallet->update([
                            'value' => $wallet->value + $value
                        ]);
                        // return $wallet;
                        if (!$updateResult) {
                            DB::rollBack();
                            return $this->returnError(500, 'Failed to update wallet');
                        }

                        $deleteResult = $service_lock->delete();
                        if (!$deleteResult) {
                            return $this->returnError(500, 'Failed to delete service lock');
                        }
                    } else {
                        return $this->returnError(404, 'Wallet not found');
                    }
                }
            }

            // التحقق من وجود مواعيد مثبتة (status == 1)
            foreach ($services_lock as $service_lock) {
                if ($service_lock->status == 1 && $timeNow <= $service_lock->date) {
                    return $this->returnError(501, "The service can't be deleted because there is an appointment booked for the service.");
                }
            }

            // حذف الخدمة بعد معالجة جميع المواعيد
            $service_teacher->delete();
            DB::commit();
            return $this->returnSuccessMessage(__('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->returnError(500, 'Please try again later');
        }
    }


    public function getMyService()
    {
        try {
            $profile_teacher = auth()->user()->profile_teacher()->first();

            $service_teachers = [];
            if ($profile_teacher)
                $service_teachers = $profile_teacher->service_teachers()->orderBy('created_at', 'desc')->get();

            return $this->returnData($service_teachers, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            return $this->returnError("500", "Please try again later");
        }
    }
}
