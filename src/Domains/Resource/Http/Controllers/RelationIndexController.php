<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Http\ResolvesResource;
use SuperV\Platform\Domains\Resource\Table\ResourceTable;
use SuperV\Platform\Http\Controllers\BaseApiController;

class RelationIndexController extends BaseApiController
{
    use ResolvesResource;

    public function __invoke(ResourceTable $table)
    {
        $relation = $this->resolveRelation();

        $table->setResource($relation->getRelatedResource());
        $table->setConfig($this->resolveRelation()->makeTableConfig());

        if ($this->route->parameter('data')) {
            return $table->build();
        } else {
            return ['data' => sv_compose($table->makeComponent())];
        }
    }
}