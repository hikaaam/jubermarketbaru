<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class review extends Model
{
    protected $table = 'review';
    protected $guarded = [];

    public function Profile()
    {
        return $this->belongsTo(profile::class, 'user_id');
    }
}
