<?php

namespace App\Http\Traits;

use Carbon\Carbon;

trait QueryReport
{
    public function scopeWithDate($query, $field) {
        $request = request();
        $withDate = $request->get('with_date');

        switch ($withDate) {
            case 'today':
                return $query->whereDate($field, now());
            
            case 'yesterday':
                return $query->whereDate($field, now()->subDays(1));
            
            case 'month':
                return $query->whereMonth($field, now()->month)->whereYear($field, now()->year);
            
            case 'last_month':
                $lastMonth = now()->subMonths(1)->month;
                $year =  $lastMonth == 12 ? (now()->year - 1) : now()->year;
            
                return $query->whereMonth($field, $lastMonth)->whereYear($field, $year);

            case 'year':
                return $query->whereYear($field, now()->year);
            
            default:
                return $query;
        }

    }
}