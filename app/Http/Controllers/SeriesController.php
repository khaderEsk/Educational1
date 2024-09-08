<?php

namespace App\Http\Controllers;

use App\Http\Requests\SeriesRequest;
use App\Http\Requests\UpdateSeriesRequest;
use App\Models\ProfileTeacher;
use App\Models\Series;
use App\Models\TeachingMethod;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeriesController extends Controller
{
    use GeneralTrait;
    private $uploadPath = "assets/images/series";

    public function index($teacher_id)
    {
        try {
            $profile_teacher=ProfileTeacher::find($teacher_id);
            if (!$profile_teacher) {
                return $this->returnError("404",'Profile Teacher Not found');
            }
            $teaching_methods=$profile_teacher->teaching_methods()->whereDoesntHave('profile_teacher.user.block')
                ->whereHas('series')->orderBy('created_at','desc')->get();

            return $this->returnData($teaching_methods, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            return $this->returnError("500",$ex->getMessage());
        }
    }



    public function store(SeriesRequest $request)
    {
        try {
            DB::beginTransaction();

            $profile_teacher=auth()->user()->profile_teacher()->first();
            $teaching_method=$profile_teacher->teaching_methods()->find($request->teaching_method_id);
            if (!$teaching_method)
                return $this->returnError("404",'teaching_method Not found');

            $series = $request->series;
            $list_series = [];
            foreach ($series as $value) {
                $file = $this->saveImage($value['file'], $this->uploadPath);
                $data = [
                    'teaching_method_id' => $teaching_method->id,
                    'title'=>$value['title'],
                    'file' => $file,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                array_push($list_series, $data);
            }
            Series::insert($list_series);


            DB::commit();
            return $this->returnData($list_series, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", $ex->getMessage());
        }
    }


    public function show($id)
    {
        try {
            DB::beginTransaction();

            $profile_teacher=auth()->user()->profile_teacher()->first();
            $teaching_methods_ids=$profile_teacher->teaching_methods()->get('id');

            $series= Series::where('id',$id)->whereIn('teaching_method_id',$teaching_methods_ids)->first();
            if (!$series) {
                return $this->returnError("404",'series Not found');
            }

            DB::commit();
            return $this->returnData($series, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", 'Please try again later');
        }
    }


    public function update(UpdateSeriesRequest $request, $id)
    {
        try {
            DB::beginTransaction();

            $profile_teacher=auth()->user()->profile_teacher()->first();

            $series = Series::where('id', $id)
                ->whereHas('teaching_method', function ($query) use ($profile_teacher) {
                    $query->where('teaching_methods.profile_teacher_id', $profile_teacher->id);
                })
                ->first();

            if (!$series)
                return $this->returnError("404",'series Not found');

            $this->deleteImage($series->file);
            $file = $this->saveImage($request->file, $this->uploadPath);

            $series->update([
                'title'=>isset($request->title) ? $request->title : $series->title,
                'file'=>isset($request->file) ?$file : $series->file,
            ]);

            DB::commit();
            return $this->returnData($series, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", 'Please try again later');
        }
    }


    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $profile_teacher=auth()->user()->profile_teacher()->first();
            $series=Series::where('id',$id)->whereHas('teaching_method',function ($query)use($profile_teacher)
            {
                $query->where('teaching_methods.profile_teacher_id',$profile_teacher);
            })->first();
            if (!$series)
                return $this->returnError("404",'series Not found');

            $this->deleteImage($series->file);

            $series->delete();
            DB::commit();
            return $this->returnSuccessMessage(__('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", 'Please try again later');
        }
    }

    public function getMySeries()
    {
        try {
            $profile_teacher =auth()->user()->profile_teacher()->first();
            $series=[];
            if($profile_teacher) {
                $series = $profile_teacher->teaching_methods()->whereHas('series')->orderBy('created_at', 'desc')->get();
            }

            return $this->returnData($series, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            return $this->returnError("500", "Please try again later");
        }
    }

    public function getSeriesForTeachingFT($id)
    {
        try {
            $profile_teacher = auth()->user()->profile_teacher()->first();

            $teaching_method=[];
            if ($profile_teacher) {
                $teaching_method = $profile_teacher->teaching_methods()->where('id',$id)
                    ->whereHas('series')
                    ->with(['series'])
                    ->get();
            }
            return $this->returnData($teaching_method, __('backend.operation completed successfully', [], app()->getLocale()));

        } catch (\Exception $ex) {
            return $this->returnError("500", $ex->getMessage());
        }
    }


    public function getAll()
    {
        try {
            $teaching_methods = TeachingMethod::whereHas('series')->join('profile_teachers', 'teaching_methods.profile_teacher_id', '=', 'profile_teachers.id')->join('users', 'profile_teachers.user_id', '=', 'users.id')
                ->select('teaching_methods.*', 'users.name')->orderBy('created_at', 'desc')
                ->get();

            return $this->returnData($teaching_methods, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            return $this->returnError("500", "Please try again later");
        }
    }

    public function deleteForAdmin($id)
    {
        try {
            DB::beginTransaction();
            $series=Series::where('id',$id)->first();
            if (!$series)
                return $this->returnError("404",'series Not found');

            $this->deleteImage($series->file);
            $series->delete();
            DB::commit();
            return $this->returnSuccessMessage(__('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", 'Please try again later');
        }
    }

    public function getSeriesForTeachingA($id)
    {
        try {
            $teaching_method =TeachingMethod::find($id);
            if(!$teaching_method)
                return $this->returnError("404",'Not found');
            $teaching_method->loadMissing('series');

            return $this->returnData($teaching_method, __('backend.operation completed successfully', [], app()->getLocale()));

        } catch (\Exception $ex) {
            return $this->returnError("500", $ex->getMessage());
        }
    }
}
