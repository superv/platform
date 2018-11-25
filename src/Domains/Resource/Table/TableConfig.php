<?php

namespace SuperV\Platform\Domains\Resource\Table;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use SuperV\Platform\Domains\Context\Context;
use SuperV\Platform\Domains\Resource\Action\EditEntryAction;
use SuperV\Platform\Domains\Resource\Action\ViewEntryAction;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesColumns;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesQuery;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
use SuperV\Platform\Domains\Resource\Table\Contracts\Column;
use SuperV\Platform\Domains\UI\Components\TableComponent;
use SuperV\Platform\Support\Composer\Composition;

class TableConfig
{
    protected $uuid;

    /**
     * Table title
     *
     * @var string
     */
    protected $title;

    /**
     * @var array
     */
    protected $hiddenColumns = [];

    /** @var \Illuminate\Support\Collection */
    protected $contextActions;

    /** @var \Illuminate\Support\Collection */
    protected $rowActions;

    /** @var string */
    protected $dataUrl;

    protected $query;

    /** @var \Illuminate\Support\Collection */
    protected $columns;

    /** @var \SuperV\Platform\Domains\Context\Context */
    protected $context;

    public function __construct()
    {
        $this->uuid = Str::uuid();
    }

    public function build(): self
    {
        $this->contextActions = collect($this->contextActions ?? [])
            ->map(function ($action) {
                /** @var \SuperV\Platform\Domains\Resource\Action\Action $action */
                if (is_string($action)) {
                    $action = $action::make();
                }

                if ($this->context) {
                    $this->context->add($action)->apply();
                }

                return $action->makeComponent();
            });

        $this->rowActions = collect($this->rowActions ?? [ViewEntryAction::class, EditEntryAction::class])
            ->map(function ($action) {
                /** @var \SuperV\Platform\Domains\Resource\Action\Action $action */
                if (is_string($action)) {
                    $action = $action::make();
                }

                return $action;
            });

        return $this;
    }

    public function compose()
    {
        $composition = new Composition([
            'config' => [
                'context' => [
                    'actions' => $this->contextActions,
                ],
                'meta'    => [
                    'columns' => $this->getColumns()
                                      ->map(function (Column $column) {
                                          return ['label' => $column->getLabel(), 'name' => $column->getName()];
                                      })
                                      ->all(),
                ],
                'dataUrl' => $this->getDataUrl(),
            ],
        ]);

        return $composition;
    }

    public function makeTable(): Table
    {
        $table = Table::config($this);

        return $table->build();
    }

    public function getDataUrl()
    {
        return $this->dataUrl;
    }

    public function setDataUrl(string $dataUrl): TableConfig
    {
        $this->dataUrl = $dataUrl;

        return $this;
    }

    public function getRowActions(): Collection
    {
        return wrap_collect($this->rowActions);
    }

    public function setRowActions($rowActions): TableConfig
    {
        $this->rowActions = $rowActions;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function hideColumn(string $name)
    {
        $this->hiddenColumns[] = $name;
    }

    public function getColumns(): Collection
    {
        if ($this->columns instanceof ProvidesColumns) {
            $this->columns = $this->columns->provideColumns();
        }

        if (is_array($this->columns)) {
            $this->columns = collect($this->columns);
        }

        return $this->columns
            ->map(function (Column $column) {
                if (in_array($column->getName(), $this->hiddenColumns)) {
                    return null;
                }

                return $column;
            })
            ->filter();
    }

    public function setColumns($columns): self
    {
        $this->columns = $columns;

        return $this;
    }

    public function addColumn($column)
    {
        if ($column instanceof Field) {
            $column = TableColumn::fromField($column);
        }
        $this->columns->put($column->getName(), $column);

        return $this;
    }

    public function addColumns($columns)
    {
        collect($columns)->map(function ($column) {
            $this->addColumn($column);
        });

        return $this;
    }

    public function getContextActions(): Collection
    {
        return $this->contextActions;
    }

    public function setContextActions($contextActions)
    {
        $this->contextActions = $contextActions;

        return $this;
    }

    public function setContext(Context $context)
    {
        $this->context = $context;

        return $this;
    }

    public function newQuery()
    {
        if ($this->query instanceof ProvidesQuery) {
            return $this->query->newQuery();
        }

        return $this->query;
    }

    public function setQuery($query): self
    {
        $this->query = $query;

        return $this;
    }

    public function uuid()
    {
        return $this->uuid;
    }

    public static function make(): self
    {
        return new static;
    }
}