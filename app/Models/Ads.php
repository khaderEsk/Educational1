<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LaravelLegends\EloquentFilter\Concerns\HasFilter;

class Ads extends Model
{
    use HasFactory,HasFilter;

    protected $fillable = [
        'title',//
        'price',//
        'description',//
        'file',//
        'number_students',//
        'profile_teacher_id',
        'status',
        'place',//
        'start_date',//
        'end_date',
        'active'
    ];

    public function profile_teacher()
    {
        return $this->belongsTo(ProfileTeacher::class, 'profile_teacher_id');
    }

    public function reservation_ads()
    {
        return $this->hasMany(ReservationAds::class,'ads_id','id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class,'ads_id','id');
    }
}
