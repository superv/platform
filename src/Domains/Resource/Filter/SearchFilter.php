<?php

namespace SuperV\Platform\Domains\Resource\Filter;

use SuperV\Platform\Domains\Resource\Contracts\Filter\Filter;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;

class SearchFilter implements Filter
{
    protected $type = 'text';

    protected $name = 'search';

    protected $fields = ['user.email'];

    public function makeField()
    {
        return FieldFactory::createFromArray(['type' => $this->type, 'name' => $this->name]);
    }

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

    protected function applyRelationQuery( $query, $slug, $value, $operator = '=', $method = 'whereHas')
    {
        list($relation, $column) = explode('.', $slug);
        $query->{$method}($relation, function ( $query) use ($column, $value, $operator) {
            $query->where($column, $operator, $value);
        });
    }


    public function apply($query, $value)
    {
        if ($this->name === 'search') {
            $query->where(function ($query) use ($value) {
                foreach ($this->fields as $field) {
                    if (str_contains($field, '.')) {
                        $this->applyRelationQuery($query, $field, "%{$value}%", 'LIKE', 'orWhereHas');
                    } else {
                        $query->orWhere($field, 'LIKE', "%{$value}%");
                    }
                }
            });
        } elseif (str_contains($this->name, '.')) {
            $this->applyRelationQuery($query, $this->name, "%{$value}%", 'LIKE');
        }
    }

    public function getName(): string
    {
        return $this->name;
    }
}