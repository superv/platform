<?php

namespace SuperV\Platform\Domains\Resource\Table;

use SuperV\Platform\Domains\Resource\Action\Action;
use SuperV\Platform\Domains\Resource\Contracts\HasResource;
use SuperV\Platform\Domains\Resource\Contracts\NeedsEntry;
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

    /** @var array */
    protected $actions = [];

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
                        $field = $field->copy();

                        if ($field instanceof NeedsEntry) {
                            $field->setResource($this->resource);
                        }

//                        if ($field->getType() === 'boolean') {
//                            if (! $field->isBuilt()) {
//                                $field->build();
//                            }
//                            $this->setValue($field->getName(), $field->compose());
//                        } else if ($field->getType() === 'file') {
//                            if (! $field->isBuilt()) {
//                                $field->build();
//                            }
//                            $field->getConfig();
//                            $this->setValue($field->getName(), $field->compose());
//                        } else {
//                            $this->setValue($field->getName(), $field->getValue());
//                        }

                        $this->setValue($field->getName(), $field->presentValue());
                    });

        $this->table->getActions()
                    ->map(function (Action $action) {
                        $action = $action->copy();

                        if ($action instanceof HasResource) {
                            $action->setResource($this->resource);
                        }
                        $this->actions[] = $action->build()->compose();
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

    public function getActions(): array
    {
        return $this->actions;
    }

    public function compose()
    {
        return [
            'values'  => $this->values,
            'actions' => $this->actions,
        ];
    }
}