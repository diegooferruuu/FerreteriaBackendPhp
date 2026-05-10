<?php

namespace App\Http\Services\Filters;

use Illuminate\Database\Eloquent\Builder;

class LessThanClause extends BaseClause
{
    protected function apply($query): Builder
    {
        [$field, $value] = $this->normalizeValues();

        return $query->{$this->determineMethod($value)}($field, '<', $value);
    }

    protected function validate($value): bool
    {
        if(is_null($value)) return false;

        if(!hasComma($value)) return false;

        return true;
    }

    protected function normalizeValues()
    {
        return separateCommaValues($this->values);
    }

    protected function determineMethod($value)
    {
        return isDateTime($value) ? 'whereDate' : 'where';
    }
}