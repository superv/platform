<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers\Relation;

use SuperV\Platform\Domains\Resource\Http\ResolvesResource;
use SuperV\Platform\Domains\Resource\Table\Contracts\TableInterface;
use SuperV\Platform\Domains\UI\Jobs\MakeComponentTree;
use SuperV\Platform\Http\Controllers\BaseApiController;

class LookupController extends BaseApiController
{
    use ResolvesResource;

    public function __invoke(TableInterface $table)
    {
        $relation = $this->resolveRelation();
        $resource = $relation->getRelatedResource();

        $table = $resource->resolveTable();
        $table->setDataUrl(url()->current().'/data')
              ->makeSelectable();

        if ($this->route->parameter('data')) {
            /** @var \Illuminate\Database\Eloquent\Builder $query */
            $query = $table->getQuery();

            $alreadyAttachedItems = $this->entry->{$relation->getName()}()
                                                ->pluck($resource->config()->getTable().'.'.$query->getModel()->getKeyName());

            $query->whereNotIn($query->getModel()->getQualifiedKeyName(), $alreadyAttachedItems);

            return $table->setRequest($this->request)->build();
        }

        return MakeComponentTree::dispatch($table)->withTokens(['res' => $resource->toArray()]);
    }
}
