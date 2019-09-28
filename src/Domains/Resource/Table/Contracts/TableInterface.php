<?php

namespace SuperV\Platform\Domains\Resource\Table\Contracts;

use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\UI\Components\ComponentContract;

interface TableInterface
{
    public function getTitle();

    public function setTitle($title);

    public function getFields();

    public function setOption($key, $value);

    public function makeFields(): Collection;

    public function setFields($fields);

    public function mergeFields($fields);

    public function makeComponent(): ComponentContract;

    public function isSelectable();

    public function showIdColumn();

    public function shouldShowIdColumn(): bool;

    public function composeConfig();

    public function build();

    public function getFilters(): Collection;

    public function setFilters($filters): TableInterface;

    public function setRequest($request): TableInterface;

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

    public function addSelectionAction($action): TableInterface;

    public function addRowAction($action): TableInterface;

    public function addContextAction($action): TableInterface;

    public function getAction($name);

    public function setRowActions($actions);
}
