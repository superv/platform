<?php

namespace SuperV\Platform\Domains\Table\Jobs;

use Illuminate\Pagination\LengthAwarePaginator;
use SuperV\Platform\Domains\Table\TableBuilder;

class LoadPagination
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
        $table = $this->builder->getTable();

        $pageName = $table->getOption('prefix').'page';
        $perPage = $table->getOption('limit') ?: 8;
        $page = app('request')->get($pageName);

        $path = '/'.app('request')->path();
        $paginator = new LengthAwarePaginator(
            $table->getEntries(),
            $table->getOption('total_results', 0),
            $perPage,
            $page,
            compact('path', 'pageName')
        );

        $pagination = $paginator->toArray();
        $pagination['links'] = $paginator->appends(app('request')->all())->render();

        $table->setData('results', $pagination);
    }
}