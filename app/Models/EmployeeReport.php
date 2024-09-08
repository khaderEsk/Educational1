<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeReport extends Model
{
    use HasFactory;
    
    protected $table = "employees_reportes";
    protected $fillable = [
        'nameEmployee',
        'operation',
        'name',
        'nameColumn'
    ];
}
