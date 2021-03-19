<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpKernel\Profiler\Profile;

class cart_ref extends Model
{
    protected $table = 'cart_header';
    protected $guarded = [];

    public function Store()
    {
        return $this->belongsTo(store::class, 'store_id');
    }
}
