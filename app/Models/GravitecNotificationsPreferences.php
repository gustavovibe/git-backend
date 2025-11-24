<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GravitecNotificationsPreferences extends Model
{
  use HasFactory;

  protected $fillable = [
    'reg_id', 't_id', 'price_change', 'dates_change'
  ];

  protected $hidden = [ 'created_at', 'updated_at'];
}
