<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\LockHourRequest;
use App\Http\Requests\LockHourRequestStore;
use App\Jobs\LockHourJob;
use App\Jobs\NotificationJobProfile;
use App\Jobs\NotificationJobUser;
use App\Models\CalendarHour;
use App\Models\FinancialReport;
use App\Models\HistoryLockHours;
use App\Models\LockHour;
use App\Models\ProfitRatio;
use App\Models\ServiceTeacher;
use App\Models\User;
use App\Traits\GeneralTrait;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request as FacadesRequest;

class LockHourController extends Controller
{
    use GeneralTrait;


    public function index()
    {
        try {
            $user = Auth::user();
            $teacher = $user->profile_teacher;
            if (!$teacher) {
                return $this->returnError(400, 'Token is Invalid');
            }
            $lock_hour = DB::table('calendar_days')
                ->where('calendar_days.teacher_id', '=', $teacher->id)
                ->join('calendar_hours', 'calendar_days.id', '=', 'calendar_hours.day_id')
                ->join('lock_hours', 'calendar_hours.id', '=', 'lock_hours.hour_id')
                ->join('service_teachers', 'service_teachers.id', '=', 'lock_hours.service_id')
                ->where('lock_hours.status', '=', 0)
                ->join('profile_students', 'profile_students.id', '=', 'lock_hours.student_id')
                ->join('users', 'users.id', '=', 'profile_students.user_id')
                ->select(
                    'lock_hours.id',
                    'users.name',
                    'users.address',
                    'users.governorate',
                    'lock_hours.date',
                    'calendar_days.day',
                    'calendar_hours.hour',
                    'service_teachers.type',
                    'lock_hours.appointmentAddress'
                )
                ->get();
            return $this->returnData($lock_hour, 'operation completed successfully');
        } catch (\Exception $ex) {
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }


    public function get_all_accept_request()
    {
        try {
            $user = Auth::user();
            $teacher = $user->profile_teacher;
            if (!$teacher) {
                return $this->returnError(400, 'Token is Invalid');
            }
            $lock_hour = DB::table('calendar_days')
                ->where('calendar_days.teacher_id', '=', $teacher->id)
                ->join('calendar_hours', 'calendar_days.id', '=', 'calendar_hours.day_id')
                ->join('lock_hours', 'calendar_hours.id', '=', 'lock_hours.hour_id')
                ->join('service_teachers', 'service_teachers.id', '=', 'lock_hours.service_id')
                ->where('lock_hours.status', '=', 1)
                ->join('profile_students', 'profile_students.id', '=', 'lock_hours.student_id')
                ->join('users', 'users.id', '=', 'profile_students.user_id')
                ->select(
                    'lock_hours.id',
                    'users.name',
                    'users.address',
                    'users.governorate',
                    'lock_hours.date',
                    'calendar_hours.hour',
                    'calendar_days.day',
                    'lock_hours.date',
                    'service_teachers.type',
                    'lock_hours.appointmentAddress'
                )
                ->get();
            return $this->returnData($lock_hour, 'operation completed successfully');
        } catch (\Exception $ex) {
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }

    public function store(LockHourRequestStore $request)
    {
        try {
            $user = Auth::user();
            $student = $user->profile_student;
            $wallet = $user->wallet;

            if (!$student) {
                return $this->returnError(400, 'Token is Invalid');
            }

            $existingLock = $student->hour_lock()
                ->where('service_id', $request->service_id)
                ->where('hour_id', $request->hour_id)
                ->where('date', $request->date)
                ->first();


            if ($existingLock) {
                return $this->returnError(400, __('backend.already request hour lock', [], app()->getLocale()));
            }
            $hours = CalendarHour::find($request->hour_id)->day->teacher->service_teachers;
            if (!CalendarHour::find($request->hour_id)) {
                return $this->returnError(404, 'The Hour Not Found');
            }
            foreach ($hours as $hour) {
                if ($hour->id == $request->service_id) {
                    if ($wallet->value < $hour->price) {
                        return $this->returnError(501, __('backend.not Enough money in wallet', [], app()->getLocale()));
                    }
                    $lock = $student->hour_lock()->create([
                        'hour_id' => $request->hour_id,
                        'service_id' => $request->service_id,
                        'date' => $request->date,
                        'value' => $hour->price,
                        'status' => 0,
                        'appointmentAddress' => $request->appointmentAddress ? $request->appointmentAddress : null
                    ]);
                    $lock->save();
                    if ($hour->type == 'private lesson') {
                        $wallet->update([
                            'value' => $wallet->value  - (10 / 100) * $hour->price,
                        ]);
                    } elseif ($hour->type == 'video call') {
                        $wallet->update([
                            'value' => $wallet->value  - $hour->price,
                        ]);
                    }

                    $service=ServiceTeacher::find($request->service_id);

                    NotificationJobProfile::dispatch($service->profile_teacher, 'طلب حجز موعد', $user->name . ' تم طلب حجز موعد من قبل ')->delay(Carbon::now()->addSeconds(2));

                    return $this->returnData(200, __('backend.operation completed successfully', [], app()->getLocale()));
                }
            }

            return $this->returnError(404, 'The service Not Found');
        } catch (\Exception $ex) {
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $lockHour = LockHour::find($id);
            $teacher = auth()->user()->profile_teacher;
            if (!$lockHour) {
                return $this->returnError(400, 'not found request');
            }
            if ($lockHour->status == 1) {
                return $this->returnError(400, __("backend.can not delete because the request is accept", [], app()->getLocale()));
            }
            if (!$teacher) {
                return $this->returnError(400, 'Token is Invalid');
            }
            $user = $lockHour->student->user->wallet;
            $wallet = $user->update([
                'value' => $user->value + (10 / 100) * $lockHour->value,
            ]);
            $timeOnly = Carbon::now()->setTimezone('Asia/Damascus')->toDateString();
            $historyLock = HistoryLockHours::create([
                'type' => $lockHour->service->type,
                'nameStudent' => $lockHour->student->user->name,
                'idProfileTeacher' => $user->id,
                'hour' => $lockHour->hour->hour,
                'dateAccept' => $timeOnly,
                'date' => $lockHour->date,
                'day' => $lockHour->hour->day->day,
                'price' => $lockHour->value,
                'status' => "UnAcceptable",
                'case' => $request->case,
            ]);
            $lockHour->delete();

            NotificationJobProfile::dispatch($lockHour->student, 'تم الرفض', ' تم رفض طلب حجز الموعد الخاص بك ')->delay(Carbon::now()->addSeconds(2));
            return $this->returnData($wallet, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }
    public function get_my_request()
    {
        try {
            $user = Auth::user()->profile_student;
            if (!$user) {
                return $this->returnError(400, 'Token is Invalid');
            }
            $lock_hour = DB::table('lock_hours')
                ->where('lock_hours.student_id', '=', $user->id)
                ->join('service_teachers', 'service_teachers.id', '=', 'lock_hours.service_id')
                ->join('calendar_hours', 'calendar_hours.id', '=', 'lock_hours.hour_id')
                ->join('calendar_days', 'calendar_days.id', '=', 'calendar_hours.day_id')
                ->join('profile_teachers', 'profile_teachers.id', '=', 'calendar_days.teacher_id')
                ->join('users', 'users.id', '=', 'profile_teachers.user_id')
                ->select(
                    'service_teachers.type',
                    'lock_hours.value',
                    'lock_hours.date',
                    'calendar_hours.hour',
                    'calendar_days.day',
                    'users.name',
                    'users.address',
                    'users.governorate',
                    'lock_hours.appointmentAddress'
                )
                ->get();
            return $this->returnData($lock_hour, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }

    public function accept_request($id)
    {
        try {
            $user = Auth::user()->profile_teacher;
            if (!$user) {
                return $this->returnError(400, 'Token is Invalid');
            }
            $lock_hour = LockHour::find($id);
            if (!$lock_hour) {
                return $this->returnError(404, 'Not found Request');
            }
            if ($lock_hour->status == 1) {
                return $this->returnError(501, 'The Request is accept');
            }

            $admin = User::whereHas('roles', function ($query) {
                $query->where('name', 'admin');
            })->first();
            $admin->load('wallet');
            $profit = ProfitRatio::first();
            if ($lock_hour->service->type == 'video call') {
                $profit = ProfitRatio::where('type', 'video call')->first();
                $admin->wallet->update([
                    'value' => $admin->wallet->value + $lock_hour->value * ($profit->value / 100)
                ]);
            } else {
                $profit = ProfitRatio::where('type', 'private lesson')->first();
                $admin->wallet->update([
                    'value' => $admin->wallet->value + $lock_hour->value * ($profit->value / 100)
                ]);
            }
            $deleteWallets = LockHour::where('id', '!=', $id)
                ->where('date', '=', $lock_hour->date)
                ->get();

            $timeOnly = Carbon::now()->setTimezone('Asia/Damascus')->toDateString();
            if ($deleteWallets) {
                foreach ($deleteWallets as $deleteWallet) {
                    $service = $deleteWallet->load('service');
                    if ($service->service->type == 'private lesson') {
                        $wallet = $deleteWallet->load('student.user.wallet');
                        $wallet->student->user->wallet->update([
                            'value' => $wallet->student->user->wallet->value + (10 / 100) * $deleteWallet->value
                        ]);
                    } else {
                        $wallet = $deleteWallet->load('student.user.wallet');
                        $wallet->student->user->wallet->update([
                            'value' => $wallet->student->user->wallet->value + $deleteWallet->value
                        ]);
                    }
                }
            }

            $historyLock = HistoryLockHours::create([
                'type' => $lock_hour->service->type,
                'nameStudent' => $lock_hour->student->user->name,
                'idProfileTeacher' => $user->id,
                'hour' => $lock_hour->hour->hour,
                'dateAccept' => $timeOnly,
                'date' => $lock_hour->date,
                'day' => $lock_hour->hour->day->day,
                'price' => $lock_hour->value,
                'status' => "acceptable",
            ]);
            $lock_hour->update([
                'status' => 1
            ]);
            $wallet = Auth::user()->wallet;
            if ($lock_hour->service->type == 'video call') {
                $wallet->update([
                    'value' => $wallet->value + $lock_hour->value
                ]);
            } elseif ($lock_hour->service->type == 'private lesson') {
                $wallet->update([
                    'value' => $wallet->value + (10 / 100) * $lock_hour->value
                ]);
            }
            $lock_hour->hour->update([
                'status' => 1
            ]);
            $financialReport = FinancialReport::create([
                'type' => $lock_hour->service->type,
                'teacherName' =>  Auth::user()->name,
                'value' => $lock_hour->value,
                'ProfitAmount' => $lock_hour->value * ($profit->value / 100),
                'profitRatio' => $profit->value
            ]);

            NotificationJobProfile::dispatch($lock_hour->student, 'تم الموافقة', 'تم الموافقة على طلب حجز الموعد الخاص بك')->delay(Carbon::now()->addSeconds(2));
            return $this->returnData(200, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }

    public function delete_my_request($id)
    {
        try {
            $user = Auth::user()->profile_student;
            $wallet = Auth::user()->wallet;
            if (!$user) {
                return $this->returnError(400, 'Token is Invalid');
            }
            $lock_hour = LockHour::find($id);
            if (!$lock_hour) {
                return $this->returnError(404, 'Not found Request');
            }
            if ($lock_hour->status == 1) {
                return $this->returnError(500, __("backend.can not delete Request", [], app()->getLocale()));
            }
            if ($lock_hour->service->type == 'video call') {
                $wallet->update([
                    'value' => $wallet->value + $lock_hour->value
                ]);
            } elseif ($lock_hour->service->type == 'private lesson') {
                $wallet->update([
                    'value' => $wallet->value + (10 / 100) * $lock_hour->value
                ]);
            }
            $lock_hour->delete();
            return $this->returnData(200, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }
    public function get_my_accept_request()
    {
        try {
            $user = Auth::user()->profile_student;
            if (!$user) {
                return $this->returnError(400, 'Token is Invalid');
            }
            $lock_hour = DB::table('lock_hours')
                ->where('lock_hours.student_id', '=', $user->id)
                ->join('calendar_hours', 'calendar_hours.id', '=', 'lock_hours.hour_id')
                ->join('calendar_days', 'calendar_days.id', '=', 'calendar_hours.day_id')
                ->join('service_teachers', 'service_teachers.id', '=', 'lock_hours.service_id')
                ->join('profile_teachers', 'profile_teachers.id', '=', 'calendar_days.teacher_id')
                ->join('users', 'users.id', '=', 'profile_teachers.user_id')
                ->where('lock_hours.status', '=', 1)
                ->select(
                    'lock_hours.id',
                    'users.name',
                    'users.address',
                    'users.governorate',
                    'lock_hours.date',
                    'calendar_days.day',
                    'calendar_hours.hour',
                    'service_teachers.type',
                    'lock_hours.value',
                    'lock_hours.appointmentAddress'
                )
                ->get();
            return $this->returnData($lock_hour, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }
    public function getDate($id)
    {
        $hour = CalendarHour::find($id);
        return $hour->day->day;
    }
}
