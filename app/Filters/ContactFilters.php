<?php

namespace App\Filters;

use App\Models\ContactEmail;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class ContactFilters{

    protected $list_status;
    protected $list_styles;
    protected $list_type;

    public function __construct() {
    $this->list_status = [
        40 => 'sent',
        50 => 'delivered',
        75 => 'opened',
        100 => 'clicked',
        35 => 'bounced'
    ];

    $this->list_styles = [
        40 => 'progressBar-blue',
        50 => 'progressBar-blue',
        75 => 'progressBar-green',
        100 => 'progressBar-green',
        35 => 'progressBar-red'
    ];

    $this->list_type=[
       1=> 'Booking confirmation',
       2=> 'Booking cancellation',
       3=> 'Payment receipt',
       4=> 'Payment request',
       5=> 'Reminder 1',
       6=> 'Reminder 2',
       7=> 'FollowUp: review',
       8=> 'FollowUp: recommend',
       9=> 'Simple email',
    ];
    }
    public function ContactE(Request $r) {
        $query = $r->filter;
        $start_date = $r->start_date ? Carbon::createFromFormat('D M d Y H:i:s e+', $r->start_date)->startOfDay() : '';
        $ends_date = $r->ends_date ? Carbon::createFromFormat('D M d Y H:i:s e+', $r->ends_date)->endOfDay() : $start_date ;

        $results = ContactEmail::where(function ($q) use ($query) {
            $columns = \Schema::getColumnListing('contact_emails');
            foreach ($columns as $column) {
                $q->orWhere($column, 'LIKE', "%{$query}%");
            }
        });

        if ($start_date) {
            $results->whereBetween('created_at', [$start_date, $ends_date]);
        }
        !$r->email?:$results->where('mail_from','like','%'.$r->email.'%');
        !$r->status?:$results->wherein('status',explode(',',$r->status));
        !$r->mail_type?:$results->wherein('mail_type',explode(',',$r->mail_type));

       /*  $results->limit($r->limit ? $r->limit : 10); */
        $results = $results->get();
        $results = $results->map(function($re) {
            $re->traveler_name = $re->traveler->name . ' ' . $re->traveler->last;
            $re->sent =  Carbon::parse($re->created_at)->format('d M Y, h:i a');
            $re->status_name = $this->list_status[$re->status];
            $re->status_style = $this->list_styles[$re->status];
            $re->mail_type_name = $this->list_type[$re->mail_type];
            unset($re->traveler);
            return $re;
        })->values();

        $perPage = $r->limit ?: 15;
        $currentPage = $r->page ?: 1;
        $results = new LengthAwarePaginator(
            $results->forPage($currentPage, $perPage),
            $results->count(),
            $perPage,
            $currentPage,
            ['path' => $r->url()]
        );

        return $results;
    }
}
