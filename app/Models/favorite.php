<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class favorite extends Model
{
    use HasFactory;
    protected $table = 'favorite';
    protected $guarded = [];

    public function Item()
    {
        return $this->belongsTo(item::class, 'item_id')->where('is_shown',1)->where('service','jbmarket');
    }
    public function Profile()
    {
        return $this->belongsTo(profile::class, 'user_id');
    }
}
