<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Http\Requests\RegisterEmployeeRequest;
use App\Traits\GeneralTrait;

class EmployeeController extends Controller
{
    use GeneralTrait;
    private $uploadPath = "assets/images/employees";

    public function createEmployee(RegisterEmployeeRequest $request)
    {
        try {
            DB::beginTransaction();
            $image=null;
            if(isset($request->image))
            {
                $image = $this->saveImage($request->image, $this->uploadPath);
            }

            $data=User::create([
                'name'           => $request->name,
                'email'          => $request->email,
                'password'       => $request->password,
                'address'        => $request->address,
                'governorate'    => $request->governorate,
                'birth_date'     =>$request->birth_date,
                'image'          =>$image
            ]);
            $role=Role::where('name',"employee")->first();

            $data->assignRole($role);
            $data->loadMissing('roles');
            Wallet::create([
                'user_id' => $data->id,
                'number' => random_int(1000000000000, 9000000000000),
                'value' => 0,
            ]);
            DB::commit();

            return $this->returnData($data, __('backend.operation completed successfully', [], app()->getLocale()));
        }
        catch (\Exception $ex) {
            DB::rollBack();
            return $this->returnError("500",$ex->getMessage());

        }
    }

    public function updateEmployee($id,UpdateEmployeeRequest $request)
    {
        try {
            DB::beginTransaction();
            $data=User::where('id',$id)->first();
            if (!$data) {
                return $this->returnError("404",'Not found');
            }
            if(isset($request->image))
            {
                $image = $this->saveImage($request->image, $this->uploadPath);
                $this->deleteImage($data->image);
            }

            $data->update([
                'name'           => isset($request->name)? $request->name :$data->name,
                'email'          => isset($request->email)? $request->email :$data->email,
                'password'       => isset($request->password)? $request->password :$data->password,
                'address'         => isset($request->address)? $request->address :$data->address,
                'governorate'    => isset($request->governorate)? $request->governorate :$data->governorate,
                'birth_date'     => isset($request->birth_date)? $request->birth_date :$data->birth_date,
                'image'           => isset($request->image)? $image :$data->image,
            ]);
            $data->loadMissing('roles');
            DB::commit();
            return $this->returnData($data, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->returnError("500",'Please try again later');

        }
    }

    public function getById($id)
    {
        try {
            $data=User::where('id',$id)->whereHas('roles',function ($query){
                $query->where('name',"employee");
            })->first();
            if (!$data) {
                return $this->returnError("404",'Not found');
            }
            $data->loadMissing(['roles']);
            return $this->returnData($data, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            return $this->returnError($ex->getCode(),'Please try again later');

        }
    }




    public function delete($id)
    {
        try {
            $data=User::where('id',$id)->first();
            if (!$data) {
                return $this->returnError("404",'Not found');
            }
            $this->deleteImage($data->image);
            $data->delete();
            return $this->returnSuccessMessage(__('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            return $this->returnError($ex->getCode(),'Please try again later');

        }
    }

    public function getAll()
    {
        try {

            $data = User::with('roles'
            )->whereHas('roles',function ($query){
                $query->where('id',4);
            })->get()->map(function ($user){
                $user->is_blocked = $user->isBlocked() ;
                return $user;
            });
            return $this->returnData($data, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            return $this->returnError($ex->getCode(),'Please try again later');
        }
    }

}
