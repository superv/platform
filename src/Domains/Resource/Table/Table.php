<?php

namespace SuperV\Platform\Domains\Resource\Table;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesUIComponent;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
use SuperV\Platform\Domains\Resource\Field\FieldComposer;
use SuperV\Platform\Domains\Resource\Table\Contracts\Table as TableContract;
use SuperV\Platform\Domains\UI\Components\Component;
use SuperV\Platform\Domains\UI\Components\ComponentContract;
use SuperV\Platform\Support\Composer\Composable;
use SuperV\Platform\Support\Composer\Tokens;

class Table implements TableContract, Composable, ProvidesUIComponent, Responsable
{
    /** @var Collection */
    protected $rows;

    protected $rowActions = [];

    protected $contextActions = [];

    protected $dataUrl;

    protected $mergeFields;

    protected $fields;

    protected $selectable = true;

    public function mergeFields($fields)
    {
        $this->mergeFields = $fields;

        return $this;
    }

    public function build()
    {
        $fields = $this->makeFields();

        $this->rows = $this->buildRows($fields);

        return $this;
    }

    public function addRowAction($action): TableContract
    {
        $this->rowActions[] = $action;

        return $this;
    }

    public function addContextAction($action): TableContract
    {
        $this->contextActions[] = $action;

        return $this;
    }

    protected function copyMergeFields()
    {
        return wrap_collect($this->mergeFields)
            ->map(function (Field $field) {
                return clone $field;
            });
    }

    public function compose(Tokens $tokens = null)
    {
        return [
            'rows' => $this->getRows()->all(),
        ];
    }

    public function getRows(): Collection
    {
        return $this->rows;
    }

    public function setRows($rows)
    {
        $this->rows = $rows;

        return $this;
    }

    public function toResponse($request)
    {
        return response()->json([
//            'data' => sv_compose($this->compose(), $this->makeTokens()),
            'data' => $this->compose(),
        ]);
    }

    protected function makeTokens(): array
    {
        return [];
    }

    protected function buildRows(Collection $fields): Collection
    {
        return $this->rows->map(
            function ($row) use ($fields) {
                return [
                    'id'      => $row['id'] ?? null,
                    'fields'  => $fields->map(function (Field $field) use ($row) {
                        return (new FieldComposer($field))->forTableRow($row)->get();
                    })->values(),
                    'actions' => ['view'],
                ];
            });
    }

    public function setFields($fields)
    {
        $this->fields = $fields;

        return $this;
    }

    public function makeComponent(): ComponentContract
    {
        return Component::make('sv-table')->card()->setProps($this->composeConfig());
    }

    public function composeConfig()
    {
        return (new TableComposer($this))->forConfig();
    }

    public function setActions($rowActions)
    {
        $this->rowActions = $rowActions;

        return $this;
    }

    public function getDataUrl()
    {
        if ($this->dataUrl) {
            return $this->dataUrl;
        }

        return url()->current().'/data';
    }

    public function setDataUrl($url): TableContract
    {
        $this->dataUrl = $url;

        return $this;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function getRowActions()
    {
        return $this->rowActions;
    }

    public function getContextActions()
    {
        return $this->contextActions;
    }

    public function makeFields(): Collection
    {
        return wrap_collect($this->getFields())->merge($this->copyMergeFields());
    }

    public function isSelectable(): bool
    {
        return $this->selectable;
    }
}