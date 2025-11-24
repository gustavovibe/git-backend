<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departure extends Model
{
    // Since we're using our own primary key without auto-incrementing:
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'unsignedBigInteger';

    protected $fillable = [
        'id', 'tour_id', 'date', 'availability', 'departure_type', 'is_instant_confirmable',
        'currency', 'based_on', 'price_base', 'price_addons', 'price_promotion',
        'price_total_upfront', 'price_total', 'promotion', 'mandatory_addons', 'optional_extras', 'accommodations'
    ];
    protected $casts = [
        'accommodations' => 'array',
    ];
    /* // One Departure can have many Accommodations
    public function accommodations()
    {
        return $this->hasMany(Accommodation::class, 'departure', 'id');
    }
    */
}
