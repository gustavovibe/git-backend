<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserHistory extends Model
{
    use HasFactory;
    protected $table='users_history';

    protected $fillable=[
        'user_id',
        'action',
        'item',
    ];

   /*  protected $dates = ['action_date']; */


}
