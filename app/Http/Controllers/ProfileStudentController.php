<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileStudentRequest;
use App\Models\ProfileStudent;
use Illuminate\Http\Request;
use App\Traits\GeneralTrait;
use Illuminate\Support\Facades\DB;

class ProfileStudentController extends Controller
{
    use GeneralTrait;

    private $uploadPath = "assets/images/profile_students";


    public function getAll(Request $request)
    {
        try {
            DB::beginTransaction();

            $profile_student = ProfileStudent::whereDoesntHave('user.block')
                ->filter($request)->get();
            if (count($profile_student) > 0)
                $profile_student->loadMissing(['user']);

            DB::commit();
            return $this->returnData($profile_student, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", 'Please try again later');
        }
    }


    public function store(ProfileStudentRequest $request)
    {
        //try {

            DB::beginTransaction();

            $user = auth()->user();

            $profile_student=ProfileStudent::firstOrNew(['user_id'=>$user->id]);
            $profile_student->educational_level = isset($request->educational_level) ? $request->educational_level : $profile_student->educational_level;
            $profile_student->phone = isset($request->phone) ? $request->phone : $profile_student->phone;
            $profile_student->save();

            $image=null;
            if(isset($request->image))
            {
                $image = $this->saveImage($request->image, $this->uploadPath);
                $this->deleteImage($user->image);
                $profile_student->image=$image;
            }

            $name=$request->name;

            $user->update([
                'name'=>isset($request->name)  ? $name :$user->name,
                'image'=>isset($request->image) ? $image : $user->image
            ]);
            $profile_student->name=$name;


            DB::commit();
            return $this->returnData($profile_student, __('backend.operation completed successfully', [], app()->getLocale()));
//        } catch (\Exception $ex) {
//            DB::rollback();
//            return $this->returnError("500", $ex->getMessage());
//        }
    }


    public function show()
    {

        try {
            DB::beginTransaction();

            $user=auth()->user();
            $user->loadMissing('profile_student');

            DB::commit();
            return $this->returnData($user, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", 'Please try again later');
        }
    }



    public function getById($id)
    {
        try {
            DB::beginTransaction();

            $profile_student = ProfileStudent::find($id);
            if (!$profile_student)
                return $this->returnError("404", 'Not found');
            $profile_student->loadMissing(['user']);

            DB::commit();
            return $this->returnData($profile_student, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", 'Please try again later');
        }
    }


    public function destroy()
    {
        try {
            DB::beginTransaction();

            $user = auth()->user();

            $profile_student = $user->profile_student()->first();
            if (!$profile_student)
                return $this->returnError("404", 'not found');
            $profile_student->delete();

            DB::commit();
            return $this->returnSuccessMessage(__('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", 'Please try again later');
        }
    }

    public function getByIdForAdmin($id)
    {
        try {
            DB::beginTransaction();

            $profile_student = ProfileStudent::find($id);

            if (!$profile_student)
                return $this->returnError("404", 'not found');

            $profile_student->loadMissing(['user.wallet', 'note_as_student', 'reservation_ads.ads' => function ($query) {
                $query->select('ads.id','ads.title');
            }]);
            $profile_student->loadCount([
                'report_as_reporter',
                'report_as_reported',
                'reservation_teaching_methods_free' ,
                'reservation_teaching_methods_paid'
            ]);


            DB::commit();
            return $this->returnData($profile_student, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", 'Please try again later');
        }
    }

}
