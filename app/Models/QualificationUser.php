<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QualificationUser extends Model
{
    protected $fillable = [
        'user_id',
        'qualification_id'
    ];
}
