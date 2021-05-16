<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class chat_admin extends Model
{
    use HasFactory;
    protected $table = "chat_admin";
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
    public function Return()
    {
        return $this->belongsTo(trans_return::class, 'return_id');
    }
    public function Trans_head()
    {
        return $this->belongsTo(trans_head::class, 'trans_head_id');
    }
}
