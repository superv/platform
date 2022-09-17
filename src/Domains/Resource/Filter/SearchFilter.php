<?php

namespace SuperV\Platform\Domains\Resource\Filter;

use SuperV\Platform\Domains\Resource\Field\FieldFactory;

class SearchFilter extends Filter
{
    protected $type = 'text';

    protected $placeholder = 'Search';

    protected $fields = [];

    public function apply($query, $value)
    {
        if ($this->getIdentifier() === 'search') {
            $query->where(function ($query) use ($value) {
                foreach ($this->getFields() as $field) {
                    if (\Str::contains($field, '.')) {
                        $this->applyRelationQuery($query, $field, "%{$value}%", 'LIKE', 'orWhereHas');
                    } else {
                        $query->orWhere($field, 'LIKE', "%{$value}%");
                    }
                }
            });
        } elseif (\Str::contains($this->getIdentifier(), '.')) {
            $this->applyRelationQuery($query, $this->getIdentifier(), "%{$value}%", 'LIKE');
        }
    }

    public function getIdentifier(): string
    {
        return 'search';
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function setFields(array $fields): SearchFilter
    {
        $this->fields = $fields;

        return $this;
    }
}