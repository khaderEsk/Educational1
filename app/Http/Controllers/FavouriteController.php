<?php

namespace App\Http\Controllers;

use App\Http\Requests\FavouriteRequest;
use App\Models\Favourite;
use App\Models\ProfileTeacher;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;

class FavouriteController extends Controller
{
    use GeneralTrait;

    public function getMyFavourite()
    {
        $user = auth()->user()->profile_student()->first();
        $profile_teachers=[];
        if($user)
        {
            $profile_teachers=$user->favourites()->get();
            $profile_teachers->loadMissing('user:id,name');
        }
        return $this->returnData($profile_teachers,__('backend.operation completed successfully', [], app()->getLocale()));

    }




    public function store(FavouriteRequest $request)
    {

        try {
            $user = auth()->user()->profile_student()->first();
            $profile_teacher = ProfileTeacher::find($request->profile_teacher_id);
            if (!$profile_teacher) {
                return $this->returnError("404",'Not found');
            }
            $is_fav = Favourite::where('profile_teacher_id', $request->profile_teacher_id)->where('profile_student_id', $user->id)->first();
            if ($is_fav == null) {
                Favourite::create(['profile_teacher_id' => $request->profile_teacher_id, 'profile_student_id' => $user->id]);
            } else {
                $is_fav->delete();
            }
            $is_fa=isset($is_fav)?true:false;
            return $this->returnData($is_fa,__('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            return $this->returnError($ex->getCode(),'Please try again later');
        }
    }

}
