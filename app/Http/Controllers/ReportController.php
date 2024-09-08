<?php

namespace App\Http\Controllers;

use App\Models\HistoryLockHours;
use App\Models\ProfileStudent;
use App\Models\ProfileTeacher;
use App\Models\Report;
use Illuminate\Http\Request;
use App\Traits\GeneralTrait;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ReportRequest;
use Carbon\Carbon;

class ReportController extends Controller
{
    use GeneralTrait;

    public function index()
    {
        try {
            DB::beginTransaction();
            $reports = Report::with(['reporter'=>function($query){
                $query->select('id','user_id');
                $query->with([
                    'user:id,name'
                ]);
            },'reported'=>function($query){
                $query->select('id','user_id');
                $query->with([
                    'user:id,name'
                ]);
            }])->orderBy('created_at','desc')->get();

            DB::commit();
            return $this->returnData($reports, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", $ex->getMessage());
        }
    }


    public function report_student(ReportRequest $request)
    {
        try {
            DB::beginTransaction();

            $user = auth()->user();
            $profile_teacher=$user->profile_teacher()->first();

            $profile_student = ProfileStudent::find($request->reported_id);
            if (!$profile_student) {
                return $this->returnError("404", 'Not found' . ' Profile student Id : ' . $request->reported_id);
            }
            $is_lock=HistoryLockHours::where('nameStudent',$profile_student->user->name)->where('idProfileTeacher',$profile_teacher->id)->exists();
            if(!$is_lock)
                return $this->returnError("403",__('backend.You Can’t do it because there\'s no appointment between you.', [], app()->getLocale()));
            $report = $profile_teacher->report_as_reporter()->firstOrCreate(
                ['reported_id' =>  $request->reported_id],
                ['reported_type' => "App\Models\ProfileStudent"]
            );
            $report->update([
                'reason' => $request->reason,
                'date'=>Carbon::now()->format('Y-m-d H:i:s')
            ]);
            $profile_teacher->loadMissing(['report_as_reporter']);

            DB::commit();
            return $this->returnData($profile_teacher, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", $ex->getMessage());
        }
    }



    public function report_teacher(ReportRequest $request)
    {
        try {
            DB::beginTransaction();

            $user = auth()->user();
            $profile_student=$user->profile_student()->first();

            $reported_id=$request->reported_id;
            $profile_teacher = ProfileTeacher::find($reported_id);
            if (!$profile_teacher) {
                return $this->returnError("404",'Not found' . ' Profile Teacher Id : ' . $reported_id);
            }

            $is_lock=HistoryLockHours::where('idProfileTeacher',$profile_teacher->id)->where('nameStudent',$user->name)->exists();
            if(!$is_lock)
                return $this->returnError("403",__('backend.You Can’t do it because there\'s no appointment between you.', [], app()->getLocale()));

            $report = $profile_student->report_as_reporter()->firstOrCreate(
                ['reported_id' =>  $request->reported_id],
                ['reported_type' => "App\Models\ProfileTeacher"]
            );
            $report->update([
                'reason' => $request->reason,
                'date'=>Carbon::now()->format('Y-m-d H:i:s')
            ]);

            $profile_student->loadMissing('report_as_reporter');

            DB::commit();
            return $this->returnData($profile_student, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", $ex->getMessage());
        }
    }



}
