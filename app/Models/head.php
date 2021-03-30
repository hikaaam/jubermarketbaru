<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class head extends Model
{
    protected $table = 'transaction_head';
    protected $guarded = [];
    public $timestamps = false;
}
