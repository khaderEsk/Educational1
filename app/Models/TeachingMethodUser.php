<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeachingMethodUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'teaching_method_id',
        'profile_student_id'
    ];


}
