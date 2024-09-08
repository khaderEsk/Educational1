<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompleteStudentRequest;
use App\Models\CompleteStudent;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompleteStudentController extends Controller
{

    use GeneralTrait;
    private $uploadPath = "assets/images/Complete_Students";
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            DB::beginTransaction();
            $user = auth()->user()->profile_student;
            if (!$user) {
                return $this->returnError(400, 'Token is Invalid');
            }
            $re = CompleteStudent::where('student_id', $user->id)->first();
            DB::commit();
            return $this->returnData($re, 'operation completed successfully');
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
    public function store(CompleteStudentRequest $request)
    {
        try {
            DB::beginTransaction();
            $self_identity = null;
            if (isset($request->self_identity)) {
                $self_identity = $this->saveImage($request->self_identity, $this->uploadPath);
            }
            $user = auth()->user()->profile_student;
            if (!$user) {
                return $this->returnError(400, 'Token is Invalid');
            }
            $re = CompleteStudent::where('student_id', $user->id)->first();
            if ($re) {
                return $this->returnError(400, 'already Complete');
            }
            $requestComplete = $user->request_complete()->create([
                'self_identity' => $self_identity,
                'phone' => isset($request->phone) ? $request->phone : null,
            ]);
            $requestComplete->save();
            DB::commit();
            return $this->returnData(200, 'operation completed successfully');
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }

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
    public function update(CompleteStudentRequest $request)
    {
        try {
            DB::beginTransaction();
            $self_identity = null;
            if (isset($request->self_identity)) {
                $self_identity = $this->saveImage($request->self_identity, $this->uploadPath);
            }

            $user = auth()->user()->profile_student;
            if (!$user) {
                return $this->returnError(400, 'Token is Invalid');
            }
            $requestComplete = $user->request_complete()->first();
            if (!$requestComplete) {
                return $this->returnError(404, 'Complete Not found');
            }
            $request = $user->request_complete()->update([
                'self_identity' => isset($request->self_identity) ? $request->self_identity : null,
                'phone' => isset($request->phone) ? $request->phone : null,
            ]);
            DB::commit();
            return $this->returnData(200, 'operation completed successfully');
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
