<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateCategorie extends Model
{
    use HasFactory;

    
    protected $fillable = [
        'id',
        'template_id',
        'category_id',
    ];

    protected $table = 'template_categories';
}
