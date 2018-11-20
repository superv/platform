<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

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
        $relation = $this->resource()->getRelation($this->route->parameter('relation'));
        if ($this->entry) {
            $relation->acceptParentResourceEntry($this->entry);
        }

        return $relation;
    }

    public function table()
    {
        /** @var TableConfig $config */
        $config = $this->resolveRelation()->makeTableConfig();

        if ($this->request->get('data')) {
            return ['data' => Table::config($config)->build()->compose()];
        } else {
            return ['data' => $config->makeComponent()->compose()];
        }
    }

    public function tableData()
    {
    }

    public function attach()
    {
        $this->resource();
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

    public function lookup()
    {
        $config = $this->getLookupTableConfig();

        return ['data' => $config->build()->makeComponent()->compose()];
    }

    public function lookupData()
    {
        $config = $this->getLookupTableConfig();
        $config->build();
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

    public function detach()
    {
        $relatedId = $this->route->parameter('related');

        $this->makeBuilder()->newQuery()->detach($relatedId);
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

        return $config;
    }
}