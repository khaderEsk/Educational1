<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LaravelLegends\EloquentFilter\Concerns\HasFilter;

class Notification extends Model
{
    use HasFactory,HasFilter;
    public $table = 'notifications';
    protected $fillable = [
        'title',
        'body',
        'seen',
        'url',
        'user_id'
    ];
    protected $hidden = [
        'updated_at',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
