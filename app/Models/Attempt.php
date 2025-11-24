<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attempt extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * If your table name differs from the plural of the model ("bookings"),
     * uncomment and adjust the line below:
     *
     * protected $table = 'your_table_name';
     */

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'booking_id',
        'tour',
        'flight',
        'new_url',
        'url',
        'status',
        'duffel_res',
        'tourradar_res',
        'order_id',
        'payment_id',
        'expiration',
        'checkout_session',
        'passengers',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tour'           => 'array',
        'flight'         => 'array',
        'duffel_res'     => 'array',
        'tourradar_res'  => 'array',
        'passengers'     => 'array',
        'expiration'     => 'datetime',
    ];

    /**
     * The attributes that should be mutated to dates.
     * This is optional if you rely on $casts only.
     *
     * @var array<int, string>
     */
    // protected $dates = ['expiration'];

    /**
     * Indicates if the model should be timestamped.
     * @var bool
     */
    public $timestamps = true;
}
