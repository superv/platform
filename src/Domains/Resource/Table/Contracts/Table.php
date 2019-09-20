<?php

namespace SuperV\Platform\Domains\Resource\Table\Contracts;

use Illuminate\Support\Collection;
use SuperV\Platform\Domains\UI\Components\ComponentContract;

interface Table
{
    public function getTitle();

    public function setTitle($title);

    public function getFields();

    public function setOption($key, $value);

    public function makeFields(): Collection;

    public function setFields($fields);

    public function mergeFields($fields);

    public function setRows($rows);

    public function addSelectionAction($action): Table;

    public function addRowAction($action): Table;

    public function addContextAction($action): Table;

    public function getAction($name);

    public function setRowActions($actions);

    public function makeComponent(): ComponentContract;

    public function isSelectable();

    public function showIdColumn();

    public function shouldShowIdColumn(): bool;

    public function composeConfig();

    public function build();

    public function getDataUrl();

    public function setDataUrl($url): Table;

    public function getRowActions();

    public function getSelectionActions();

    public function getContextActions();

    public function getRows(): Collection;

    public function removeRowAction($actionName);
}
