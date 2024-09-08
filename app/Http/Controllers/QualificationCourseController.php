<?php

namespace App\Http\Controllers;

use App\Http\Requests\QualificationCourseRequestUpdate;
use App\Models\QualificationCourse;
use Illuminate\Http\Request;
use App\Traits\GeneralTrait;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\QualificationCourseRequest;
use App\Models\QualificationUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class QualificationCourseController extends Controller
{
    use GeneralTrait;

    public function index()
    {
        try {
            $qualificationCourses = QualificationCourse::withCount('user')->get();
            return $this->returnData($qualificationCourses, 200);
        } catch (\Exception $ex) {
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */

    /**
     * Store a newly created resource in storage.
     */
    public function store(QualificationCourseRequest $request)
    {
        try {
            DB::beginTransaction();
            $qualification_course = QualificationCourse::create([
                'name' => $request->name,
                'description' => $request->description,
                'date' => $request->date,
                'end_date' => $request->end_date,
                'count_subscribers' => $request->count_subscribers,
                'price' => $request->price,
                'place' => $request->place,
                'status' => 0
            ]);
            DB::commit();
            return $this->returnData($qualification_course, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            DB::beginTransaction();
            $QualificationCourses = QualificationCourse::with('user')->find($id);
            if (!$QualificationCourses)
                return $this->returnError(404, 'Not found Qualification Course');
            DB::commit();
            return $this->returnData($QualificationCourses, 200);
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }
    public function show_my_courses()
    {
        try {
            DB::beginTransaction();
            $user = Auth::user();
            $courses = $user->qualification_users;
            DB::commit();
            return $this->returnData($courses, 200);
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }

    public function insert_into_courses($id)
    {
        try {
            DB::beginTransaction();
            $user = auth();
            $QualificationCourse = QualificationCourse::find($id);
            $userId = Auth::user();
            $now = Carbon::now();
            $qualificationUser = QualificationUser::where('qualification_id', '=', $id)
                ->where('user_id', '=', $userId->id)->first();
            $countUser = QualificationUser::where('qualification_id', '=', $id)->count();
            if (!$QualificationCourse)
                return $this->returnError(404, 'Not found Qualification Course');
            if ($QualificationCourse->date <= now()) {
                return $this->returnError(501, __('backend.The course has begun', [], app()->getLocale()));
            }
            if ($qualificationUser) {
                return $this->returnError(500, __('backend.already insert', [], app()->getLocale()));
            }
            if ($countUser >= $QualificationCourse->count_subscribers) {
                return $this->returnError(401, __('backend.The number is complete', [], app()->getLocale()));
            }
            if ($user->user()->wallet->value < $QualificationCourse->price)
                return $this->returnError(500, __('backend.not Enough money in wallet', [], app()->getLocale()));
            $user->user()->wallet->update([
                'value' => $user->user()->wallet->value - $QualificationCourse->price
            ]);
            $user->user()->wallet->save();
            $user->user()->qualification_users()->attach($id, ['created_at' => $now]);
            DB::commit();
            return $this->returnData('successfully', 200);
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(QualificationCourseRequestUpdate $request, $id)
    {
        try {
            DB::beginTransaction();
            $qualification_course = QualificationCourse::find($id);
            if (!$qualification_course)
                return $this->returnError("404", 'not found');
            $qualification_course->update([
                'name' => isset($request->name) ? $request->name : $qualification_course->name,
                'description' => isset($request->description) ? $request->description : $qualification_course->description,
                'date' => isset($request->date) ? $request->date : $qualification_course->date,
                'count_subscribers' => isset($request->count_subscribers) ?
                    $request->count_subscribers : $qualification_course->count_subscribers,
                'price' => isset($request->price) ? $request->price : $qualification_course->price,
            ]);
            DB::commit();
            return $this->returnData($qualification_course, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), 'Please try again later');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $course = QualificationCourse::find($id);
            if (!$course) {
                DB::rollback();
                return $this->returnError(404, 'not found');
            }
            $numberOfUsers = $course->user()->count();
            if ($numberOfUsers != 0) {
                DB::rollback();
                return $this->returnError(501, __('backend.cant delete course because There are users who have joined the course', [], app()->getLocale()));
            }
            $course->delete();
            DB::commit();
            return $this->returnSuccessMessage(__('backend.operation completed successfully', [], app()->getLocale()), 200);
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), 'Please try again later');
        }
    }
}
