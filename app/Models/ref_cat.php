<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ref_cat extends Model
{
    protected $table = 'ref_category';
    protected $guarded = [];

    public function child()
    {
        return $this->hasMany(category::class, 'ref_category');
    }
}
