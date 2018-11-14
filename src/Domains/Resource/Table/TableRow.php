<?php

namespace SuperV\Platform\Domains\Resource\Table;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Database\Model\Entry;
use SuperV\Platform\Domains\Resource\Action\Action;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Model\ResourceEntry;

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

    /**
     * @var \SuperV\Platform\Domains\Resource\Model\ResourceEntry
     */
    protected $entry;

    public function __construct(Table $table, ResourceEntry $entry)
    {
        $this->table = $table;
        $this->entry = $entry;
    }

    public function build(): self
    {
        $this->setValue('id', $this->entry->id);

        $this->table->getColumns()
                    ->map(function (Field $field) {
                        $value = $this->entry->getAttribute($field->getName());

                        if ($field->hasCallback('presenting')) {
                            if ($value instanceof EntryContract) {
                                $value = ResourceEntry::make($value);
                            }

                            $callback = $field->getCallback('presenting');
                            $value = $callback($value);
                        }

                        $this->setValue($field->getName(), $value);
                    });

        $this->table->getActions()
                    ->map(function (Action $action) {
                        $this->actions[] = $action->compose($this->entry);
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