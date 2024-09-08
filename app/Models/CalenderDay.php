<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalenderDay extends Model
{
    use HasFactory;
    protected $table = "calendar_days";
    protected $fillable = ['day'];
    protected $hidden = ['created_at', 'updated_at'];

    public function teacher()
    {
        return $this->belongsTo(ProfileTeacher::class);
    }

    public function hours()
    {
        return $this->hasMany(CalendarHour::class, 'day_id', 'id');
    }
}
