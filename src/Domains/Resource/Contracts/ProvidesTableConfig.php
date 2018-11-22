<?php

namespace SuperV\Platform\Domains\Resource\Contracts;

use SuperV\Platform\Domains\Resource\Table\TableConfig;

interface ProvidesTableConfig
{
    public function provideTableConfig(): TableConfig;
}