<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Verification extends Model
{
    use HasFactory;

    protected $table = 'verified_emails';

    protected $fillable = [
        'id',
        'email',
        'verified',
        'code',
    ];
    protected $hidden = ['created_at', 'updated_at'];
}
