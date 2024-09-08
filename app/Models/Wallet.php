<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;
    protected $table = "wallets";
    protected $fillable = ['user_id', 'number', 'value',];
    protected $hidden = ['created_at', 'updated_at', 'id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function governor()
    {
        return $this->hasMany(Governor::class);
    }

    public function governor_charge()
    {
        return $this->hasMany(Governor::class)->where('type',"charge");
    }

    public function governor_recharge()
    {
        return $this->hasMany(Governor::class)->where('type',"recharge");
    }
}
