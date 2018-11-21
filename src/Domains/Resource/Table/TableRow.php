<?php

namespace SuperV\Platform\Domains\Resource\Table;

use SuperV\Platform\Domains\Context\Negotiator;
use SuperV\Platform\Domains\Resource\Action\Action;
use SuperV\Platform\Domains\Resource\Contracts\Providings\ProvidesResourceEntry;
use SuperV\Platform\Domains\Resource\Contracts\Requirements\AcceptsResourceEntry;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Model\ResourceEntry;

class TableRow implements ProvidesResourceEntry
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

        $this->setColumnValues();

        $this->composeActions();

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

    protected function composeActions(): void
    {
        $this->table->getActions()
                    ->map(function (Action $action) {
                        if ($action instanceof AcceptsResourceEntry) {
                            $action->acceptResourceEntry($this->provideResourceEntry());
                        }
                        $this->actions[] = $action->makeComponent()->compose();
                    });
    }

    protected function setColumnValues(): void
    {
        $this->table->getFields()
                    ->map(function (Field $field) {
                        $value = $field->present($this->entry);
                        $this->setValue($field->getName(), $value);
                    });
    }

    public function provideResourceEntry(): ResourceEntry
    {
        return $this->entry;
    }
}