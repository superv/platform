<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Http\ResolvesResource;
use SuperV\Platform\Domains\Resource\Table\Table;
use SuperV\Platform\Domains\Resource\Table\TableConfig;
use SuperV\Platform\Http\Controllers\BaseApiController;

class LookupController extends BaseApiController
{
    use ResolvesResource;

    public function __invoke()
    {
        $resource = $this->resolveRelation()->getRelatedResource();

        $config = new TableConfig();
        $config->setColumns($resource);
        $config->setDataUrl(url()->current().'/data');
        $config->build();

        $table = Table::config($config);

        if (! $this->route->parameter('data')) {
            return ['data' => sv_compose($table->makeComponent())];
        }

        $relation = $this->resolveRelation();

        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = $relation->getRelatedResource()->newQuery();

        $alreadyAttachedItems = $this->entry->{$relation->getName()}()
                                            ->pluck($relation->getRelatedResource()->getHandle().'.id');

        $query->whereNotIn($query->getModel()->getKeyName(), $alreadyAttachedItems);
        $table->setQuery($query);
         $table->build();

        return ['data' => sv_compose($table, ['res' => $resource->toArray()])];
    }
}