<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers\Relation;

use SuperV\Platform\Domains\Resource\Contracts\HandlesRequests;
use SuperV\Platform\Domains\Resource\Http\ResolvesResource;
use SuperV\Platform\Http\Controllers\BaseApiController;

class RelationController extends BaseApiController
{
    use ResolvesResource;

    public function request()
    {
        $relation = $this->resolveRelation();

        if ($relation instanceof HandlesRequests) {
            $relation->handleRequest($this->request);
        }

        return ['status' => 'ok'];
    }

    public function attach()
    {
        $this->resolveResource();

        $relationName = $this->route->parameter('relation');
        $items = $this->request->get('items');
        if ($pivotColumns = $this->resolveRelation()->getRelationConfig()->getPivotColumns()) {
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