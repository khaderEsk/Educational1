<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Intrest extends Model
{
    use HasFactory;
    protected $fillable = [
        'type',
        'profile_student_id'
    ];

    public function user()
    {
        return $this->belongsTo(ProfileStudent::class, 'profile_student_id');
    }
}
