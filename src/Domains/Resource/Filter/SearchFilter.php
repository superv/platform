<?php

namespace SuperV\Platform\Domains\Resource\Filter;

use SuperV\Platform\Domains\Resource\Field\FieldFactory;

class SearchFilter extends Filter
{
    protected $type = 'text';

    protected $name = 'search';

    protected $fields = [];

    public function makeField()
    {
        return FieldFactory::createFromArray([
            'type' => $this->type,
            'name' => $this->name,
            'placeholder' => 'Search'
        ]);
    }


    public function apply($query, $value)
    {
        if ($this->name === 'search') {
            $query->where(function ($query) use ($value) {
                foreach ($this->getFields() as $field) {
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

    public function setFields(array $fields): SearchFilter
    {
        $this->fields = $fields;

        return $this;
    }

    public function getFields(): array
    {
        return $this->fields;
    }
}