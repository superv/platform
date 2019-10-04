<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers\Relation;

use SuperV\Platform\Domains\Resource\Contracts\ProvidesTable;
use SuperV\Platform\Domains\Resource\Http\ResolvesResource;
use SuperV\Platform\Domains\UI\Jobs\MakeComponentTree;
use SuperV\Platform\Exceptions\PlatformException;
use SuperV\Platform\Http\Controllers\BaseApiController;

class RelationIndexController extends BaseApiController
{
    use ResolvesResource;

    public function __invoke()
    {
        $relation = $this->resolveRelation();

        if (! $relation instanceof ProvidesTable) {
            throw new PlatformException('This relation does not provide a table');
        }
        $table = $relation->makeTable();

        if ($this->route->parameter('data')) {
            return $table->setRequest($this->request)->build();
        }

        if ($callback = $relation->getCallback('index.config')) {
            app()->call($callback, ['table' => $table]);
        }

        return MakeComponentTree::dispatch($table)->withTokens(['res' => $relation->getRelatedResource()->toArray()]);
    }
}
