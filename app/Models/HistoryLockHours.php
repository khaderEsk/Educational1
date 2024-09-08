<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryLockHours extends Model
{
    use HasFactory;
    protected $table = "history_lock_hours";
    protected $fillable = [
        'type',
        'nameStudent',
        'hour',
        'date',
        'dateAccept',
        'day',
        'price',
        'status',
        'idProfileTeacher',
        'case'
    ];
    protected $hidden = ['created_at', 'updated_at'];
}
