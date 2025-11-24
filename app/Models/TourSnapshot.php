<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourSnapshot extends Model
{
    protected $table = 'tour_snapshots';

    protected $casts = [
        'payload' => 'array',
        'price_total' => 'float',
        'total_price' => 'float',
        'flight_price' => 'float',
    ];

    protected $fillable = [
        'tour_id','tour_name','start_city','end_city','start_city_name','end_city_name',
        'countries_list','payload','snapshot_at', 'type'
    ];
}
