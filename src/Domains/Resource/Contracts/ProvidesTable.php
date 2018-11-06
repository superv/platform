<?php

namespace SuperV\Platform\Domains\Resource\Contracts;

use SuperV\Platform\Domains\Resource\Table\TableConfig;

interface ProvidesTable
{
    public function makeTableConfig(): TableConfig;
}