<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetauthorEmail extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'email_sub',
        'template',
        'date',
        'time',
        'status',
        'vip',
        'type',
        'do_or',
    ];

    protected $table = 'smtda_meetauthor_email';
    public $timestamps = false;
}
