<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    use HasFactory;
    protected $table = "channels";
    protected $fillable = [
        'status', 
        'studentId',
        'teacherId'
    ];
    protected $hidden = ['created_at', 'updated_at'];
}
