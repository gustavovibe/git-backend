namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $primaryKey = 'booking_id';

    protected $fillable = [
        'booking_id',
        'created',
        'departure',
        'start',
        'arrival',
        'end',
        'duration',
        'tour_length',
        'tour_name',
        'tour_id',
        'style',
        'operator',
        'start_city',
        'end_city',
        'booking_status',
        'duffel_status',
        'tourradar_status',
        'tourradar_reason',
        'tourradar_text',
        'duffel',
        'source',
        'device',
        'affiliate',
        'origin',
        'f_destination',
        'f_return',
        'f_duration',
        'destination_stops',
        'return_stops',
        'total_stops',
        'destination_carrier',
        'return_carrier',
        'checked_bags',
        'title',
        'gender',
        'name',
        'country',
        'city',
        'address',
        'zip',
        'birthdate',
        'mail',
        'mobile',
        'travelers',
        'reference',
        'method',
        'currency',
        'invoice',
        'paid',
        'fees',
        'markup',
        'refunded',
        'p_flight',
        'p_tour',
        'discounted',
        'promo',
        'profit',
        'ratio',
        'suscribed',
        'hear',
    ];
}
