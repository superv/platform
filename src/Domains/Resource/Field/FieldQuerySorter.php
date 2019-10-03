<?php

namespace SuperV\Platform\Domains\Resource\Field;

use SuperV\Platform\Domains\Resource\Field\Contracts\SortsQuery;

class FieldQuerySorter
{
    /** @var \Illuminate\Database\Eloquent\Builder */
    protected $query;

    /** @var \SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface */
    protected $field;

    public function sort($direction)
    {
        $fieldType = $this->field->getFieldType();

        if (! $fieldType instanceof SortsQuery) {
            return;
        }

        $fieldType->sortQuery($this->query, $direction);
    }

    public function setQuery($query)
    {
        $this->query = $query;
    }

    /**
     * @param \SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface $field
     */
    public function setField(\SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface $field): void
    {
        $this->field = $field;
    }
}
