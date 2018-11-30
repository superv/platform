<?php

namespace SuperV\Platform\Domains\Resource\Table;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Action\Action;
use SuperV\Platform\Domains\Resource\Table\Contracts\Column;
use SuperV\Platform\Support\Composer\Composable;
use SuperV\Platform\Support\Composer\Payload;

class TableRow implements Composable
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Table\Table
     */
    protected $table;

    /**
     * @var array
     */
    protected $values = [];

    /**
     * @var array
     */
    protected $actions = [];

    /**
     * @var \SuperV\Platform\Domains\Resource\Model\ResourceEntry
     */
    protected $entry;

    public function __construct(Table $table, EntryContract $entry)
    {
        $this->table = $table;
        $this->entry = $entry;
    }

    public function build(): self
    {
        $this->setValue('id', $this->entry->id);

        $this->setColumnValues();

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

    public function compose(\SuperV\Platform\Support\Composer\Tokens $tokens = null)
    {
        $composer = new Payload([
            'values' => $this->values,
            'actions' => $this->table->getActions()->map(function (Action $action) {
                return $action->makeComponent();
            }),
        ]);

        return sv_compose($composer, ['entry' => $this->entry]);
    }

    protected function setColumnValues(): void
    {
        $this->table->getColumns()
                    ->map(function (Column $column) {
                        if ($callback = $column->getPresenter()) {
//                            $value = $callback($this->entry);
                            $value = app()->call($callback, ['entry' => $this->entry]);
                        } else {
                            $value = $this->getValueFromEntry($column->getName());
                        }

                        $this->setValue($column->getName(), $value);
                    });
    }

    protected function getValueFromEntry($name)
    {
//        if (str_contains($name, '.')) {
//            [$relation, $field] = explode('.', $name);
//
//            return optional($this->entry->{$relation})->getAttribute($field);
//        }

        return $this->entry->getAttribute($name);
    }
}