<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class trans_return extends Model
{
    use HasFactory;
    protected $table = 'market_transaction_return';
    protected $guarded = [];
    public function Store()
    {
        return $this->belongsTo(store::class, 'store_id');
    }
    public function Profile()
    {
        return $this->belongsTo(profile::class, 'user_id');
    }
    public function Trans_head()
    {
        return $this->belongsTo(trans_head::class, 'order_id');
    }
    public function Return_problem()
    {
        return $this->belongsTo(return_problem::class, 'problem_id');
    }
}
