<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class courier extends Model
{
    use HasFactory;
    protected $table = 'user_courier';
    protected $guarded = [];

    public function Profile()
    {
        return $this->belongsTo(profile::class, 'user_id');
    }
    public function Courier()
    {
        return $this->belongsTo(ref_courier::class, 'courier_id');
    }
}
