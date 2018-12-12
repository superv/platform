<?php

namespace SuperV\Platform\Domains\Resource\Table;

use SuperV\Platform\Domains\Resource\Contracts\Filter\Filter;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
use SuperV\Platform\Domains\Resource\Field\FieldComposer;
use SuperV\Platform\Domains\Resource\Table\Contracts\Table;
use SuperV\Platform\Support\Composer\Payload;

class TableComposer
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Table\Contracts\Table
     */
    protected $table;

    public function __construct(Table $table)
    {
        $this->table = $table;
    }

    public function forConfig()
    {
        $payload = new Payload([
            'config' => [
                'title'             => $this->table->getTitle(),
                'selectable'        => $this->table->isSelectable(),
                'data_url'          => $this->makeDataUrl(),
                'fields'            => $this->makeFields(),
                'row_actions'       => $this->makeRowActions(),
                'selection_actions' => $this->makeSelectionActions(),
                'context_actions'   => $this->makeContextActions(),

            ],
        ]);

        if ($this->table instanceof EntryTable) {
            $payload->set('config.filters', $this->makeFilters());
        }

        return $payload->get();
    }

    protected function makeRowActions()
    {
        return collect($this->table->getRowActions())->map(function ($action) {
            if (is_string($action)) {
                $action = $action::make();
            }

            return $action;
        });
    }

    protected function makeSelectionActions()
    {
        return collect($this->table->getSelectionActions())->map(function ($action) {
            if (is_string($action)) {
                $action = $action::make();
            }

            return $action;
        });
    }

    protected function makeContextActions()
    {
        return collect($this->table->getContextActions())->map(function ($action) {
            if (is_string($action)) {
                $action = $action::make();
            }

            return $action;
        });
    }

    protected function makeFields()
    {
        $fields = $this->table->makeFields()
                              ->map(function (Field $field) {
                                  return (new FieldComposer($field))->forTableConfig();
                              })->values();

        return $fields;
    }

    /**
     * @return mixed
     */
    protected function makeFilters()
    {
        return $this->table->getFilters()->map(function (Filter $filter) {
            return (new FieldComposer($filter))->forForm();
        });
    }

    /**
     * @return string
     */
    protected function makeDataUrl(): string
    {
        return $this->table->getDataUrl().(request()->query() ? '?'.http_build_query(request()->query()) : '');
    }
}