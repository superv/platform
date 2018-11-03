<?php

namespace SuperV\Platform\Domains\Resource\Table;

use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Resource;

class TableRow
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Table\Table
     */
    protected $table;

    /**
     * @var \SuperV\Platform\Domains\Resource\Resource
     */
    protected $resource;

    /** @var array */
    protected $values = [];

    public function __construct(Table $table, Resource $resource)
    {
        $this->table = $table;
        $this->resource = $resource;
    }

    public function build(): self
    {
        $this->setValue('id', $this->resource->getEntryId());

        $this->table->getColumns()
                    ->map(function (Field $field) {

                        $field->setResource($this->resource);

                        $this->setValue($field->getName(), $field->getValue());
                    });

        return $this;
    }

    public function getValues(): array
    {
        return $this->values;
    }

    protected function setValue(string $slug, $newValue)
    {
        $this->values[$slug] = $newValue;
    }
}