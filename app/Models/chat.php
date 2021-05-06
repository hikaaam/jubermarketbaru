<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class chat extends Model
{
    use HasFactory;
    protected $table = "chat";
    protected $guarded = [];
    public function User()
    {
        return $this->belongsTo(profile::class, 'user_id');
    }
    public function Store_user()
    {
        return $this->belongsTo(profile::class, 'store_user_id');
    }
    public function Store()
    {
        return $this->belongsTo(store::class, 'store_id');
    }
}
