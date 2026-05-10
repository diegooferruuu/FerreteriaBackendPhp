<?php

namespace App\Http\Traits\Filters\Models;

trait ProductoFilters {

    protected $filters = [
        'grupo_id', // library
        'atributo_id', // custom filter,
        'search',
        'no_sales',
    ];

    public function atributo_id($query, $value) {

        $query->whereRelation('atributos', 'atributo_id', $value);

        return $query;
    }

    public function search($query, $value) {
        $query->whereLike(['producto', 'descripcion'], $value);
        return $query;
    }

    public function no_sales($query, $value) {
        if(!in_array($value, ['now', 'yesterday', 'month', 'last_month', 'year', 'all'])) {
            return $query;
        }

        $query->whereNotIn('id', function($query) use ($value) {
            $query->select('i.producto_id')
            ->distinct()
            ->from('inventario AS i')
            ->join('detalle_venta AS dv', 'dv.inventario_id', 'i.id')
            ->join('ventas AS v', 'dv.venta_id', 'v.id')
            ->when($value == 'today', function ($query) {
                $query->whereDate('v.fecha', now());
            })
            ->when($value == 'yesterday', function ($query) {
                $query->whereDate('v.fecha', now()->subDays(1));
            })
            ->when($value == 'month', function ($query) {
                $query->whereMonth('v.fecha', now()->month)->whereYear('v.fecha', now()->year);
            })
            ->when($value == 'last_month', function ($query) {
                $lastMonth = now()->subMonths(1)->month;
                $year =  $lastMonth == 12 ? (now()->year - 1) : now()->year;
                $query->whereMonth('v.fecha', $lastMonth)->whereYear('v.fecha', $year);
            })
            ->when($value == 'year', function ($query) {
                $query->whereYear('v.fecha', now()->year);
            });
        });
    }
}
