<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DDCategorie extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'dd_id',
        'category_id'
    ];
    protected $table = 'dd_categories';
}
