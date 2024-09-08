<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LaravelLegends\EloquentFilter\Concerns\HasFilter;

class TeachingMethod extends Model
{
    use HasFactory,HasFilter;

    protected $fillable = [
        'title',//
        'type',//
        'description',//
        'file',//
        'status',
        'profile_teacher_id',
        'price',//
        'views'
    ];

    public function profile_teacher()
    {
        return $this->belongsTo(ProfileTeacher::class, 'profile_teacher_id');
    }
    public function reservation_teaching_methods()
    {
        return $this->hasMany(ReservationTeachingMethod::class, 'teaching_method_id', 'id');
    }
    public function series()
    {
        return $this->hasMany(Series::class, 'teaching_method_id', 'id');
    }


}
