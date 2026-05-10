<?php

namespace App\Http\Services\Filters;

use Illuminate\Database\Eloquent\Builder;

class WhereClause extends BaseClause
{
    protected function apply($query): Builder
    {
        $method = is_array($this->values) ? 'orWhere' : 'andWhere';
        
        return $this->{ $method }($query, $this->filter, $this->values);
    }

    protected function validate($value): bool
    {
        return !is_null($value);
    }

    protected function orWhere($query, $filter, $values)
    {
        $query->where(function($query) use ($values, $filter) {
            foreach ( (array) $values as $value) {
                $query->orWhere($filter, $value);
            }
        });

        return $query;
    }

    protected function andWhere($query, $filter, $values)
    {
        foreach ( (array) $values as $value) {
            $query->where($filter, $value);
        }

        return $query;
    }
}