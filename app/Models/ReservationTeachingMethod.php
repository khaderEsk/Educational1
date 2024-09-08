<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationTeachingMethod extends Model
{
    use HasFactory;
    protected $fillable = [
        'profile_student_id',
        'teaching_method_id',
        'reserved_at',
        'deducted'
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function profile_student()
    {
        return $this->belongsTo(ProfileStudent::class, 'profile_student_id');
    }

    public function teaching_method()
    {
        return $this->belongsTo(TeachingMethod::class, 'teaching_method_id');
    }
}
