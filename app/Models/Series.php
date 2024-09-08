<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Series extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'file',
        'teaching_method_id',
    ];

    public function teaching_method()
    {
        return $this->belongsTo(TeachingMethod::class, 'teaching_method_id');
    }
}
