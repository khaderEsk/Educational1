<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ads_id',
        'parent_id',
        'comment'
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ads()
    {
        return $this->belongsTo(Ads::class, 'ads_id');
    }

    public function children()
    {
        return $this->hasMany(self::class,'parent_id')->with('user:id,name');
    }

    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive.user:id,name');
    }
}
