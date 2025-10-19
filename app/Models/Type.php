<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    use HasFactory;
    protected $table = 'tour_types';

    protected $fillable = [
        'tour_type_id',
        'tourtype_name',
        'group_id',
        'group_name',
    ];


    protected $hidden = ['created_at', 'updated_at'];

    public function type()
    {
        return $this->hasMany(TourType::class, 'tour_id', 'tour_id')->with('type');
    }

    public function type_t()
    {
        return $this->hasMany(TourType::class, 'tour_type_id', 'tour_type_id');
    }

}
