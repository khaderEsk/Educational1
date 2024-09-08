<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'reason',
        'date',
        'reporter_id',//
        'reporter_type',
        'reported_id',
        'reported_type'
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function reporter()
    {
        return $this->morphTo();
    }

    public function reported()
    {
        return $this->morphTo();
    }
}
