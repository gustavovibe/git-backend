<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContacUs extends Model
{
    use HasFactory;

    public $fillable=[
        'name',
        'last',
        'email',
        'topic',
        'booking',
        'link',
        'message'
    ];
}
