<?php

namespace App\Models\jbfood;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestoReview extends Model
{
    protected $table = 'restoreview';
    protected $guarded = [];
    protected $connection = 'mysql';
    use HasFactory;
}
