<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Http\ResolvesResource;
use SuperV\Platform\Domains\Resource\Table\TableV2;
use SuperV\Platform\Domains\UI\Jobs\MakeComponentTree;
use SuperV\Platform\Http\Controllers\BaseApiController;

class LookupController extends BaseApiController
{
    use ResolvesResource;

    public function __invoke(TableV2 $table)
    {
        $relation = $this->resolveRelation();
        $resource = $relation->getRelatedResource();

        $table->setResource($resource);
        $table->setDataUrl(url()->current().'/data');

        if ($this->route->parameter('data')) {
            /** @var \Illuminate\Database\Eloquent\Builder $query */
            $query = $resource->newQuery();
            $alreadyAttachedItems = $this->entry->{$relation->getName()}()
                                                ->pluck($resource->getHandle().'.id');

            $query->whereNotIn($query->getModel()->getKeyName(), $alreadyAttachedItems);
            $table->setQuery($query);

            return $table->build($this->request);
        }

        return MakeComponentTree::dispatch($table)->withTokens(['res' => $resource->toArray()]);
    }
}