<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\ProxyTourRadarController;

class Wishlist extends Model
{
    use HasFactory;


    protected $fillable = ['traveler_id','wish_id', 'tour_id', 'notes'];
    protected $hidden = ['created_at', 'updated_at'];
    // protected $appends = ['tour_data'];

    public function traveler(){
        return $this->belongsTo(Traveler::class);
    }

    public function tour(){
        return $this->hasOne(Tour::class,'tour_id','tour_id');
    }

    public function getTourDataAttribute()
    {
        return ProxyTourRadarController::showTour($this->tour_id);
    }
}
