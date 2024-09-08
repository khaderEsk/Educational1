<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarHour extends Model
{
    use HasFactory;
    protected $table = "calendar_hours";
    protected $fillable = ['status', 'hour', 'day_id'];
    protected $hidden = ['created_at', 'updated_at'];

    public function day()
    {
        return $this->belongsTo(CalenderDay::class);
    }

    public function service()
    {
        return $this->belongsTo(ServiceTeacher::class);
    }
    public function hour_lock()
    {
        return $this->hasMany(LockHour::class, 'hour_id', 'id');
    }
}
