<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LaravelLegends\EloquentFilter\Concerns\HasFilter;

class ProfileStudent extends Model
{
    use HasFactory,HasFilter;

    protected $fillable = [
        'phone',
        'educational_level',
        'user_id',
        //'assessing',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function intrests()
    {
        return $this->hasMany(Intrest::class, 'profile_student_id', 'id');
    }


    public function reservation_ads()
    {
        return $this->hasMany(ReservationAds::class, 'profile_student_id', 'id');
    }

    public function reservation_teaching_methods()
    {
        return $this->hasMany(ReservationTeachingMethod::class, 'profile_student_id', 'id');
    }

    public function reservation_teaching_methods_free()
    {
        return $this->hasMany(ReservationTeachingMethod::class, 'profile_student_id', 'id')
            ->whereHas('teaching_method', function ($q) {
                $q->where('price', 0);
            });
    }

    public function reservation_teaching_methods_paid()
    {
        return $this->hasMany(ReservationTeachingMethod::class, 'profile_student_id', 'id')
            ->whereHas('teaching_method', function ($q) {
                $q->where('price','>', 0);
            });
    }


    public function evaluation_as_student()
    {
        return $this->hasMany(Evaluation::class, 'student_id', 'id');
    }

    public function note_as_student()
    {
        return $this->hasMany(Note::class, 'profile_student_id', 'id');
    }

    public function report_as_reporter()
    {
        return $this->morphMany(Report::class, 'reporter');
    }

    public function report_as_reported()
    {
        return $this->morphMany(Report::class, 'reported');
    }
    //kadar
    public function hour_lock()
    {
        return $this->hasMany(LockHour::class, 'student_id', 'id');
    }
    public function request_complete()
    {
        return $this->hasOne(CompleteStudent::class, 'student_id', 'id');
    }

    public function favourites()
    {
        return $this->belongsToMany(ProfileTeacher::class, 'favourites', 'profile_student_id', 'profile_teacher_id');

    }


}
