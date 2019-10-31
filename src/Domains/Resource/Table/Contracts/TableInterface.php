<?php

namespace SuperV\Platform\Domains\Resource\Table\Contracts;

use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Resource\Contracts\Filter\Filter;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\Table\Actions\SelectionAction;
use SuperV\Platform\Domains\UI\Components\ComponentContract;

interface TableInterface
{
    public function composeConfig();

    public function build();

    public function getTitle();

    public function setTitle($title);

    public function setOption($key, $value);

    public function makeFields(): Collection;

    public function getFields();

    public function setFields($fields): TableInterface;

    public function mergeFields($fields): TableInterface;

    public function makeComponent(): ComponentContract;

    public function showIdColumn();

    public function shouldShowIdColumn(): bool;

    public function getFilters(): Collection;

    public function setFilters($filters): TableInterface;

    public function mergeFilters($filters): TableInterface;

    public function addFilter(Filter $filter): TableInterface;

    public function setRequest($request): TableInterface;

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getQuery();

    public function setQuery($query): TableInterface;

    public function getPagination(): array;

    public function orderByLatest();

    public function setResource(Resource $resource): TableInterface;

    public function getDataUrl();

    public function setDataUrl($url): TableInterface;

    public function setRows($rows);

    public function getRows(): Collection;

    public function getRowActions();

    public function getSelectionActions();

    public function getContextActions();

    public function removeRowAction($actionName);

    public function addSelectionAction(SelectionAction $action): TableInterface;

    public function addRowAction($action): TableInterface;

    public function addContextAction($action): TableInterface;

    public function getAction($name);

    public function setRowActions($actions);

    public function notViewable(): TableInterface;

    public function notDeletable(): TableInterface;

    public function notSelectable(): TableInterface;

    public function makeSelectable(): TableInterface;

    public function isViewable(): bool;

    public function isDeletable(): bool;

    public function isEditable(): bool;

    public function isSelectable(): bool;
}
