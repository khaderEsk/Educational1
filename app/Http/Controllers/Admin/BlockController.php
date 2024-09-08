<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BlockRequest;
use App\Jobs\NotificationJobUser;
use App\Models\Block;
use App\Models\EmployeeReport;
use App\Models\User;
use App\Traits\GeneralTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BlockController extends Controller
{
    use GeneralTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            DB::beginTransaction();
            $user = Block::with('user')->get();
            DB::commit();
            return $this->returnData($user, 200);
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
    public function store(BlockRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            $user = User::find($id);
            if (!$user) {
                return $this->returnError(404, 'not found user');
            }
            if ($user->block) {
                return $this->returnError(501, 'the user is block');
            }

            $block = Block::create([
                'user_id' => $id,
            ]);
            $block->save();
            NotificationJobUser::dispatch($user, 'تم حظرك', '')->delay(Carbon::now()->addSeconds(2));
            DB::commit();
            return $this->returnData($block, 200);
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $block = Block::where('user_id',$id)->first();
            if (!$block) {
                return $this->returnError(404, 'not found user');
            }

            $admin = Auth::user();
            $EmployeeReport = EmployeeReport::create([
                'nameEmployee' => $admin->name,
                'operation' => "فك حظر عن مستخدم",
                'name' => $block->user->name,
                'nameColumn' => 'مستخدم',
            ]);

            $block->delete();
            DB::commit();
            return $this->returnData(__('backend.unblock successfully', [], app()->getLocale()), 200);
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }
}
