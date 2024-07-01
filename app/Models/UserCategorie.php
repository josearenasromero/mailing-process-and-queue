<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCategorie extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'user_id',
        'category_id',
        'created_at',
    ];

    protected $table = 'user_categories';
}
