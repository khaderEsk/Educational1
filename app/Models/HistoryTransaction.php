<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryTransaction extends Model
{
    use HasFactory;
    protected $table = "history_transaction";
    protected $fillable = [
        'name',
        'image',
        'type',
        'status',
        'case',
        'value',
    ];
    protected $hidden = ['created_at', 'updated_at'];
}
