<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    use HasFactory;
    protected $fillable = [
        'rate',
        'profile_student_id',
        'profile_teacher_id'
    ];

    public function profile_student()
    {
        return $this->belongsTo(ProfileStudent::class, 'profile_student_id');
    }

    public function profile_teacher()
    {
        return $this->belongsTo(ProfileTeacher::class, 'profile_teacher_id');
    }
}
