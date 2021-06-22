<?php

namespace App\Models\jbfood;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dokumen extends Model
{
    protected $table = 'userdokumen';
    protected $guarded = [];
    protected $connection = 'mysql';
    public $timestamps = false;
    use HasFactory;
}
