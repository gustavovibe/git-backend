<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    use HasFactory;
    protected $table = 'tour_types';

    protected $fillable = [
        'tourtype_id',
        'tourtype_name',
    ];


    protected $hidden = ['created_at', 'updated_at'];
}
