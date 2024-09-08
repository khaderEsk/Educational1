<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompleteTeacher extends Model
{
    use HasFactory;
    protected $table = "complete_teachers";
    protected $fillable = [
        'teacher_id',
        'cv',
        'self_identity',
        'phone',
        'status',
    ];
    protected $hidden = ['created_at', 'updated_at'];

    public function teacher()
    {
        return $this->belongsTo(ProfileTeacher::class);
    }
}
