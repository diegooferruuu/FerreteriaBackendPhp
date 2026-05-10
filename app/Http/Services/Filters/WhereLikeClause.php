<?php

namespace App\Http\Services\Filters;

use Illuminate\Database\Eloquent\Builder;

class WhereLikeClause extends BaseClause
{
    protected function apply($query): Builder
    {
        $normalized = $this->normalizeValues();

        $query->where(function($query) use ($normalized) {
            foreach ($normalized as $field => $values) {
                foreach ($values as $value) {
                    $query->orWhere($field, 'ILIKE', "%$value%");
                }
            }
        });

        return $query;
    }

    protected function validate($value): bool
    {
        if( is_null($value) ) {
            return false;
        }

        foreach ( (array) $value as $item ) {
            if( count( separateCommaValues($item) ) != 2 ) {
                return false;
            }
        }

        return true;
    }

    private function normalizeValues()
    {
        $normalized = [];

        foreach ( (array) $this->values as $value) {
            [$field, $value] = separateCommaValues($value);
            $normalized[$field][] = $value;
        }

        return $normalized;
    }
}