<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Contracts\ProvidesTable;
use SuperV\Platform\Domains\Resource\Http\ResolvesResource;
use SuperV\Platform\Domains\Resource\Table\ResourceTable;
use SuperV\Platform\Domains\UI\Jobs\MakeComponentTree;
use SuperV\Platform\Http\Controllers\BaseApiController;

class RelationIndexController extends BaseApiController
{
    use ResolvesResource;

    public function __invoke(ResourceTable $table)
    {
        $relation = $this->resolveRelation();

        if ($relation instanceof ProvidesTable) {
            $table = $relation->makeTable();
        }

        if ($this->route->parameter('data')) {
            return $table->build();
        }

        return MakeComponentTree::dispatch($table)->withTokens(['res' => $relation->getRelatedResource()->toArray()]);
    }
}