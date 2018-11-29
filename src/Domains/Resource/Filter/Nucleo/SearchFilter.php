<?php

namespace SuperV\Modules\Nucleo\Domains\Resource\Table\Filter;

use Illuminate\Database\Eloquent\Builder;

class SearchFilter extends Filter
{
    protected $slug = 'search';

    public function apply(Builder $query, $value)
    {
        if ($this->slug === 'search') {
            $query->where(function (Builder $query) use ($value) {
                $fields = $this->resource->getSearch() ?? [];
                foreach ($fields as $field) {
                    if (str_contains($field, '.')) {
                        $this->applyRelationQuery($query, $field, "%{$value}%", 'LIKE', 'orWhereHas');
                    } else {
                        $query->orWhere($field, 'LIKE', "%{$value}%");
                    }
                }
            });
        } elseif (str_contains($this->slug, '.')) {
            $this->applyRelationQuery($query, $this->slug, "%{$value}%", 'LIKE');
        }
    }
}