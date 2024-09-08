<?php

namespace App\Http\Controllers;

use App\Models\Evaluation;
use App\Models\HistoryLockHours;
use App\Models\ProfileTeacher;
use Illuminate\Http\Request;
use App\Traits\GeneralTrait;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\EvaluationRequest;

class EvaluationController extends Controller
{
    use GeneralTrait;



    public function store(EvaluationRequest $request)
    {

        try {
            DB::beginTransaction();

            $profile_student=auth()->user()->profile_student()->first();

            $teacher=ProfileTeacher::find($request->teacher_id);
            if(!$teacher)
                return $this->returnError("404", 'teacher not found');

            $is_lock=HistoryLockHours::where('nameStudent',$profile_student->user->name)->where('idProfileTeacher',$teacher->id)->first();
            if(!$is_lock)
                return $this->returnError("403",__('backend.You Can’t do it', [], app()->getLocale()));

            $evaluation = Evaluation::firstOrCreate(
                ['profile_teacher_id' =>  $request->teacher_id],
                ['profile_student_id' => $profile_student->id]
            );
            $evaluation->update(['rate' => $request->rate]);
            DB::commit();
            return $this->returnData($evaluation, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", 'Please try again later');
        }
    }



    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $user=auth()->user()->profile_student()->first();
            $evaluation=$user->evaluation_as_student()->find($id);
            if(!$evaluation)
                return $this->returnError("404", 'not found');
            $evaluation->delete();

            DB::commit();
            return $this->returnSuccessMessage(__('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", 'Please try again later');
        }
    }
}
