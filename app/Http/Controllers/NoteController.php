<?php

namespace App\Http\Controllers;

use App\Models\HistoryLockHours;
use App\Models\Note;
use App\Models\ProfileStudent;
use Illuminate\Http\Request;
use App\Traits\GeneralTrait;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\NoteRequest;
use App\Models\User;

class NoteController extends Controller
{
    use GeneralTrait;


    public function index()
    {
        try {
            $profile_teacher=auth()->user()->profile_teacher()->first();
            $users_name=HistoryLockHours::where('idProfileTeacher',$profile_teacher->id)->pluck('nameStudent');
            $profile_students=[];
            if(count($users_name)>0) {
                $profile_students = ProfileStudent::whereHas('user', function ($query) use ($users_name) {
                    $query->whereIn('name', $users_name)
                    ->whereDoesntHave('block');
                })
                    ->with(['user'=> function ($query){
                        $query->select('id','name');
                    },'note_as_student' => function ($query) use ($profile_teacher) {
                        $query->where('profile_teacher_id', $profile_teacher->id);
                    }])
                    ->get();
            }

            return $this->returnData($profile_students, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            return $this->returnError("500",$ex->getMessage());
        }
    }


    public function store(NoteRequest $request)
    {
        try {
            DB::beginTransaction();

            $user = auth()->user()->profile_teacher()->first();

            $student = ProfileStudent::find($request->student_id);
            if (!$student)
                return $this->returnError(404, 'Profile Student Id Not Found');

            $exists=HistoryLockHours::where('nameStudent',$student->user->name)->exists();
            if(!$exists)
                return $this->returnError("403",__('backend.You Can’t do it', [], app()->getLocale()));

            $note = $user->note_as_teacher()->firstOrCreate([
                'profile_student_id' => $request->student_id
            ]);
            $note->update(['note'=>$request->note]);

            DB::commit();
            return $this->returnData($note, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", $ex->getMessage());
        }
    }


    public function show(Note $note)
    {
        //
    }

    public function update(Request $request, Note $note)
    {
        //
    }


    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $user = auth()->user()->profile_teacher()->first();
            $note = $user->note_as_teacher()->find($id);
            if (!$note)
                return $this->returnError("404", 'note not found');
            $note->delete();

            DB::commit();
            return $this->returnSuccessMessage(__('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", 'Please try again later');
        }
    }
}
