<?php

namespace App\Http\Controllers;

use App\Http\Requests\GovernorRequest;
use App\Jobs\AdminNotificationJob;
use App\Jobs\NotificationJobUser;
use App\Models\Governor;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Models\EmployeeReport;
use App\Models\HistoryTransaction;
use App\Traits\GeneralTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class GovernorController extends Controller
{
    use GeneralTrait;

    private $uploadPath = "assets/images/transaction";
    /**
     * Display a listing of the resource.
     */
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
                ->get()
                ->map(function ($governor) {
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
                ->get()
                ->map(function ($governor) {
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
                ->get()
                ->map(function ($governor) {
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
                ->get()
                ->map(function ($governor) {
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



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function addRequestCharge(GovernorRequest $request)
    {
        try {
            DB::beginTransaction();
            $user_auth=auth()->user();
            if (!$user_auth) {
                return $this->returnError(500, 'the token is not valid ');
            }
            $image_transactions = null;
            if (isset($request->image_transactions)) {
                $image_transactions = $this->saveImage($request->image_transactions, $this->uploadPath);
            }
            $user = auth()->user()->wallet;
            if ($request->image_transactions == null) {
                return $this->returnError(500, 'You must input image transaction');
            }
            $convenor = $user->governor()->create([
                'amount' => isset($request->amount) ? $request->amount : null,
                'image_transactions' => $image_transactions,
                'type' => 'charge',

            ]);
            $convenor->save();
            AdminNotificationJob::dispatch( 'طلب شحن', $user_auth->name.' طلب شحن جديد من قبل ')->delay(Carbon::now()->addSeconds(2));
            DB::commit();
            return $this->returnData($convenor, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }

    public function addRequestRecharge(GovernorRequest $request)
    {
        try {
            DB::beginTransaction();
            $user_auth=auth()->user();
            if (!$user_auth) {
                return $this->returnError(500, 'the token is not valid ');
            }
            $user = $user_auth->wallet;
            if ($request->amount > $user->value) {
                return $this->returnError(400, __('backend.not Enough money in wallet', [], app()->getLocale()));
            }
            $user->update([
                'value' => $user->value - $request->amount,
            ]);
            $user->save();
            $convenor = $user->governor()->create([
                'amount' => isset($request->amount) ? $request->amount : null,
                'type' => 'recharge',
                'phone' => isset($request->phone) ? $request->phone : null,
                'transferCompany' => isset($request->transferCompany) ? $request->transferCompany : null,
                'address' => isset($request->address) ? $request->address : null,

            ]);
            $convenor->save();
            AdminNotificationJob::dispatch( 'طلب تفريغ', $user_auth->name.' طلب تفريغ جديد من قبل ')->delay(Carbon::now()->addSeconds(2));
            DB::commit();
            return $this->returnData($convenor, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        try {
            DB::beginTransaction();
            $userWallet = auth()->user()->wallet->id;
            $data = Governor::with('wallet')->where('wallet_id', $userWallet)->where('type', 'charge')
                ->get();
            DB::commit();
            return $this->returnData($data, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }
    public function showMyRequestRecharge()
    {
        try {
            DB::beginTransaction();
            $userWallet = auth()->user()->wallet->id;
            $data = Governor::with('wallet')->where('wallet_id', $userWallet)->where('type', 'recharge')
                ->get();
            DB::commit();
            return $this->returnData($data, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $history = Governor::with('wallet')->with('wallet.user')->find($id);
            $convenor = Governor::find($id);
            if (!$convenor) {
                return $this->returnError(404, 'not found request');
            }

            $admin = Auth::user();
            $EmployeeReport = EmployeeReport::create([
                'nameEmployee' => $admin->name,
                'operation' => "رفض طلب محفظة",
                'name' => $convenor->wallet->user->name,
                'nameColumn' => 'مستخدم',
            ]);
            $convenor->delete();
            // return $request->case;
            $historyTrn = HistoryTransaction::create([
                'name' => $history->wallet->user->name,
                'image' => $history->image_transactions,
                'type' => $history->type,
                'value' => $history->amount,
                'status' => 'رفض',
                'case' => $request->case
            ]);
            DB::commit();
            return $this->returnData(200, __('backend.delete order successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }

    public function accept_request_charge($id)
    {
        try {
            DB::beginTransaction();
            $history = Governor::with('wallet')->with('wallet.user')->where('type', 'charge')->find($id);
            $convenor = Governor::with('wallet')->where('type', 'charge')->find($id);
            if (!$convenor) {
                return $this->returnError(404, 'not found request');
            }
            $convenor->wallet->update([
                'value' => $convenor->amount + $convenor->wallet->value,
            ]);
            $convenor->wallet->save();
            $convenor->delete();
            $historyTrn = HistoryTransaction::create([
                'name' => $history->wallet->user->name,
                'image' => $history->image_transactions,
                'type' => 'شحن',
                'value' => $history->amount,
                'status' => 'موافقة'
            ]);
            $wallet = $convenor->wallet()->first();
            $user = $wallet->user()->first();

            $admin = Auth::user();
            $EmployeeReport = EmployeeReport::create([
                'nameEmployee' => $admin->name,
                'operation' => "قبول طلب شحن للمحفظة",
                'name' => $convenor->wallet->user->name,
                'nameColumn' => 'مستخدم',
            ]);
            NotificationJobUser::dispatch($user, 'تم الموافقة', 'تم الموافقة على طلب الشحن الخاص بك')->delay(Carbon::now()->addSeconds(2));
            DB::commit();
            return $this->returnData(200, __('backend.charge successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }
    public function accept_request_recharge($id)
    {
        try {
            DB::beginTransaction();
            $history = Governor::with('wallet')->with('wallet.user')->where('type', 'recharge')->find($id);
            $convenor = Governor::with('wallet')->where('type', 'recharge')->find($id);
            if (!$convenor) {
                return $this->returnError(404, 'not found request');
            }
            $convenor->delete();
            $historyTrn = HistoryTransaction::create([
                'name' => $history->wallet->user->name,
                // 'image' => $history->image_transactions,
                'type' => 'تفريغ',
                'value' => $history->amount,
                'status' => 'موافقة '
            ]);
            $wallet = $convenor->wallet()->first();
            $user = $wallet->user()->first();

            $admin = Auth::user();
            $EmployeeReport = EmployeeReport::create([
                'nameEmployee' => $admin->name,
                'operation' => "قبول طلب تفريغ للمحفظة",
                'name' => $convenor->wallet->user->name,
                'nameColumn' => 'مستخدم',
            ]);
            NotificationJobUser::dispatch($user, 'تم الموافقة', 'تم الموافقة على طلب التفريغ الخاص بك')->delay(Carbon::now()->addSeconds(2));
            DB::commit();
            return $this->returnData(200, __('backend.recharge successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getMessage(), $ex->getCode());
        }
    }
}
