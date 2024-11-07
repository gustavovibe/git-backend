<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravelGuideGallery extends Model
{
  use HasFactory;

  protected $table = 'travel_guide_gallery';

  protected $fillable = [
      't_id',
      'name',
      'unsplash_id',
      'url',
      'author',
      'author_url',
  ];

}
