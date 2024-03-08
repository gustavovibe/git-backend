<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_category', 'title', 'description', 'img'
    ];

    public function category()
    {
        return $this->hasOne(Category::class, 'id', 'id_category');
    }

    public function video()
    {
        return $this->hasMany(Video::class, 'id_course');
    }
}
