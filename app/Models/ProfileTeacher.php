<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LaravelLegends\EloquentFilter\Concerns\HasFilter;

class ProfileTeacher extends Model
{
    use HasFactory,HasFilter;

    protected $fillable = [
        'user_id',
        'certificate',
        'description',
        'jurisdiction',
        //'domain',
        'status',
        'assessing',
        'image'
    ];
    protected $hidden = ['created_at', 'updated_at'];
    protected $appends = ['rate'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function ads()
    {
        return $this->hasMany(Ads::class, 'profile_teacher_id', 'id');
    }

    public function service_teachers()
    {
        return $this->hasMany(ServiceTeacher::class, 'profile_teacher_id', 'id');
    }

    public function teaching_methods()
    {
        return $this->hasMany(TeachingMethod::class, 'profile_teacher_id', 'id');
    }

    public function teaching_methods_free()
    {
        return $this->hasMany(TeachingMethod::class, 'profile_teacher_id', 'id')->where('price', 0);
    }

    public function teaching_methods_paid()
    {
        return $this->hasMany(TeachingMethod::class, 'profile_teacher_id', 'id')->where('price','>', 0);
    }


    public function evaluation_as_teacher()
    {
        return $this->hasMany(Evaluation::class, 'profile_teacher_id', 'id');
    }


    public function note_as_teacher()
    {
        return $this->hasMany(Note::class, 'profile_teacher_id', 'id');
    }

    public function domains()
    {
        return $this->hasMany(Domain::class, 'profile_teacher_id', 'id');
    }

    public function report_as_reporter()
    {
        return $this->morphMany(Report::class, 'reporter');
    }

    public function report_as_reported()
    {
        return $this->morphMany(Report::class, 'reported');
    }
    //khader
    public function day()
    {
        return $this->hasMany(CalenderDay::class, 'teacher_id', 'id');
    }
    public function request_complete()
    {
        return $this->hasOne(CompleteTeacher::class, 'teacher_id', 'id');
    }
    public function getRateAttribute()
    {
        return intval($this->evaluation_as_teacher()->avg('rate'));
    }

    public function favourites()
    {
        return $this->belongsToMany(ProfileStudent::class, 'favourites', 'profile_teacher_id', 'profile_student_id');

    }
}
