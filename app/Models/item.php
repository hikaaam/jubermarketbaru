<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class item extends Model
{
    use SoftDeletes;
    protected $table = 'item';
    protected $guarded = [];

    public function Variant()
    {
        return $this->hasMany(Variant::class, 'item_id');
    }
    public function Store()
    {
        return $this->belongsTo(store::class, 'store_id');
    }
}
