<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Http\ResolvesResource;
use SuperV\Platform\Domains\Resource\Table\Table;
use SuperV\Platform\Domains\Resource\Table\TableConfig;
use SuperV\Platform\Http\Controllers\BaseApiController;

class RelationIndexController extends BaseApiController
{
    use ResolvesResource;

    public function __invoke()
    {
        /** @var TableConfig $config */
        $config = $this->resolveRelation()->makeTableConfig();
        $table = Table::config($config);

        if ($this->route->parameter('data')) {
            return $table->build();
        } else {
            return ['data' => sv_compose($table->makeComponent()->compose())];
        }
    }
}