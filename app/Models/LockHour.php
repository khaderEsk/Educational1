<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LockHour extends Model
{
    use HasFactory;
    protected $table = "lock_hours";
    protected $fillable = [
        'teacher_id',
        'service_id',
        'hour_id',
        'value',
        'status',
        'date',
        'appointmentAddress'
    ];
    protected $hidden = ['created_at', 'updated_at', 'id'];

    public function student()
    {
        return $this->belongsTo(ProfileStudent::class);
    }
    public function service()
    {
        return $this->belongsTo(ServiceTeacher::class);
    }
    public function hour()
    {
        return $this->belongsTo(CalendarHour::class);
    }
}
