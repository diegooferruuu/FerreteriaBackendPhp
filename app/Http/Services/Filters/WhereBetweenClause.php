<?php

namespace App\Http\Services\Filters;

use Illuminate\Database\Eloquent\Builder;

class WhereBetweenClause extends BaseClause
{
    protected function apply($query): Builder
    {
        [$field, $value1, $value2] = $this->normalizeValues();

        return $query->whereBetween($field, [$value1, $value2]);
    }

    protected function validate($value): bool
    {
        return count( separateCommaValues($this->values) ) != 3 ? false : true;
    }

    private function normalizeValues()
    {
        return separateCommaValues($this->values);
    }
}