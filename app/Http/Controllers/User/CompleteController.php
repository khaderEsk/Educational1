<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompleteRequest;
use App\Models\CompleteTeacher;
use App\Models\RequestComplete;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use function Laravel\Prompts\select;

class CompleteController extends Controller
{
    use GeneralTrait;

    private $uploadPath = "assets/images/profile_students";
    /**
     * Display a listing of the resource.
     */

    
    // public function get_request_teacher()
    // {
    //     try {
    //         DB::beginTransaction();
    //         $date = [];
    //         $requestCompletes = CompleteTeacher::with('teacher')->where('status', '=', 0)->get();
    //         foreach ($requestCompletes as $requestComplete) {
    //             if ($requestComplete->user->role_id == 'teacher') {
    //                 $date[] = $requestComplete;
    //             }
    //         }
    //         DB::commit();
    //         return $this->returnData($date, 'operation completed successfully');
    //     } catch (\Exception $ex) {
    //         DB::rollback();
    //         return $this->returnError($ex->getCode(), $ex->getMessage());
    //     }
    // }

    
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
    // public function store(CompleteRequest $request)
    // {
    //     try {
    //         DB::beginTransaction();
    //         $self_identity = null;
    //         if (isset($request->self_identity)) {
    //             $self_identity = $this->saveImage($request->self_identity, $this->uploadPath);
    //         }
    //         $cv = null;
    //         if (isset($request->cv)) {
    //             $cv = $this->saveAnyFile($request->cv, $this->uploadPath);
    //         }
    //         $user = auth()->user()->profile_teacher;
    //         if(!$user){
    //             return $this->returnError(400, 'Token is Invalid');                
    //         }
    //         $re = CompleteTeacher::where('user_id', $user->id)->first();
    //         if ($re) {
    //             return $this->returnError(500, 'already Request');
    //         }
    //         $requestComplete = $user->request_complete()->create([
    //             'cv' => $cv,
    //             'self_identity' => $self_identity,
    //             'phone' => isset($request->phone) ? $request->phone : null,
    //             'status' => 0
    //         ]);
    //         $requestComplete->save();
    //         DB::commit();
    //         return $this->returnData($requestComplete, 'operation completed successfully');
    //     } catch (\Exception $ex) {
    //         DB::rollback();
    //         return $this->returnError($ex->getCode(), $ex->getMessage());
    //     }
    // }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $requestComplete = CompleteTeacher::find($id);
            if (!$requestComplete) {
                return $this->returnError('not found request', 404);
            }
            if ($requestComplete->status == 1) {
                return $this->returnError('The request is notarized', 500);
            }
            $requestComplete->delete();
            DB::commit();
            return $this->returnData(200,'delete order successfully');
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }
    public function accept_request_complete_teacher($id)
    {
        try {
            DB::beginTransaction();
            $rate = 0;
            $requestComplete = CompleteTeacher::with('teacher')->find($id);
            
            return $this->returnData($requestComplete, 'accept request complete successfully');
            if (!$requestComplete) {
                return $this->returnError('not found request', 404);
            }
            if ($requestComplete->status == 1) {
                return $this->returnError('The request is notarized', 500);
            }
            $requestComplete->update([
                'status' => 1
            ]);
            $requestComplete->save();
            if ($requestComplete->cv) {
                $rate = $rate + 1;
            }
            if ($requestComplete->self_identity) {
                $rate = $rate + 1;
            }
            if ($requestComplete->phone) {
                $rate = $rate + 1;
            }
            // DB::table('request_complete')
            //     ->join('profile_teachers', 'teacher.id', '=', 'profile_teachers.id')
            //     ->where('request_complete.id', $id)
            //     ->update(['profile_teachers.assessing' => $rate]);
            // DB::commit();
            return $this->returnData(200, 'accept request complete successfully');
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getMessage(), $ex->getCode());
        }
    }
    public function accept_request_complete_student($id)
    {
        try {
            DB::beginTransaction();
            $rate = 0;
            $requestComplete = CompleteTeacher::with('user')->find($id);
            if (!$requestComplete) {
                return $this->returnError('not found request', 404);
            }
            if ($requestComplete->status == 1) {
                return $this->returnError('The request is notarized', 500);
            }
            $requestComplete->update([
                'status' => 1
            ]);
            $requestComplete->save();

            if ($requestComplete->self_identity) {
                $rate = $rate + 1;
            }
            if ($requestComplete->phone) {
                $rate = $rate + 1;
            }
            DB::table('request_complete')
                ->join('users', 'users.id', '=', 'request_complete.user_id')
                ->join('profile_students', 'users.id', '=', 'profile_students.user_id')
                ->where('request_complete.id', $id)
                ->update(['profile_students.assessing' => $rate]);
            DB::commit();
            return $this->returnData(200, 'accept request complete successfully');
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getMessage(), $ex->getCode());
        }
    }
}
