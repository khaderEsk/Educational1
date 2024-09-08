<?php

namespace App\Http\Controllers;

use App\Models\Ads;
use App\Models\CompleteTeacher;
use App\Models\EmployeeReport;
use App\Models\FinancialReport;
use App\Models\Governor;
use App\Models\HistoryTransaction;
use App\Models\ProfileStudent;
use App\Models\ProfileTeacher;
use App\Models\Report;
use App\Models\TeachingMethod;
use App\Models\User;
use App\Traits\GeneralTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaginateController extends Controller
{
    use GeneralTrait;
    public function profile_teacher()
    {
        try {
            DB::beginTransaction();
            $user = auth()->user();

            $profile_teacher = ProfileTeacher::where('status', 1)
                ->whereDoesntHave('user.block')
                ->orderByRaw("CASE WHEN (SELECT governorate FROM users WHERE users.id = profile_teachers.user_id) = '{$user->governorate}' THEN 0 ELSE 1 END")
                ->paginate(10);

            $profile_teacher->loadMissing(['user', 'domains']);

            DB::commit();
            return $this->returnData($profile_teacher, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", 'Please try again later');
        }
    }


    public function getAllReport()
    {
        try {
            DB::beginTransaction();
            $report = EmployeeReport::paginate(10);

            DB::commit();
            return $this->returnData($report, 200);
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }

    public function getFinancialReports()
    {
        DB::beginTransaction();
        try {

            $reports = FinancialReport::paginate(10);
            DB::commit();
            return $this->returnData($reports, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }

    public function profile_student()
    {
        try {
            DB::beginTransaction();

            $profile_student = ProfileStudent::whereDoesntHave('user.block')
                ->paginate(10);
            $profile_student->loadMissing(['user']);

            DB::commit();
            return $this->returnData($profile_student, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", 'Please try again later');
        }
    }

    public function ads()
    {
        try {
            $user = auth()->user();

            $ads = Ads::orderByRaw("CASE WHEN place = '{$user->governorate}' THEN 0 ELSE 1 END, created_at DESC")->join('profile_teachers', 'ads.profile_teacher_id', '=', 'profile_teachers.id')->join('users', 'profile_teachers.user_id', '=', 'users.id')
                ->select('ads.*', 'users.name')
                ->paginate(10);

            return $this->returnData($ads, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            return $this->returnError("500", "Please try again later");
        }
    }

    public function employee()
    {
        try {

            $data = User::with('roles')
                ->whereHas('roles', function ($query) {
                    $query->where('id', 4);
                })
                ->paginate(10);

            $data->getCollection()->transform(function ($user) {
                $user->is_blocked = $user->isBlocked();
                return $user;
            });
            return $this->returnData($data, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            return $this->returnError($ex->getCode(), 'Please try again later');
        }
    }

    public function report()
    {
        try {
            DB::beginTransaction();
            $reports = Report::with(['reporter' => function ($query) {
                $query->select('id', 'user_id');
                $query->with([
                    'user:id,name'
                ]);
            }, 'reported' => function ($query) {
                $query->select('id', 'user_id');
                $query->with([
                    'user:id,name'
                ]);
            }])->orderBy('created_at', 'desc')->paginate(10);

            DB::commit();
            return $this->returnData($reports, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", $ex->getMessage());
        }
    }


    public function TeachingMethod()
    {
        try {

            $teaching_methods = TeachingMethod::whereDoesntHave('series')->join('profile_teachers', 'teaching_methods.profile_teacher_id', '=', 'profile_teachers.id')->join('users', 'profile_teachers.user_id', '=', 'users.id')
                ->select('teaching_methods.*', 'users.name')->orderBy('created_at', 'desc')
                ->paginate(10);

            return $this->returnData($teaching_methods, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            return $this->returnError("500", "Please try again later");
        }
    }

    public function get_request_charge_student()
    {
        try {
            DB::beginTransaction();
            $convenor = Governor::where('type', 'charge')
                ->whereHas('wallet.user.roles', function ($query) {
                    $query->where('name', 'student');
                })
                ->with(['wallet.user' => function ($query) {
                    $query->select('id', 'name', 'address', 'governorate');
                }])
                ->paginate(10);

            $convenor->getCollection()->transform(function ($governor) {
                return [
                    'id' => $governor->id,
                    'amount' => $governor->amount,
                    'walletsValue' => $governor->wallet->value,
                    'name' => $governor->wallet->user->name,
                    'address' => $governor->wallet->user->address,
                    'governorate' => $governor->wallet->user->governorate,
                    'image_transactions' => $governor->image_transactions,
                    'date' => Carbon::parse($governor->created_at)->toDateString()
                ];
            });
            DB::commit();
            return $this->returnData($convenor, 'Request recharge');
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }

    public function get_request_charge_teacher()
    {
        try {
            DB::beginTransaction();
            $convenor = Governor::where('type', 'charge')
                ->whereHas('wallet.user.roles', function ($query) {
                    $query->where('name', 'teacher');
                })
                ->with(['wallet.user' => function ($query) {
                    $query->select('id', 'name', 'address', 'governorate');
                }])
                ->paginate(10);

            $convenor->getCollection()->transform(function ($governor) {
                return [
                    'id' => $governor->id,
                    'amount' => $governor->amount,
                    'walletsValue' => $governor->wallet->value,
                    'name' => $governor->wallet->user->name,
                    'address' => $governor->wallet->user->address,
                    'governorate' => $governor->wallet->user->governorate,
                    'image_transactions' => $governor->image_transactions,
                    'date' => Carbon::parse($governor->created_at)->toDateString()
                ];
            });
            DB::commit();
            return $this->returnData($convenor, 'Request recharge');
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }

    public function get_request_recharge_student()
    {
        try {
            DB::beginTransaction();
            $convenor = Governor::where('type', 'recharge')
                ->whereHas('wallet.user.roles', function ($query) {
                    $query->where('name', 'student');
                })
                ->with(['wallet.user' => function ($query) {
                    $query->select('id', 'name', 'address', 'governorate');
                }])
                ->paginate(10);


            $convenor->getCollection()->transform(function ($governor) {
                return [
                    'id' => $governor->id,
                    'amount' => $governor->amount,
                    'walletsValue' => $governor->wallet->value,
                    'name' => $governor->wallet->user->name,
                    'address' => $governor->wallet->user->address,
                    'governorate' => $governor->wallet->user->governorate,
                    'transferCompany' => $governor->transferCompany,
                    'date' => Carbon::parse($governor->created_at)->toDateString()
                ];
            });
            DB::commit();
            return $this->returnData($convenor, 'Request recharge');
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }

    public function get_request_recharge_teacher()
    {
        try {
            DB::beginTransaction();
            $convenor = Governor::where('type', 'recharge')
                ->whereHas('wallet.user.roles', function ($query) {
                    $query->where('name', 'teacher');
                })
                ->with(['wallet.user' => function ($query) {
                    $query->select('id', 'name', 'address', 'governorate');
                }])
                ->paginate(10);

            $convenor->getCollection()->transform(function ($governor) {
                return [
                    'id' => $governor->id,
                    'amount' => $governor->amount,
                    'walletsValue' => $governor->wallet->value,
                    'name' => $governor->wallet->user->name,
                    'address' => $governor->wallet->user->address,
                    'governorate' => $governor->wallet->user->governorate,
                    'transferCompany' => $governor->transferCompany,
                    'date' => Carbon::parse($governor->created_at)->toDateString()
                ];
            });
            DB::commit();
            return $this->returnData($convenor, 'Request recharge');
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }


    public function history_transaction()
    {
        try {
            DB::beginTransaction();
            $history = HistoryTransaction::paginate(10);
            // DB::commit();
            return $this->returnData($history, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }


    public function request_complete()
    {
        try {
            $requestCompletes = CompleteTeacher::with([
                'teacher' => function ($q) {
                    $q->select('id', 'user_id');
                },
                'teacher.user' => function ($q) {
                    $q->select('id', 'name', 'address');
                },
                'teacher.domains' => function ($q) {
                    $q->select(
                        'id',
                        'profile_teacher_id',
                        'type'
                    );
                }
            ])->where('status', '=', 0)->paginate(10);
            DB::commit();
            return $this->returnData($requestCompletes, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }

    public function request_join()
    {
        try {
            DB::beginTransaction();
            $teacher = ProfileTeacher::with('user')->with('domains')->where('status', 0)->paginate(10);
            DB::commit();
            return $this->returnData($teacher, 200);
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }
}
