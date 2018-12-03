<?php

namespace SuperV\Platform\Domains\Resource\Table\Contracts;

use Illuminate\Support\Collection;
use SuperV\Platform\Domains\UI\Components\ComponentContract;

interface Table
{
    public function getFields();

    public function makeFields(): Collection;

    public function setFields($fields);

    public function mergeFields($fields);

    public function setRows($rows);

    public function addAction($action): Table;

    public function addContextAction($action): Table;

    public function setActions($actions);

    public function makeComponent(): ComponentContract;

    public function isSelectable();

    public function composeConfig();

    public function build();

    public function getDataUrl();

    public function setDataUrl($url): Table;

    public function getRowActions();

    public function getContextActions();
}