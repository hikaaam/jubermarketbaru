<?php

namespace App\Models\jbfood;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appsjbfood extends Model
{
    protected $table = 'appsjbfood';
    protected $guarded = [];
    protected $connection = 'mysql';
    public $incrementing = false;
    use HasFactory;
}
