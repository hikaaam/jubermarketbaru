<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class trans extends Model
{
    use HasFactory;
    protected $table = 'market_transaction';
    protected $guarded = [];

    public function Item()
    {
        return $this->belongsTo(item::class, 'item_id');
    }

    public function Trans_head()
    {
        return $this->belongsTo(trans_head::class, 'transaction_id');
    }
}
