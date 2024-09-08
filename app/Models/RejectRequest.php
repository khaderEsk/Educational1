<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RejectRequest extends Model
{
    use HasFactory;
    protected $table = "reject_requests";
    protected $fillable = [
        'type',
        'name',
        'case'

    ];
}
