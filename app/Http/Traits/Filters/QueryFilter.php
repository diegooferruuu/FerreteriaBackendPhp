<?php

namespace App\Http\Traits\Filters;

use App\Http\Services\Filters\{WhereClause, OrderByClause, WhereBetweenClause, WhereInClause, WhereLikeClause, GreaterThanClause, LessThanClause, GreaterOrEqualThanClause, LessOrEqualThanClause};
use App\Http\Traits\Filters\Resolvings;
use Illuminate\Pipeline\Pipeline;

trait QueryFilter
{
    use Resolvings;

    private $availableFilters = [
        'default' => WhereClause::class,
        'sort' => OrderbyClause::class,
        'in' => WhereInClause::class,
        'like' => WhereLikeClause::class,
        'between' => WhereBetweenClause::class,
        'gt' => GreaterThanClause::class,
        'gte' => GreaterOrEqualThanClause::class,
        'lt' => LessThanClause::class,
        'lte' => LessOrEqualThanClause::class,
    ];

    private $defaultFilters = [
        'sort',
        'like'
    ];

    public function scopeFilter($query, ...$filters)
    {
        $filters = collect($this->getFilters($filters))->map(function ($values, $filter) {
            return $this->resolve($filter, $values);
        })->toArray();
        
        return app(Pipeline::class)
            ->send($query)
            ->through($filters)
            ->thenReturn();
    }

    private function getFilters($filters)
    {
        $filter = function ($key) use ($filters) {

            $filters = $filters ?: $this->getMergeFilters() ?: [];
            
            return in_array($key, $filters);
        };

        return array_filter(request()->query(), $filter, ARRAY_FILTER_USE_KEY) ?? [];
    }

    private function getMergeFilters()
    {
        return array_merge($this->defaultFilters, $this->filters ?? []);
    }
}
