<?php

namespace SuperV\Platform\Domains\Resource\Table;

use Current;
use SuperV\Platform\Domains\Resource\Action\Action;
use SuperV\Platform\Domains\Resource\Contracts\Filter\Filter;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;
use SuperV\Platform\Domains\Resource\Table\Contracts\TableInterface;
use SuperV\Platform\Support\Composer\Payload;

class TableComposer
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Table\Contracts\TableInterface
     */
    protected $table;

    public function __construct(TableInterface $table)
    {
        $this->table = $table;
    }

    public function forConfig()
    {
        $payload = new Payload([
            'config' => [
                'title'             => $this->table->getTitle(),
                'selectable'        => $this->table->isSelectable(),
                'show_id_column'    => $this->table->shouldShowIdColumn(),
                'data_url'          => $this->makeDataUrl(),
                'fields'            => $this->makeFields(),
                'row_actions'       => $this->makeRowActions(),
                'selection_actions' => $this->makeSelectionActions(),
                'context_actions'   => $this->makeContextActions(),
                'filters'           => $this->table->getFilters()
                                                   ->map(function (Filter $filter) {
                                                       return $filter->makeField()->getComposer()->toForm()->get();
                                                   }),

            ],
        ]);

        return $payload->get();
    }

    protected function makeRowActions()
    {
        return collect($this->table->getRowActions())
            ->filter(function (Action $action) {
                return Current::user()->can($action->getIdentifier());
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
                              ->map(function (FieldInterface $field) {
                                  return $field->getComposer()->toTable();
                              })->values();

        return $fields;
    }

    /**
     * @return string
     */
    protected function makeDataUrl(): string
    {
        return $this->table->getDataUrl().(request()->query() ? '?'.http_build_query(request()->query()) : '');
    }
}
