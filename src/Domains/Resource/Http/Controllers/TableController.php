<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Table\Table;
use SuperV\Platform\Domains\Resource\Table\TableConfig;
use SuperV\Platform\Http\Controllers\BaseApiController;

class TableController extends BaseApiController
{
    public function config($uuid)
    {
        $config = TableConfig::fromCache($uuid);

        return ['data' => $config->makeComponent()->compose()];
    }

    public function data($uuid)
    {
        $config = TableConfig::fromCache($uuid);

        return ['data' => Table::config($config)->build()->compose()];
    }
}