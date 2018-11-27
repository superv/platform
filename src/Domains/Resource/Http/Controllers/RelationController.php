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
        $table = Table::config($config);

        if ($this->route->parameter('data')) {
            return $table->build();
        } else {
            return ['data' => sv_compose($table->makeComponent()->compose())];
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
        $res = $this->entry->{$relationName}()->detach($this->route->parameter('related'));

        return $res;
    }
}