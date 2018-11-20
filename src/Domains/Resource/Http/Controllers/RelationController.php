<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Http\ResolvesResource;
use SuperV\Platform\Domains\Resource\Table\Table;
use SuperV\Platform\Domains\Resource\Table\TableConfig;
use SuperV\Platform\Http\Controllers\BaseApiController;

class RelationController extends BaseApiController
{
    use ResolvesResource;

    /** @var \SuperV\Platform\Domains\Resource\Relation\Relation */
    protected $relation;

    protected function relation()
    {
        $this->relation = $this->resource()->getRelation($this->route->parameter('relation'));

        return $this->relation;
    }

    public function attach()
    {
        $this->resource();
        $relationName = $this->route->parameter('relation');
        $res = $this->entry->{$relationName}()->syncWithoutDetaching($this->request->get('items'));

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

        $relation = $this->relation();

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
        $relatedResource = $this->relation()->getRelatedResource();
        $config = new TableConfig();
        $config->setFieldsProvider($relatedResource);
        $config->setDataUrl(url()->current().'/data');

        return $config;
    }
}

//        $config->setQueryProvider($relatedResource);

//        $relationConfig = $relation->getConfig();
//        $config->setQueryParams([
//            'joins'  => [
//                [
//                    'table'    => $relationConfig->getPivotTable(),
//                    'first'    => $relationConfig->getPivotTable().'.'.$relationConfig->getPivotRelatedKey(),
//                    'operator' => '=',
//                    'second'   => $relatedResource->getHandle().'.id',
//                    'type'     => 'inner',
//                ],
//            ],
//            'wheres' => [
//                [
//                    'column'   => $relationConfig->getPivotTable().'.'.$relationConfig->getPivotForeignKey(),
//                    'operator' => '!=',
//                    'value'    => $this->entry->getId(),
//                ],
//                [
//                    'column'   => $relationConfig->getPivotTable().'.owner_type',
//                    'operator' => '!=',
//                    'value'    => $this->entry->getHandle(),
//                    'boolean' => 'or'
//                ],
//            ],
//        ]);