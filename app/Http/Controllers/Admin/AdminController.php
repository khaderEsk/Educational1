<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\NotificationJobProfile;
use App\Models\Ads;
use App\Models\Block;
use App\Models\EmployeeReport;
use App\Models\HistoryLockHours;
use App\Models\ProfileStudent;
use App\Models\ProfileTeacher;
use App\Models\QualificationCourse;
use App\Models\RejectRequest;
use App\Models\TeachingMethod;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\GeneralTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminController extends Controller
{

    use GeneralTrait;
    public function index()
    {
        try {
            DB::beginTransaction();
            $teacher = ProfileTeacher::with('user')->with('domains')->where('status', 0)->get();
            DB::commit();
            return $this->returnData($teacher, 200);
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $cases = $request->input('case');
            $teacher = ProfileTeacher::find($id);
            if (!$teacher) {
                return $this->returnError(404, 'not Found teacher');
            }
            if ($teacher->status == 1) {
                return $this->returnError(500, 'The teacher is accept');
            }

            $user = Auth::user();
            $EmployeeReport = EmployeeReport::create([
                'nameEmployee' => $user->name,
                'operation' => "destroy Teacher",
                'name' => 'Teacher',
                'nameColumn' => $teacher->user->name,
            ]);
            $reject_requests = RejectRequest::create([
                'name' => $teacher->user->name,
                'case' => $cases,
                'type' => 'join Request'
            ]);
            $teacher->user()->delete();
            $teacher->delete();
            DB::commit();
            return $this->returnData($msg = "delete successfully", 200);
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }

    public function accept_request_teacher($id)
    {
        try {
            DB::beginTransaction();
            $teacher = ProfileTeacher::find($id);
            if (!$teacher) {
                return $this->returnError(404, 'not Found teacher');
            }
            if ($teacher->status == 1) {
                return $this->returnError(500, 'The teacher is accept');
            }
            $teacher->update([
                'status' => 1
            ]);
            $teacher->save();

            $user = Auth::user();
            $EmployeeReport = EmployeeReport::create([
                'nameEmployee' => $user->name,
                'operation' => "قبول طلب انضمام استاذ",
                'name' => $teacher->user->name,
                'nameColumn' => 'استاذ',
            ]);
            $EmployeeReport->save();
            NotificationJobProfile::dispatch($teacher, 'تم الموافقة', 'تم الموافقة على طلبك')->delay(Carbon::now()->addSeconds(2));

            DB::commit();
            return $this->returnData($msg = "accept request successfully", 200);
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }

    public function get_all_teacher_unblock(Request $request)
    {
        try {
            DB::beginTransaction();
            $teacher = ProfileTeacher::filter($request)->with('user')
                ->whereHas('user', function ($query) {
                    $query->whereDoesntHave('block');
                })
                ->with('domains')
                ->with('user.wallet')
                ->where('status', 1)
                ->get();

            DB::commit();
            return $this->returnData($teacher, 200);
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }

    public function get_all_teacher_block(Request $request)
    {
        try {
            DB::beginTransaction();
            $teacher = User::filter($request)->whereHas('block')
                ->whereHas('profile_teacher')
                ->with('profile_teacher')
                ->with('wallet')
                ->get();
            DB::commit();
            return $this->returnData($teacher, 200);
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }

    public function get_all_teacher(Request $request)
    {
        try {
            DB::beginTransaction();
            $teachers = ProfileTeacher::filter($request)->with('user')->with('domains')
                ->with('user.wallet')
                ->where('status', 1)->get()->map(function ($profileTeacher) {
                    $profileTeacher->is_blocked = $profileTeacher->user->isBlocked() ;
                    return $profileTeacher;
                });
            DB::commit();
            return $this->returnData($teachers, 200);
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }

    public function allPercentage()
    {
        try {
            DB::beginTransaction();
            $totalUsers = User::count();

            $studentCount = User::whereHas('roles', function ($query) {
                $query->where('name', 'student');
            })->whereDoesntHave('block')->count();
            $studentPercentage = ($studentCount / $totalUsers) * 100;

            $blockedStudentsCount = User::whereHas('roles', function ($query) {
                $query->where('name', 'student');
            })->whereHas('block')->count();
            $blockedStudentPercentage = ($blockedStudentsCount / $totalUsers) * 100;

            ///////////
            $blockedTeachersCount = User::whereHas('roles', function ($query) {
                $query->where('name', 'student');
            })->whereHas('block')->count();
            $blockedTeacherPercentage = ($blockedTeachersCount / $totalUsers) * 100;

            $teacherCount = User::whereHas('roles', function ($query) {
                $query->where('name', 'teacher');
            })->whereDoesntHave('block')->count();
            $teacherPercentage = ($teacherCount / $totalUsers) * 100;
            $totalTeachers = User::whereHas('roles', function ($q) {
                $q->where('name', "teacher");
            })->whereHas('profile_teacher', function ($qu) {
                $qu->where('status', 1);
            })->count();
            $unblockTeachers = User::whereHas('roles', function ($q) {
                $q->where('name', "teacher");
            })->whereHas('profile_teacher', function ($qu) {
                $qu->where('status', 1);
            })->whereDoesntHave('block')->count();

            $blockTeachers = User::whereHas('roles', function ($q) {
                $q->where('name', "teacher");
            })->whereHas('profile_teacher', function ($qu) {
                $qu->where('status', 1);
            })->whereHas('block')->count();
            $teaching_methods = TeachingMethod::where('type', 'Video')->count();
            $teaching_methods_free = TeachingMethod::where('type', 'Video')->where('price', 0)->count();

            $teaching_methods_pdf = TeachingMethod::where('type', 'PDF file')->count();
            $teaching_methods_free_pdf = TeachingMethod::where('type', 'PDF file')->where('price', 0)->count();

            $courses = QualificationCourse::count();
            $ads_free = Ads::where('price', 0)->count();
            $ads = Ads::count();

            ///////////

            $wallet = Auth::user()->wallet->value;

            $totalStudents = User::whereHas('roles', function ($q) {
                $q->where('name', "student");
            })->whereHas('profile_student')->count();

            $unblockStudents = User::whereHas('roles', function ($q) {
                $q->where('name', "student");
            })->whereHas('profile_student')
                ->whereDoesntHave('block')->count();

            $blockStudents = User::whereHas('roles', function ($q) {
                $q->where('name', "student");
            })->whereHas('profile_student')
                ->whereHas('block')->count();

            $employees = User::whereHas('roles', function ($q) {
                $q->where('name', "employee");
            })->count();

            $blockEmployees = User::whereHas('roles', function ($q) {
                $q->where('name', "employee");
            })->whereHas('block')->count();

            $unBlockEmployees = User::whereHas('roles', function ($q) {
                $q->where('name', "employee");
            })->whereDoesntHave('block')->count();
            $employeesCount = User::whereHas('roles', function ($query) {
                $query->where('name', 'employee');
            })->whereDoesntHave('block')->count();
            $employeesPercentage = ($employeesCount / $totalUsers) * 100;
            $video = HistoryLockHours::where('type', 'video call')->count();
            $pre = [
                'total_teachers' => $totalTeachers,
                'unblock_teachers' => $unblockTeachers,
                'block_teachers' => $blockTeachers,
                'teacherPercentage' => $teacherPercentage,
                'blockedTeacherPercentage' => $blockedTeacherPercentage,
                'teaching_methods_video' => $teaching_methods,
                'teaching_methods_free_video' => $teaching_methods_free,
                'teaching_methods_pdf' => $teaching_methods_pdf,
                'teaching_methods_free_pdf' => $teaching_methods_free_pdf,

                'total_students' => $totalStudents,
                'unblock_students' => $unblockStudents,
                'block_students' => $blockStudents,
                'studentPercentage' => $studentPercentage,
                'blockedStudentPercentage' => $blockedStudentPercentage,

                'employees' => $employees,
                'unblock_employees' => $unBlockEmployees,
                'block_employees' => $blockEmployees,
                'employeesPercentage' => $employeesPercentage,

                'courses' => $courses,
                'ads' => $ads,
                'ads_free' => $ads_free,
                'video_call' => $video,
                'Profits' => $wallet
            ];
            DB::commit();
            return $this->returnData($pre, 200);
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }

    //Student
    public function get_all_student_unblock(Request $request)
    {
        try {
            DB::beginTransaction();
            $users = ProfileStudent::filter($request)->with('user')
                ->whereHas('user', function ($query) {
                    $query->whereDoesntHave('block');
                })
                ->with('user.wallet')
                ->get();
            DB::commit();
            return $this->returnData($users, 200);
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }
    public function get_all_student_block(Request $request)
    {
        try {
            DB::beginTransaction();
            $users = User::filter($request)->whereHas('block')
                ->whereHas('profile_student')
                ->with('profile_student')
                ->with('wallet')
                ->get();
            DB::commit();
            return $this->returnData($users, 200);
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }

    public function get_all_student(Request $request)
    {
        try {
            DB::beginTransaction();
            $teachers = ProfileStudent::filter($request)->with('user')->with('user.wallet')->get()->map(function ($profileStudent) {
                $profileStudent->is_blocked =$profileStudent->user->isBlocked();
                return $profileStudent;
            });
            DB::commit();
            return $this->returnData($teachers, 200);
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }

    public function destroy_teacher($id)
    {
        try {
            DB::beginTransaction();

            $teacher = ProfileTeacher::find($id);
            if (!$teacher) {
                return $this->returnError(404, 'Teacher not found');
            }

            $user = Auth::user();
            $EmployeeReport = EmployeeReport::create([
                'nameEmployee' => $user->name,
                'operation' => "حذف استاذ من المنصة",
                'name' => $teacher->user->name,
                'nameColumn' => 'استاذ',
            ]);
            $EmployeeReport->save();
            $teacher->domains()->each(function ($domain) {
                $domain->delete();
            });
            $user = $teacher->user;
            $teacher->day()->each(function ($day) {
                $day->hours()->each(function ($hour) {
                    $hour->delete();
                });
                $day->delete();
            });
            if ($teacher->request_complete) {
                $teacher->request_complete->delete();
            }
            if ($user) {
                $block = $user->block;
                if ($block) {
                    $block->delete();
                }
                $user->delete();
            }

            $teacher->delete();
            DB::commit();
            return $this->returnData(200, __("backend.Delete successfully", [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }
    public function destroy_student($id)
    {
        try {
            DB::beginTransaction();

            $student = ProfileStudent::find($id);
            if (!$student) {
                return $this->returnError(404, 'Student not found');
            }

            $user = Auth::user();
            $EmployeeReport = EmployeeReport::create([
                'nameEmployee' => $user->name,
                'operation' => "حذف طالب من المنصة",
                'name' => $student->user->name,
                'nameColumn' => 'طالب',
            ]);
            $EmployeeReport->save();
            $user = $student->user;
            if ($user) {
                $block = $user->block;
                if ($block) {
                    $block->delete();
                }
                $user->delete();
            }
            $student->delete();
            DB::commit();
            return $this->returnData(200, __("backend.Delete successfully", [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }


    public function searchByName(Request $request)
    {
        $searchTerm = $request->input('search');
        if (!$searchTerm) {
            return response()->json(['message' => 'Please provide a search term'], 400);
        }
        $searchTerms = explode(' ', $searchTerm);
        $usersQuery = User::query();
        foreach ($searchTerms as $term) {
            $usersQuery->where('name', 'LIKE', '%' . $term . '%')
                ->orWhere('address', 'LIKE', '%' . $term . '%')
                // ->Where('status', 1);
            ;
        }
        $users = $usersQuery->get();
        if ($users->isEmpty()) {
            return response()->json(['message' => 'No users found matching the search term'], 404);
        }
        return response()->json(['users' => $users], 200);
    }

    public function searchByAddress(Request $request)
    {
        $searchTerm = $request->input('search');
        if (!$searchTerm) {
            return response()->json(['message' => 'Please provide a search term'], 400);
        }
        $searchTerms = explode(' ', $searchTerm);
        $usersQuery = User::query();
        foreach ($searchTerms as $term) {
            $usersQuery->where('address', 'LIKE', '%' . $term . '%');
        }
        $users = $usersQuery->get();
        if ($users->isEmpty()) {
            return response()->json(['message' => 'No users found matching the search term'], 404);
        }
        return response()->json(['users' => $users], 200);
    }

    public function get_all_reject_join_request()
    {
        try {
            DB::beginTransaction();
            $teacher = RejectRequest::where('type', 'join Request')->get();

            DB::commit();
            return $this->returnData($teacher, 200);
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }
    public function get_all_reject_complete_request()
    {
        try {
            DB::beginTransaction();
            $teacher = RejectRequest::where('type', 'complete Request')->get();

            DB::commit();
            return $this->returnData($teacher, 200);
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }
    public function getAllReport()
    {
        try {
            DB::beginTransaction();
            $report = EmployeeReport::get();

            DB::commit();
            return $this->returnData($report, 200);
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }

    public function getById($id)
    {
        try {
            DB::beginTransaction();
            $employee = User::where('id', $id)->whereHas('roles', function ($q) {
                $q->where('name', "employee");
            })->first();
            if (!$employee) {
                return $this->returnError(500, "employee not found");
            }
            $reports = EmployeeReport::all();
            $report = [];
            foreach ($reports as $value) {
                if ($value->nameEmployee == $employee->name) {
                    $report[] = $value;
                }
            }
            DB::commit();
            return $this->returnData($report, 200);
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }
}


    //public function insert_teacher()
    //{
    // for ($i = 26; $i < 50; $i++) {
    //     $user = User::create([
    //         'name'           => 'student'.$i,
    //         'email'          => 'student'.$i.'@gmail.com',
    //         'password'       => '12341234',
    //         'address'        => 'Syria',
    //         'governorate'    => 'Dam',
    //         'birth_date'     => Carbon::now(),
    //         'image'          => 'D://3.jpg',
    //         'role_id'        => 'student'
    //     ]);
    //     $credentials = ['email' => $user->email, 'password' => '12341234'];
    //     $token = JWTAuth::attempt($credentials);
    //     $user->token = $token;
    //     $role = Role::where('guard_name', '=', 'student')->first();
    //     $user->assignRole($role);
    //     $user->loadMissing(['roles']);
    //     if (!$token)
    //         return $this->returnError('Unauthorized', 400);
    //     $wallet = Wallet::create([
    //         'user_id' => $user->id,
    //         'number' => random_int(1000000000000, 9000000000000),
    //         'value' => 0,
    //     ]);
    // }

    // for ($i = 1; $i < 25; $i++) {
    //     $complete = ProfileTeacher::create([
    //         'user_id' => $i,
    //         'certificate' => 'dsds',
    //         'description' => 'dsdsd',
    //         'jurisdiction' => 'wewew',
    //         'domain' => 'ewwewq',
    //         'status' => 0,
    //         'assessing' => 0
    //     ]);
    // }

    // for ($i = 26; $i < 50; $i++) {
    //     $user = ProfileStudent::create([
    //         'user_id' =>$i,
    //         'educational_level' => 'eweq',
    //         'description' => 'yti',
    //         'assessing' => 0
    //     ]);
    // }
    // }
