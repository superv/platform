<?php

namespace SuperV\Platform\Domains\Resource\Filter;

use SuperV\Platform\Domains\Resource\Contracts\Filter\Filter as FilterContract;

abstract class Filter implements FilterContract
{
    public function applyBase($query, $value)
    {
        if ($this->callback) {
            return ($this->callback)($query, $value);
        }

        if (str_contains($this->name, '.')) {
            return $this->applyRelationQuery($query, $this->name, $value);
        }
        $query->where($this->slug, '=', $value);
    }

    protected function applyRelationQuery($query, $slug, $value, $operator = '=', $method = 'whereHas')
    {
        list($relation, $column) = explode('.', $slug);
        $query->{$method}($relation, function ( $query) use ($column, $value, $operator) {
            $query->where($column, $operator, $value);
        });
    }
}