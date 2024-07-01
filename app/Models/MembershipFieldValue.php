<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipFieldValue extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'field_id',
        'subscriber_id',
        'field_value'
    ];
    protected $table = 'smtda_osmembership_field_value';
}
