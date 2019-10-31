<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers\Relation;

use SuperV\Platform\Domains\Resource\Contracts\HandlesRequests;
use SuperV\Platform\Domains\Resource\Filter\ApplyFilters;
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

        // single or multiple
        if ($this->request->has('items')) {
            $items = $this->request->get('items');
        } else {
            $items = $this->getAttachItems();
        }

        if ($pivotColumns = $this->resolveRelation()->getRelationConfig()->getPivotColumns()) {
            $formData = $this->request->get('form_data');
            $_items = [];
            foreach ($items as $item) {
                $pivotData = [];
                foreach ($pivotColumns as $column) {
                    $pivotData[$column] = array_get($formData, $column);
                }
                $_items[$item] = $pivotData;
            }

            $items = $_items;
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

    protected function getAttachItems()
    {
        $table = $this->resolveRelation()->getRelatedResource()->resolveTable();
        $query = $table->getQuery();

        $selection = $this->request->get('selected');

        if ($selection['type'] === 'filter') {
            ApplyFilters::dispatch($table->getFilters(), $query, $this->request);

            $query->whereNotIn($query->getModel()->getQualifiedKeyName(), array_get($selection, 'excluding', []));
        } else {
            $query->whereIn($query->getModel()->getQualifiedKeyName(), array_get($selection, 'including', []));
        }

        return $query->pluck('id');
    }
}