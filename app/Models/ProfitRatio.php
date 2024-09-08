<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfitRatio extends Model
{
    use HasFactory;
    protected $table = "profit_ratios";
    protected $fillable = [
        'type',
        'value',
    ];
    protected $hidden = ['created_at', 'updated_at', 'id'];
}
