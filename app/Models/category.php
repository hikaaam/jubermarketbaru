<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class category extends Model
{
    protected $table = 'category_tokopedia';
    protected $guarded = [];

    public function child()
    {
        return $this->hasMany(catTokpedChild::class, 'parent_category');
    }
}
