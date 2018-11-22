<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Contracts\HandlesRequests;
use SuperV\Platform\Domains\Resource\Http\ResolvesResource;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\Resource\Table\Table;
use SuperV\Platform\Domains\Resource\Table\TableConfig;
use SuperV\Platform\Http\Controllers\BaseApiController;

class RelationController extends BaseApiController
{
    use ResolvesResource;

    protected function resolveRelation(): Relation
    {
        $relation = $this->resolveResource()->getRelation($this->route->parameter('relation'));
        if ($this->entry) {
            $relation->acceptParentEntry($this->entry);
        }

        return $relation;
    }

    public function request()
    {
        $relation = $this->resolveRelation();

        if ($relation instanceof HandlesRequests) {
            $relation->handleRequest($this->request);
        }

        return ['status' => 'ok'];
    }

    public function table()
    {
        /** @var TableConfig $config */
        $config = $this->resolveRelation()->makeTableConfig();

        if ($this->route->parameter('data')) {
            return ['data' => Table::config($config)->build()->compose()];
        } else {
            return ['data' => sv_compose($config->makeComponent()->addClass('sv-card')->compose())];
        }
    }

    public function attach()
    {
        $this->resolveResource();
        $relationName = $this->route->parameter('relation');
        $items = $this->request->get('items');
        if ($pivotColumns = $this->resolveRelation()->getConfig()->getPivotColumns()) {
            $formData = $this->request->get('form_data');

            $items = [];
            foreach ($this->request->get('items') as $item) {
                $pivotData = [];
                foreach ($pivotColumns as $column) {
                    $pivotData[$column] = array_get($formData, $column);
                }
                $items[$item] = $pivotData;
            }
        }

        $res = $this->entry->{$relationName}()->syncWithoutDetaching($items);

        return $res;
    }

    public function detach()
    {
        $this->resolveResource();
        $relationName = $this->route->parameter('relation');
        $res = $this->entry->{$relationName}()->detach($this->request->get('item'));

        return $res;
    }

    public function lookup()
    {
        $config = $this->getLookupTableConfig();

        if (! $this->route->parameter('data')) {
            return ['data' => $config->makeComponent()->compose()];
        }

        $table = Table::config($config);

        $relation = $this->resolveRelation();

        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = $relation->getRelatedResource()->newQuery();

        $alreadyAttachedItems = $this->entry->{$relation->getName()}()
                                            ->pluck($relation->getRelatedResource()->getHandle().'.id');

        $query->whereNotIn($query->getModel()->getKeyName(), $alreadyAttachedItems);
        $table->setQuery($query);

        return ['data' => $table->build()->compose()];
    }

    /**
     * @return \SuperV\Platform\Domains\Resource\Table\TableConfig
     * @throws \Exception
     */
    protected function getLookupTableConfig(): \SuperV\Platform\Domains\Resource\Table\TableConfig
    {
        $relatedResource = $this->resolveRelation()->getRelatedResource();
        $config = new TableConfig();
        $config->setFields($relatedResource);
        $config->setDataUrl(url()->current().'/data');
        $config->build();

        return $config;
    }
}