<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class trans_head extends Model
{
    use HasFactory;
    protected $table = 'market_transaction_head';
    protected $guarded = [];

    public function Store()
    {
        return $this->belongsTo(store::class, 'store_id');
    }
    public function Profile()
    {
        return $this->belongsTo(profile::class, 'user_id');
    }
}
