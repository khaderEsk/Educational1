<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;
use LaravelLegends\EloquentFilter\Concerns\HasFilter;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles,HasFilter;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'address',
        'governorate',
        'birth_date',
        'google_id',
        'code',
        'fcm_token',
        'image'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at',
        'code',
        'google_id',
        'email_verified_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function profile_teacher()
    {
        return $this->hasOne(ProfileTeacher::class, 'user_id', 'id');
    }

    public function teaching_methods()
    {
        return $this->hasMany(TeachingMethod::class, 'user_id', 'id');
    }

    public function service_teachers()
    {
        return $this->hasMany(ServiceTeacher::class, 'user_id', 'id');
    }

    public function profile_student()
    {
        return $this->hasOne(ProfileStudent::class, 'user_id', 'id');
    }

    public function ads()
    {
        return $this->hasMany(Ads::class, 'user_id', 'id');
    }


    public function intrests()
    {
        return $this->hasMany(Intrest::class, 'user_id', 'id');
    }

    public function qualification_users()
    {
        return $this->belongsToMany(
            QualificationCourse::class,
            'qualification_users',
            'user_id',
            'qualification_id'
        )->withPivot('created_at');
    }

    public function evaluation_as_student()
    {
        return $this->hasMany(Evaluation::class, 'student_id', 'id');
    }

    public function evaluation_as_teacher()
    {
        return $this->hasMany(Evaluation::class, 'teacher_id', 'id');
    }

    public function note_as_student()
    {
        return $this->hasMany(Note::class, 'student_id', 'id');
    }

    public function note_as_teacher()
    {
        return $this->hasMany(Note::class, 'teacher_id', 'id');
    }

    //    public function report_as_reporter()
    //    {
    //        return $this->morphMany(Report::class, 'reporter');
    //    }
    //
    //    public function report_as_reported()
    //    {
    //        return $this->morphMany(Report::class, 'reported');
    //    }

    // public function appointment_available()
    // {
    //     return $this->hasMany(AppointmentAvailable::class, 'user_id', 'id');
    // }


    // public function appointment_student_teacher()
    // {
    //     return $this->hasMany(
    //         AppointmentTeacherStudent::class,
    //         'user_id',
    //         'id'
    //     ) //->withPivot('id')
    //     ;
    // }

    // public function appointment_teacher_students()
    // {
    //     return $this->hasMany(
    //         AppointmentTeacherStudent::class,
    //         'teacher_id',
    //         'id'
    //     ) //->withPivot('id')
    //     ;
    // }


    public function teaching_methods_user()
    {
        return $this->belongsToMany(
            TeachingMethod::class,
            'teaching_method_users',
            'user_id',
            'teaching_method_id'
        )->withPivot('id');
    }

    //kadar
    public function wallet()
    {
        return $this->hasOne(Wallet::class, 'user_id', 'id');
    }
    //khader
    public function block()
    {
        return $this->hasOne(Block::class, 'user_id', 'id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id', 'id');
    }


    public function comments()
    {
        return $this->hasMany(Comment::class,'user_id','id');
    }

    public function isBlocked()
    {
        return $this->block()->exists();
    }


    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
