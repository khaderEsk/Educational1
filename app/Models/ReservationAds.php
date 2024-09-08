<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationAds extends Model
{
    use HasFactory;
    protected $fillable = [
        'profile_student_id',
        'ads_id',
        'reserved_at',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function profile_student()
    {
        return $this->belongsTo(ProfileStudent::class, 'profile_student_id');
    }

    public function ads()
    {
        return $this->belongsTo(Ads::class, 'ads_id');
    }
}
