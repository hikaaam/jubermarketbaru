<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class follow extends Model
{
    use HasFactory;
    protected $table = 'follow';
    protected $guarded = [];

    public function store()
    {
        return $this->belongsTo(store::class, 'store_id');
    }
    public function Profile()
    {
        return $this->belongsTo(profile::class, 'user_id');
    }
}
