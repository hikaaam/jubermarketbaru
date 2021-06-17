<?php

namespace App\Models\jbfood;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Merchant extends Model

{
    protected $table = 'merchant';
    protected $guarded = [];
    protected $connection = 'mysql';
    use HasFactory;
}
