<?php

namespace App\Filters;

use App\Models\ContactEmail;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ContactFilters
{
    public function ContactE(Request $r){
        /*  return $r->all(); */
        $query = $r->filter;
        $start_date=Carbon::parse($r->start_date)->startofDay();
        $ends_date=Carbon::parse($r->ends_date?$r->ends_date:$r->start_date)->endofDay();

        $results = ContactEmail::where(function ($q) use ($query,$start_date,$ends_date) {
            $columns = \Schema::getColumnListing('contact_emails');
            foreach ($columns as $column) {
                $q->orWhere($column, 'LIKE', "%{$query}%");
            }
        });
        if ($start_date) {
            $results->whereBetween('created_at', [$start_date, $ends_date]);
        }
        $results->limit($r->limit?$r->limit:10);
        return $results->get();
    }
}
