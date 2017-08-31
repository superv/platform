<?php

namespace SuperV\Platform\Domains\UI\Table\Jobs;

use Illuminate\Database\Eloquent\Builder;
use SuperV\Platform\Domains\Entry\EntryModel;
use SuperV\Platform\Domains\UI\Table\TableBuilder;

class SetTableEntriesJob
{
    /**
     * @var TableBuilder
     */
    private $builder;

    public function __construct(TableBuilder $builder)
    {
        $this->builder = $builder;
    }

    public function handle()
    {
        $builder = $this->builder;

        $table = $builder->getTable();

        /** @var EntryModel $model */
        $model = $table->getModel();

        /** @var Builder $query */
        $query = $model->newQuery();

        // Prevent joins from overriding model columns
        $query->select($model->getTable().'.*');

        // Eager load relations
        $query->with([]);

        // Allow others to modify query before proceeding
        $builder->fire('querying', compact('builder', 'query'));

        // Count total entries
        $countQuery = clone $query;
        $total = $countQuery->getQuery()->count();
        $table->setOption('total_results', $total);

        $limit = 15;
        $page = (int) app('request')->get('page', 1);
        $offset = $limit * (($page ?: 1) - 1);
        if ($total < $offset && $page > 1) {
            $url = str_replace('page='.$page, 'page='.($page - 1), app('request')->fullUrl());

            header('Location: '.$url);
        }
        $query = $query->take($limit)->offset($offset);

        $entries = $query->get();
        $table->setEntries($entries);
    }
}
