<?php

namespace SuperV\Platform\Domains\Resource\Table;

use Closure;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
use SuperV\Platform\Domains\Resource\Table\Contracts\AltersTableQuery;
use SuperV\Platform\Domains\Resource\Table\Contracts\Column;
use SuperV\Platform\Support\Concerns\HasConfig;

class TableColumn implements Column
{
    use HasConfig;

    /** @var string */
    protected $name;

    /** @var string */
    protected $label;

    /** @var \Closure */
    protected $presenter;

    /** @var \Closure */
    protected $alterQueryCallback;

    protected function boot()
    {
        if (str_contains($this->name, '.')) {
            [$relation, $name] = explode('.', $this->name);

            $this->presenter = function (EntryContract $entry) use ($relation, $name) {
//                dd($entry, $relation, $name);
                if ($child = $entry->{$relation}) {
                    return $child->{$name};
                }
            };

            $this->alterQueryCallback = function ($query) use ($relation) { $query->with($relation); };

            $this->name = $name;
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function getLabel()
    {
        return $this->label ?? str_unslug($this->name);
    }

    public function setLabel(string $label): Column
    {
        $this->label = $label;

        return $this;
    }

    public function getAlterQueryCallback()
    {
        return $this->alterQueryCallback;
    }

    public function present(EntryContract $entry)
    {
        return $entry->getAttribute($this->getName());
    }

    public function setTemplate(string $template): Column
    {
        $this->presenter = function (EntryContract $entry) use ($template) {
            return sv_parse($template, $entry->toArray());
        };

        return $this;
    }

    public function getPresenter()
    {
        return $this->presenter;
    }

    public function setPresenter(Closure $callback): Column
    {
        $this->presenter = $callback;

        return $this;
    }


    public static function make(string $name, ?string $label = null): Column
    {
        $column = new static;
        $column->name = $name;
        $column->label = $label;
        $column->boot();

        return $column;
    }

    public static function fromField(Field $field): Column
    {
        $column = TableColumn::make($field->getName());
        $column->setLabel($field->getLabel());

        $fieldType = $field->fieldType();
        if ($fieldType instanceof AltersTableQuery) {
            $column->alterQueryCallback = $fieldType->getAlterQueryCallback();
        }

        if ($presenter = $fieldType->getPresenter()) {
            $column->presenter = $presenter;
        }

        return $column;
    }

}