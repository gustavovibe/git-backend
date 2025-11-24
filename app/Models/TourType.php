<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourType extends Model
{
    use HasFactory;
    protected $table = 'tour_tour_types';

    protected $fillable = [
        'tour_id',
        'tour_type_id',
    ];


    protected $hidden = ['created_at', 'updated_at'];


    public function type()
    {
        return $this->belongsTo(Type::class, 'tour_type_id', 'tour_type_id');
    }
    public function tour()
    {
        return $this->belongsTo(Tour::class, 'tour_id', 'tour_id');
    }
}
