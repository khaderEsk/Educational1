<?php

namespace App\Http\Controllers;

use App\Models\Series;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;

class ReservationSeriesController extends Controller
{
    use GeneralTrait;
    public function getSeries()
    {
        try {
            $profile_student = auth()->user()->profile_student()->first();
            $teaching_methods=[];
            if ($profile_student) {
                $teaching_methods = $profile_student->reservation_teaching_methods()
                    ->with(['teaching_method'])
                    ->whereHas('teaching_method.series')
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->pluck('teaching_method')
                    ->unique('id');

            }
            return $this->returnData($teaching_methods, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            return $this->returnError("500", $ex->getMessage());
        }
    }

    public function getByIdSeries($id)
    {
        try {
            $profile_student = auth()->user()->profile_student()->first();
            if ($profile_student) {
                $series = Series::find($id);
                if (!$series)
                    return $this->returnError("404", 'not found');
                $exists = $profile_student->reservation_teaching_methods()
                    ->whereHas('teaching_method.series', function ($query) use ($id) {
                        $query->where('id', $id);
                    })
                    ->exists();
                return $exists
                    ? $this->returnData($series, __('backend.operation completed successfully', [], app()->getLocale()))
                    : $this->returnError("404", 'not found');
            }
        } catch
        (\Exception $ex) {
            return $this->returnError("500", $ex->getMessage());
        }
    }

    public function getSeriesForTeaching($id)
    {
        try {
            $profile_student = auth()->user()->profile_student()->first();

            if ($profile_student) {
                $teaching_method = $profile_student->reservation_teaching_methods()
                    ->with(['teaching_method.series'])
                    ->whereHas('teaching_method', function ($query) use ($id) {
                        $query->where('id', $id);
                    })
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->pluck('teaching_method')
                    ->unique('id');

            }
            return $teaching_method
                ? $this->returnData($teaching_method, __('backend.operation completed successfully', [], app()->getLocale()))
                : $this->returnError("404", 'Teaching method not found');

        } catch (\Exception $ex) {
            return $this->returnError("500", $ex->getMessage());
        }
    }
}
