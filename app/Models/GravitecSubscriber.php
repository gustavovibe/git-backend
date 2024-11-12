<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GravitecSubscriber extends Model
{
  use HasFactory;

  protected $fillable = [
    'reg_id', 'user_id', 'alias', 'is_subscribed', 'sub_data'
  ];

  protected $hidden = ['created_at', 'updated_at'];

  public function user()
  {
    return $this->hasOne(User::class, 'id', 'user_id');
  }

}
