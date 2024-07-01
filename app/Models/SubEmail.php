<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubEmail extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'email',
        'status',
        'created_date',
        'dfrom',
        'spotlight',
        'user_status',
        'elitereview',
        'gdpr',
        'details',
    ];

    protected $table = 'smtda_sub_emails';
}
