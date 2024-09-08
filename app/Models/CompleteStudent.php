<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompleteStudent extends Model
{
    use HasFactory;
    protected $table = "complete_students";
    protected $fillable = [
        'student_id',
        'self_identity',
        'phone',
    ];
    protected $hidden = ['created_at', 'updated_at'];

    public function student()
    {
        return $this->belongsTo(ProfileStudent::class,'student_id','id');
    }
}
