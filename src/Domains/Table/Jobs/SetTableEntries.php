<?php

namespace SuperV\Platform\Domains\Table\Jobs;

use SuperV\Platform\Domains\Table\TableBuilder;

class SetTableEntries
{
    /**
     * @var \SuperV\Platform\Domains\Table\TableBuilder
     */
    protected $builder;

    public function __construct(TableBuilder $builder)
    {
        $this->builder = $builder;
    }

    public function handle()
    {
        $table = $this->builder->getTable();

        $model = $table->getModel();

        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = $model->newQuery();

        // Prevent joins from overriding model columns
        $query->select($model->getTable().'.*');

        $this->builder->applyFilters($query);

        // Allow others to modify query before proceeding
        $this->builder->fire('querying', compact('builder', 'query'));

        // Count total entries
        $countQuery = clone $query;
        $total = $countQuery->getQuery()->count();
        $table->setOption('total_results', $total);

        $limit = 15;
        $page = (int)app('request')->get('page', 1);
        $offset = $limit * (($page ?: 1) - 1);
        if ($total < $offset && $page > 1) {
            throw new \Exception('nedir');
        }
        $query = $query->take($limit)->offset($offset);

        $table->setEntries($query->get());
    }
}