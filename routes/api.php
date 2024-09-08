<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\BlockController;
use App\Http\Controllers\Admin\ChannelController;
use App\Http\Controllers\Admin\ProfitRatioController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FavouriteController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaginateController;
use App\Http\Controllers\PasswordController;
//use App\Http\Controllers\ProfileStudentAdsController;
use App\Http\Controllers\ReservationAdsController;
use App\Http\Controllers\ReservationSeriesController;
use App\Http\Controllers\ReservationTeachingMethodController;
use App\Http\Controllers\SeriesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileTeacherController;
use App\Http\Controllers\AdsController;
//use App\Http\Controllers\AppointmentAvailableController;
//use App\Http\Controllers\AppointmentTeacherStudentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\GovernorController;
use App\Http\Controllers\ProfileStudentController;
use App\Http\Controllers\IntrestController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\QualificationCourseController;
use App\Http\Controllers\ReportController;
//use App\Http\Controllers\RequestCompleteController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ServiceTeacherController;
use App\Http\Controllers\Teacher\CalendarController;
use App\Http\Controllers\Teacher\CompleteTeacherController;
use App\Http\Controllers\TeachingMethodController;
//use App\Http\Controllers\TeachingMethodUserController;
use App\Http\Controllers\User\CompleteController;
use App\Http\Controllers\User\CompleteStudentController;
use App\Http\Controllers\User\HistoryTransactionController;
use App\Http\Controllers\User\LockHourController;
use App\Http\Controllers\User\SearchController;
use App\Http\Controllers\WalletController;
//use App\Models\Wallet;
//use Spatie\Permission\Contracts\Permission;
//use GuzzleHttp\Middleware;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['localization']], function () {
    Route::get('/login/google', [GoogleController::class, 'redirectToGoogle']);
    Route::get('/login/google/callback', [GoogleController::class, 'handleGoogleCallback']);

    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('forgetPassword', [PasswordController::class, 'forgetPassword']);
    Route::post('checkCode', [PasswordController::class, 'checkCode']);
    Route::post('passwordNew', [PasswordController::class, 'passwordNew']);
    Route::post('login_admin', [AdminAuthController::class, 'login_admin']);
    Route::post('codeAdmin', [AdminAuthController::class, 'codeAdmin']);
    Route::post('refreshToken', [AuthController::class, 'refreshToken']);
    Route::get('test', [AuthController::class, 'test'])->middleware('jwt.verify');


    Route::group(['middleware' => ['jwt.verify']], function () {

        Route::post('test', [AuthController::class, 'test']);
        Route::post('resetPassword', [PasswordController::class, 'resetPassword']);
        Route::delete('deleteMyAccount', [AuthController::class, 'deleteMyAccount']);
        Route::post('logout', [AuthController::class, 'logout']);

        Route::group(['middleware' => ['hasRole:teacher']], function () {

            Route::group(['prefix' => 'profile_teacher'], function () {
                Route::post('store', [ProfileTeacherController::class, 'store']);
                Route::post('update', [ProfileTeacherController::class, 'update']);
                Route::get('getmyProfile', [ProfileTeacherController::class, 'show']);
            });
        });

        Route::group(['middleware' => ['hasRole:student|admin|employee']], function () {

            Route::group(['prefix' => 'profile_teacher'], function () {
                Route::get('getById/{id}', [ProfileTeacherController::class, 'getById']);
                Route::get('index', [ProfileTeacherController::class, 'index']);
                Route::get('index/paginate', [PaginateController::class, 'profile_teacher']);
            });
        });

        Route::group(['middleware' => ['hasRole:admin']], function () {

            Route::group(['prefix' => 'report'], function () {
                Route::get('getAll', [AdminController::class, 'getAllReport']);
                Route::get('getAll/paginate', [PaginateController::class, 'getAllReport']);
                Route::get('getById/{id}', [AdminController::class, 'getById']);
                Route::get('financialReports', [ProfitRatioController::class, 'getFinancialReports']);
                Route::get('financialReports/paginate', [PaginateController::class, 'getFinancialReports']);
            });
        });
        Route::group(['prefix' => 'profile_teacher'], function () {
            Route::group(['middleware' => 'hasRole:admin|employee'], function () {
                Route::get('getByIdForAdmin/{id}', [ProfileTeacherController::class, 'getByIdForAdmin']);
            });
        });


        Route::group(['middleware' => ['hasRole:student']], function () {

            Route::group(['prefix' => 'profile_student'], function () {
                Route::post('store', [ProfileStudentController::class, 'store']);
                Route::post('update', [ProfileStudentController::class, 'update']);
                Route::get('getmyProfile', [ProfileStudentController::class, 'show']);
            });
        });

        Route::group(['prefix' => 'profile_student'], function () {
            Route::group(['middleware' => 'hasRole:admin|employee'], function () {
                Route::get('getByIdForAdmin/{id}', [ProfileStudentController::class, 'getByIdForAdmin']);
            });
        });

        Route::group(['middleware' => ['hasRole:teacher|admin|employee']], function () {
            Route::group(['prefix' => 'profile_student'], function () {
                Route::get('getById/{id}', [ProfileStudentController::class, 'getById']);
                Route::get('getAll', [ProfileStudentController::class, 'getAll']);
                Route::get('getAll/paginate', [PaginateController::class, 'profile_student']);
            });
        });
        Route::group(['middleware' => ['hasRole:teacher']], function () {

            Route::group(['prefix' => 'ads'], function () {
                Route::group(['middleware' => ['profileTeacher']], function () {
                    Route::post('store', [AdsController::class, 'store']);
                    Route::post('update/{id}', [AdsController::class, 'update']);
                    Route::delete('delete/{id}', [AdsController::class, 'destroy']);
                });
                Route::get('getMyAds', [AdsController::class, 'getMyAds']);
            });
        });

        Route::get('ads/getById/{id}', [AdsController::class, 'getById']);

        Route::delete('ads/deleteForAdmin/{id}', [AdsController::class, 'deleteForAdmin'])->middleware('hasRole:admin');

        Route::group(['middleware' => ['hasRole:student|admin|employee']], function () {
            Route::group(['prefix' => 'ads'], function () {
                Route::get('getAll', [AdsController::class, 'index']);
                Route::get('getAll/paginate', [PaginateController::class, 'ads']);
                Route::get('getAdsTeacher/{id}', [AdsController::class, 'getAdsTeacher']);
                Route::get('getSimilar', [AdsController::class, 'getSimilar']);
            });
        });

        Route::group(['middleware' => ['hasRole:admin']], function () {
            Route::group(['prefix' => 'employee'], function () {
                Route::get('getAll', [EmployeeController::class, 'getAll']);
                Route::get('getAll/paginate', [PaginateController::class, 'employee']);
                Route::post('store', [EmployeeController::class, 'createEmployee']);
                Route::post('update/{id}', [EmployeeController::class, 'updateEmployee']);
                Route::get('getById/{id}', [EmployeeController::class, 'getById']);
                Route::delete('delete/{id}', [EmployeeController::class, 'delete']);
            });
        });

        Route::group(['middleware' => ['hasRole:student']], function () {
            Route::group(['prefix' => 'evaluation'], function () {
                Route::post('store', [EvaluationController::class, 'store'])->middleware('profileStudent');
                Route::delete('delete/{id}', [EvaluationController::class, 'destroy']);
            });

            Route::group(['prefix' => 'intrest'], function () {
                Route::post('store', [IntrestController::class, 'store'])->middleware('profileStudent');
                Route::post('update/{id}', [IntrestController::class, 'update']);
                Route::delete('delete/{id}', [IntrestController::class, 'destroy']);
                Route::get('getMyIntrests', [IntrestController::class, 'getMyIntrests']);
            });
        });

        Route::group(['middleware' => ['hasRole:teacher']], function () {

            Route::group(['prefix' => 'note'], function () {
                Route::post('store', [NoteController::class, 'store'])->middleware('profileTeacher');;
                Route::delete('delete/{id}', [NoteController::class, 'destroy']);
                Route::get('getStudentsNotes', [NoteController::class, 'index']);
            });
        });

        Route::group(['middleware' => ['hasRole:admin']], function () {

            Route::group(['prefix' => 'permission'], function () {
                Route::post('store', [PermissionController::class, 'create']);
                Route::post('update/{id}', [PermissionController::class, 'update']);
                Route::get('getall', [PermissionController::class, 'getAll']);
                Route::get('getById/{id}', [PermissionController::class, 'getById']);
                Route::delete('delete/{id}', [PermissionController::class, 'delete']);
            });
        });

        Route::group(['prefix' => 'report'], function () {
            Route::get('get', [ReportController::class, 'index'])->middleware('hasRole:admin|employee');
            Route::get('get/paginate', [PaginateController::class, 'report'])->middleware('hasRole:admin|employee');
            Route::post('report_student', [ReportController::class, 'report_student'])->middleware(['hasRole:teacher', 'profileTeacher']);;
            Route::post('report_teacher', [ReportController::class, 'report_teacher'])->middleware(['hasRole:student', 'profileStudent']);
        });

        Route::group(['middleware' => ['hasRole:admin']], function () {

            Route::group(['prefix' => 'role'], function () {
                Route::post('store', [RoleController::class, 'create']);
                Route::post('update/{id}', [RoleController::class, 'update']);
                Route::delete('delete/{id}', [RoleController::class, 'delete']);
                Route::get('getAll', [RoleController::class, 'getAll']);
                Route::get('getById/{id}', [RoleController::class, 'getById']);
            });
        });

        Route::group(['prefix' => 'search'], function () {
            Route::post('financialReport', [SearchController::class, 'searchFinancialReport']);
            Route::post('course', [SearchController::class, 'searchCourse']);
            Route::post('searchAddress', [SearchController::class, 'searchByAddress']);
        });

        Route::group(['middleware' => ['hasRole:teacher']], function () {
            Route::group(['prefix' => 'ServiceTeacher'], function () {
                Route::group(['middleware' => ['teacher']], function () {
                    Route::post('store', [ServiceTeacherController::class, 'store']);
                    Route::post('update/{id}', [ServiceTeacherController::class, 'update']);
                    Route::delete('delete/{id}', [ServiceTeacherController::class, 'destroy']);
                });
                Route::get('getMyService', [ServiceTeacherController::class, 'getMyService']);
            });
        });

        Route::get('ServiceTeacher/getById/{id}', [ServiceTeacherController::class, 'show']);
        Route::get('ServiceTeacher/getAll/{id}', [ServiceTeacherController::class, 'index'])->middleware('hasRole:student');


        Route::group(['middleware' => ['hasRole:teacher']], function () {
            Route::group(['prefix' => 'TeachingMethod'], function () {
                Route::post('store', [TeachingMethodController::class, 'store'])->middleware('profileTeacher');;
                Route::post('update/{id}', [TeachingMethodController::class, 'update']);
                Route::delete('delete/{id}', [TeachingMethodController::class, 'destroy']);
                Route::get('getMyTeachingMethod', [TeachingMethodController::class, 'getMyTeachingMethod']);
            });
        });

        Route::group(['middleware' => ['hasRole:admin|employee']], function () {
            Route::group(['prefix' => 'TeachingMethod'], function () {
                Route::get('getAll', [TeachingMethodController::class, 'getAll']);
                Route::get('getAll/paginate', [PaginateController::class, 'TeachingMethod']);
                Route::delete('deleteForAdmin/{id}', [TeachingMethodController::class, 'deleteForAdmin']);
            });
        });

        Route::get('TeachingMethod/getById/{id}', [TeachingMethodController::class, 'show']);

        Route::group(['middleware' => ['hasRole:student']], function () {
            Route::get('TeachingMethod/getAll/{id}', [TeachingMethodController::class, 'index']);
        });

        Route::group(['middleware' => ['hasRole:student']], function () {
            Route::group(['prefix' => 'TeachingMethodUser'], function () {
                Route::post('store', [ReservationTeachingMethodController::class, 'store'])->middleware('profileStudent');
                Route::delete('delete/{id}', [ReservationTeachingMethodController::class, 'destroy']);
                Route::get('getMyTeachingMethod', [ReservationTeachingMethodController::class, 'getMyTeachingMethod']);
                Route::get('getById/{id}', [ReservationTeachingMethodController::class, 'show']);
            });
        });


        //  khadr
        Route::group(['prefix' => 'transactions-wallet'], function () {

            Route::group(['middleware' => ['hasRole:admin|employee']], function () {
                Route::get('get-request-charge-student', [GovernorController::class, 'get_request_charge_student']);
                Route::get('get-request-charge-student/paginate', [PaginateController::class, 'get_request_charge_student']);
                Route::get('get-request-charge-teacher', [GovernorController::class, 'get_request_charge_teacher']);
                Route::get('get-request-charge-teacher/paginate', [PaginateController::class, 'get_request_charge_teacher']);
                Route::get('get-request-recharge-student', [GovernorController::class, 'get_request_recharge_student']);
                Route::get('get-request-recharge-student/paginate', [PaginateController::class, 'get_request_recharge_student']);
                Route::get('get-request-recharge-teacher', [GovernorController::class, 'get_request_recharge_teacher']);
                Route::get('get-request-recharge-teacher/paginate', [PaginateController::class, 'get_request_recharge_teacher']);
                Route::post('delete-request/{id}', [GovernorController::class, 'destroy']);
                Route::get('accept_request_charge/{id}', [GovernorController::class, 'accept_request_charge']);
                Route::get('accept_request_recharge/{id}', [GovernorController::class, 'accept_request_recharge']);
                Route::get('history_transaction', [HistoryTransactionController::class, 'index']);
                Route::get('history_transaction/paginate', [PaginateController::class, 'history_transaction']);
            });
            Route::group(['middleware' => ['hasRole:student|teacher']], function () {
                Route::post('add-request-charge', [GovernorController::class, 'addRequestCharge']);
                Route::post('add-request-recharge', [GovernorController::class, 'addRequestRecharge']);
                Route::get('show-my-request-charge', [GovernorController::class, 'show']);
                Route::get('show-my-request-recharge', [GovernorController::class, 'showMyRequestRecharge']);
                Route::get('show-my-wallet', [WalletController::class, 'show']);
                Route::get('show-my-history', [HistoryTransactionController::class, 'show']);
            });
        });

        //  khader
        Route::group(['prefix' => 'request-complete'], function () {
            Route::group(['middleware' => ['hasRole:teacher']], function () {
                Route::post('store', [CompleteTeacherController::class, 'store']);
                Route::post('update', [CompleteTeacherController::class, 'update']);
            });
            Route::group(['middleware' => ['hasRole:admin|employee']], function () {
                Route::get('get', [CompleteTeacherController::class, 'index']);
                Route::get('get/paginate', [PaginateController::class, 'request_complete']);
                Route::post('delete-request-complete/{id}', [CompleteTeacherController::class, 'destroy']);
                Route::get('accept-request-complete-teacher/{id}', [CompleteTeacherController::class, 'accept_request_complete_teacher']);
            });
        });

        // Admin khader
        Route::group(['prefix' => 'request-join', 'middleware' => ['hasRole:admin|employee']], function () {
            Route::get('get', [AdminController::class, 'index']);
            Route::get('get/paginate', [PaginateController::class, 'request_join']);
            Route::post('delete-request-join/{id}', [AdminController::class, 'destroy']);
            Route::get('accept-request-join/{id}', [AdminController::class, 'accept_request_teacher']);
            // Route::get('count-teacher', [AdminController::class, 'count_teacher']);
            Route::delete('delete-teacher/{id}', [AdminController::class, 'destroy_teacher']);
            Route::delete('delete-student/{id}', [AdminController::class, 'destroy_student']);
            Route::get('get-teacher-unblock', [AdminController::class, 'get_all_teacher_unblock']);
            Route::get('get-teacher-block', [AdminController::class, 'get_all_teacher_block']);
            Route::get('get-teacher', [AdminController::class, 'get_all_teacher']);
            // Route::get('count-student', [AdminController::class, 'count_student']);

            Route::get('get-student-unblock', [AdminController::class, 'get_all_student_unblock']);
            Route::get('get-student-block', [AdminController::class, 'get_all_student_block']);
            Route::get('get-student', [AdminController::class, 'get_all_student']);
            Route::get('reject-join-request', [AdminController::class, 'get_all_reject_join_request']);
            Route::get('reject-complete-request', [AdminController::class, 'get_all_reject_complete_request']);
        });
        Route::group(['prefix' => 'block-list', 'middleware' => ['hasRole:admin|employee']], function () {
            Route::get('get', [BlockController::class, 'index']);
            Route::post('store/{id}', [BlockController::class, 'store']);
            Route::delete('unblock-user/{id}', [BlockController::class, 'destroy']);
        });

        Route::group(['prefix' => 'statistics', 'middleware' => ['hasRole:admin']], function () {
            Route::get('percentage', [AdminController::class, 'allPercentage']);
            Route::get('getAllProfitRatios', [ProfitRatioController::class, 'index']);
            Route::post('updateProfitRatioFile/{id}', [ProfitRatioController::class, 'updateProfitRatioFile']);
            Route::post('updateProfitRatioVideoCall/{id}', [ProfitRatioController::class, 'updateProfitRatioVideoCall']);
            Route::post('updateProfitRatioPrivate/{id}', [ProfitRatioController::class, 'updateProfitRatioPrivate']);
            Route::post('updateProfitRatioAds/{id}', [ProfitRatioController::class, 'updateProfitRatioAds']);
        });
        //khader

        Route::group(['middleware' => 'hasRole:admin|employee'], function () {
            Route::group(['prefix' => 'QualificationCourse'], function () {
                Route::post('store', [QualificationCourseController::class, 'store']);
                Route::post('update/{id}', [QualificationCourseController::class, 'update']);
                Route::delete('delete/{id}', [QualificationCourseController::class, 'destroy']);
                Route::get('getById/{id}', [QualificationCourseController::class, 'show']);
                Route::get('getall', [QualificationCourseController::class, 'index']);
            });
        });

        Route::group(['middleware' => 'hasRole:teacher'], function () {
            Route::group(['prefix' => 'QualificationCourse'], function () {
                Route::get('getallcourse', [QualificationCourseController::class, 'index']);
                Route::post('insert_into_courses/{id}', [QualificationCourseController::class, 'insert_into_courses']);
                Route::get('show_my_courses', [QualificationCourseController::class, 'show_my_courses'])
                    ->middleware('hasRole:teacher');
            });
        });

        Route::group(['prefix' => 'calender'], function () {
            Route::group(['middleware' => ['hasRole:teacher']], function () {
                Route::post('store', [CalendarController::class, 'store']);
                Route::post('update', [CalendarController::class, 'update']);
                Route::get('get', [CalendarController::class, 'index']);
                Route::get('get_all_accept_request', [LockHourController::class, 'get_all_accept_request']);
                Route::get('accept-request/{id}', [LockHourController::class, 'accept_request']);
                Route::get('user_lock', [LockHourController::class, 'index']);
                Route::post('delete/{id}', [LockHourController::class, 'destroy']);
            });
            Route::group(['middleware' => 'hasRole:student'], function () {
                Route::get('getDate/{id}', [LockHourController::class, 'getDate']);
                Route::get('getById/{id}', [CalendarController::class, 'show']);
                Route::post('lock-hour', [LockHourController::class, 'store']);
                Route::get('delete-request/{id}', [LockHourController::class, 'delete_my_request']);
                Route::get('show_my_request', [LockHourController::class, 'get_my_request']);
                Route::get('show_my_accept_request', [LockHourController::class, 'get_my_accept_request']);
            });
        });
        Route::controller(CompleteStudentController::class)
            ->prefix('complete-student')->middleware('hasRole:student')->group(function () {
                Route::get('get', 'index');
                Route::post('store', 'store');
                Route::post('update', 'update');
            });

        Route::group(['middleware' => ['hasRole:student']], function () {
            Route::group(['prefix' => 'ProfileStudentAds'], function () {
                Route::post('store', [ReservationAdsController::class, 'store'])->middleware('profileStudent');
                Route::delete('delete/{id}', [ReservationAdsController::class, 'destroy']);
                Route::get('getMyAds', [ReservationAdsController::class, 'getMyAds']);
                Route::get('getById/{id}', [ReservationAdsController::class, 'show']);
            });
        });
        Route::group(['prefix' => 'notifications'], function () {
            Route::get("", [NotificationController::class, 'getAll']);
            Route::get("not_viewed", [NotificationController::class, 'getNotificationsNotViewed']);
            Route::get("viewed", [NotificationController::class, 'getNotificationsViewed']);
            Route::get("{id}", [NotificationController::class, 'getById']);
            Route::delete("{id}", [NotificationController::class, 'delete']);
        });

        Route::group(['prefix' => 'comments'], function () {
            Route::get('commentsAds/{id}', [CommentController::class, 'commentsAds']);
            Route::post("store", [CommentController::class, 'store']);
            Route::post("update/{id}", [CommentController::class, 'update']);
            Route::delete("delete/{id}", [CommentController::class, 'destroy']);
        });

        Route::group(['prefix' => 'favourite'], function () {
            Route::group(['middleware' => ['hasRole:student']], function () {
                Route::get('getMyFavourite', [FavouriteController::class, 'getMyFavourite']);
                Route::post("store", [FavouriteController::class, 'store'])->middleware('profileStudent');
            });
        });

        Route::group(['prefix' => 'series'], function () {
            Route::get('getAll', [SeriesController::class, 'getAll']);

            Route::group(['middleware' => ['hasRole:admin|employee']], function () {
                Route::delete("deleteForAdmin/{id}", [SeriesController::class, 'deleteForAdmin']);
                Route::get('getSeriesForTeachingA/{id}', [SeriesController::class, 'getSeriesForTeachingA']);
            });
            Route::group(['middleware' => ['hasRole:teacher', 'profileTeacher']], function () {
                Route::post('store', [SeriesController::class, 'store']);
                Route::post('update/{id}', [SeriesController::class, 'update']);
                Route::delete("delete/{id}", [SeriesController::class, 'destroy']);
            });
            Route::group(['middleware' => ['hasRole:teacher']], function () {
                Route::get('getSeriesForTeachingFT/{id}', [SeriesController::class, 'getSeriesForTeachingFT']);
                Route::get('getMySeries', [SeriesController::class, 'getMySeries']);
            });
            Route::get('getById/{id}', [SeriesController::class, 'show']);

            Route::group(['middleware' => ['hasRole:student']], function () {
                Route::get('getSeries', [ReservationSeriesController::class, 'getSeries']);
                Route::get('getByIdSeries/{id}', [ReservationSeriesController::class, 'getByIdSeries']);
                Route::get('getSeriesForTeaching/{id}', [ReservationSeriesController::class, 'getSeriesForTeaching']);
                Route::get('getAll/{id}', [SeriesController::class, 'index']);
            });
        });

        Route::group(['prefix' => 'channel'], function () {
            Route::group(['middleware' => ['hasRole:teacher|student']], function () {
                Route::post('connect', [ChannelController::class, 'connect']);
                Route::get('Disconnect/{id}', [ChannelController::class, 'Disconnect']);
            });
        });

        Route::group(['prefix' => 'search'], function () {
            Route::post('acceptTeachers', [SearchController::class, 'acceptTeachers']);
            Route::post('students', [SearchController::class, 'students']);
            Route::post('employees', [SearchController::class, 'employees']);
            Route::post('completeRequest', [SearchController::class, 'completeRequest']);
            Route::post('ads', [SearchController::class, 'ads']);
            Route::post('teachingMethods', [SearchController::class, 'teachingMethods']);
            Route::post('reports', [SearchController::class, 'reports']);
            Route::post('request_join', [SearchController::class, 'request_join']);
            Route::post('getSeriesForTeachingA/{id}', [SearchController::class, 'getSeriesForTeachingA']);
            Route::post('teachingMethodsSeries', [SearchController::class, 'teachingMethodsSeries']);

        });
    });
});
