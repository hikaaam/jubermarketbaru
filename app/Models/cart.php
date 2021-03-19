<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class cart extends Model
{
    protected $table = 'cart';
    protected $guarded = [];

    public function Item()
    {
        return $this->belongsTo(item::class, 'item_id');
    }
}
