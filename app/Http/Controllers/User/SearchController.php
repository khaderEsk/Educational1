<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Ads;
use App\Models\CompleteTeacher;
use App\Models\EmployeeReport;
use App\Models\FinancialReport;
use App\Models\ProfileStudent;
use App\Models\ProfileTeacher;
use App\Models\QualificationCourse;
use App\Models\Report;
use App\Models\TeachingMethod;
use App\Models\User;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{

    use GeneralTrait;
    public function searchFinancialReport(Request $request)
    {
        $searchTerm = $request->input('search');

        $searchTerms = explode(' ', $searchTerm);
        $financialReportsQuery = FinancialReport::query();
        foreach ($searchTerms as $term) {
            $financialReportsQuery->where(function ($query) use ($term) {
                $query->where('teacherName', 'LIKE', '%' . $term . '%')
                    ->orWhere('type', 'LIKE', '%' . $term . '%')
                    ->orWhere('value', 'LIKE', '%' . $term . '%')
                    ->orWhere('ProfitAmount', 'LIKE', '%' . $term . '%')
                    ->orWhere('profitRatio', 'LIKE', '%' . $term . '%');
            });
        }
        $financialReports = $financialReportsQuery->get();

        return $this->returnData($financialReports, 200);
    }


    public function searchReport(Request $request)
    {
        $searchTerm = $request->input('search');

        $searchTerms = explode(' ', $searchTerm);
        $reportsQuery = EmployeeReport::query();
        foreach ($searchTerms as $term) {
            $reportsQuery->where(function ($query) use ($term) {
                $query->where('nameEmployee', 'LIKE', '%' . $term . '%')
                    ->orWhere('operation', 'LIKE', '%' . $term . '%')
                    ->orWhere('name', 'LIKE', '%' . $term . '%')
                    ->orWhere('nameColumn', 'LIKE', '%' . $term . '%');
            });
        }
        $reports = $reportsQuery->get();

        return $this->returnData($reports, 200);
    }


    public function searchCourse(Request $request)
    {
        $searchTerm = $request->input('search');

        $searchTerms = explode(' ', $searchTerm);
        $coursesQuery = QualificationCourse::query();
        foreach ($searchTerms as $term) {
            $coursesQuery->where(function ($query) use ($term) {
                $query->where('name', 'LIKE', '%' . $term . '%')
                    ->orWhere('place', 'LIKE', '%' . $term . '%')
                    ->orWhere('price', 'LIKE', '%' . $term . '%')
                    ->orWhere('description', 'LIKE', '%' . $term . '%')
                    ->orWhere('date', 'LIKE', '%' . $term . '%');
            });
        }
        $courses = $coursesQuery->get();

        return $this->returnData($courses, 200);
    }

    public function acceptTeachers(Request $request)
    {
        try {
            DB::beginTransaction();

            $search=$request->search;

            $profile_teacher = ProfileTeacher::where('status',1)
                ->with(['user','domains'])
                ->whereDoesntHave('user.block')
                ->where('jurisdiction', 'like', '%' . $search . '%')
                ->orWhereHas('user',function ($query) use ($search){
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('governorate', 'like', '%' . $search . '%');
                })
                ->get();

            DB::commit();
            return $this->returnData($profile_teacher, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", 'Please try again later');
        }
    }

    public function students(Request $request)
    {
        try {
            DB::beginTransaction();

            $search=$request->search;

            $profile_student = ProfileStudent::with('user')
            ->whereDoesntHave('user.block')
                ->where('educational_level', 'like', '%' . $search . '%')
                ->orWhereHas('user',function ($query) use ($search){
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('governorate', 'like', '%' . $search . '%');
                })
                ->get();

            DB::commit();
            return $this->returnData($profile_student, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", 'Please try again later');
        }
    }

    public function employees(Request $request)
    {
        try {

            $search=$request->search;

            $data = User::with('roles')
                ->whereHas('roles', function ($query) {
                    $query->where('id', 4);
                })
                ->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('address', 'like', '%' . $search . '%')
                        ->orWhere('governorate', 'like', '%' . $search . '%');
                })
                ->get()
                ->map(function ($user) {
                    $user->is_blocked = $user->isBlocked();
                    return $user;
                });
            return $this->returnData($data, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            return $this->returnError($ex->getCode(),'Please try again later');
        }
    }

    public function completeRequest(Request $request)
    {
        try {
            $search=$request->search;
            $requestCompletes = CompleteTeacher::where('status', 0)->with([
                'teacher' => function ($q) {
                    $q->select('id', 'user_id', 'jurisdiction');
                },
                'teacher.user' => function ($q) {
                    $q->select('id', 'name', 'address');
                }
            ])
                ->where(function ($query) use ($search) {
                    $query->orWhereHas('teacher', function ($q) use ($search) {
                        $q->where('jurisdiction', 'like', '%' . $search . '%');
                    })
                        ->orWhereHas('teacher.user', function ($q) use ($search) {
                            $q->where('name', 'like', '%' . $search . '%')
                                ->orWhere('governorate', 'like', '%' . $search . '%');
                        });
                })
                ->get();

            DB::commit();
            return $this->returnData($requestCompletes, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }

    public function ads(Request $request)
    {
        try {
            $search=$request->search;

            $ads = Ads::join('profile_teachers', 'ads.profile_teacher_id', '=', 'profile_teachers.id')
                ->join('users', 'profile_teachers.user_id', '=', 'users.id')
                ->select('ads.*', 'users.name')
                ->where('users.name', 'like', '%' . $search . '%')
                ->orWhere('title', 'like', '%' . $search . '%')
                ->orWhere('price','like', '%' . $search . '%')
                ->orWhere('place','like', '%' . $search . '%')
                ->get();

            return $this->returnData($ads, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            return $this->returnError("500", "Please try again later");
        }
    }

    public function teachingMethods(Request $request)
    {
        try {
            $search=$request->search;

            $teaching_methods = TeachingMethod::whereDoesntHave('series')->join('profile_teachers', 'teaching_methods.profile_teacher_id', '=', 'profile_teachers.id')
                ->join('users', 'profile_teachers.user_id', '=', 'users.id')
                ->select('teaching_methods.*', 'users.name')->orderBy('created_at', 'desc')
                ->where('users.name', 'like', '%' . $search . '%')
                ->orWhere('title', 'like', '%' . $search . '%')
                ->orWhere('price','like', '%' . $search . '%')
                ->get();

            return $this->returnData($teaching_methods, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            return $this->returnError("500", "Please try again later");
        }
    }

    public function reports(Request $request)
    {
        try {
            DB::beginTransaction();

            $search=$request->search;
            $reports = Report::with(['reporter'=>function($query){
                $query->select('id','user_id')
                ->with([
                    'user:id,name'
                ]);
            },'reported'=>function($query){
                $query->select('id','user_id')
                ->with([
                    'user:id,name'
                ]);
            }])
                ->where('reason', 'like', '%' . $search . '%')
                ->orwhereHas('reporter.user',function($query) use ($search)
            {
                $query->where('name', 'like', '%' . $search . '%');
            })
                ->orwhereHas('reported.user',function($query) use ($search)
                {
                    $query->where('name', 'like', '%' . $search . '%');
                })
                ->orderBy('created_at','desc')->get();

            DB::commit();
            return $this->returnData($reports, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", $ex->getMessage());
        }
    }

    public function request_join(Request $request)
    {
        try {
            DB::beginTransaction();
            $search=$request->search;
            $teacher = ProfileTeacher::with('user')->with('domains')->where('status', 0)
                ->where('jurisdiction', 'like', '%' . $search . '%')
                ->orwhereHas('user',function($query) use ($search){
                    $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('governorate', 'like', '%' . $search . '%');
                })->get();
            DB::commit();
            return $this->returnData($teacher, 200);
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }

    }

    public function teachingMethodsSeries(Request $request)
    {
        try {
            $search=$request->search;

            $teaching_methods = TeachingMethod::whereHas('series')->join('profile_teachers', 'teaching_methods.profile_teacher_id', '=', 'profile_teachers.id')
                ->join('users', 'profile_teachers.user_id', '=', 'users.id')
                ->select('teaching_methods.*', 'users.name')->orderBy('created_at', 'desc')
                ->where('users.name', 'like', '%' . $search . '%')
                ->orWhere('title', 'like', '%' . $search . '%')
                ->orWhere('price','like', '%' . $search . '%')
                ->get();

            return $this->returnData($teaching_methods, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            return $this->returnError("500", "Please try again later");
        }
    }

    public function getSeriesForTeachingA($id,Request $request)
    {
        try {
            $search=$request->search;

            $teaching_method =TeachingMethod::find($id);
            if(!$teaching_method)
                return $this->returnError("404",'Not found');
            $teaching_method->with('series',function($query) use($search){
                $query->where('title','like', '%' . $search . '%');
            });

            return $this->returnData($teaching_method, __('backend.operation completed successfully', [], app()->getLocale()));

        } catch (\Exception $ex) {
            return $this->returnError("500", $ex->getMessage());
        }
    }


}
