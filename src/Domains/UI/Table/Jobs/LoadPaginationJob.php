<?php namespace SuperV\Platform\Domains\UI\Table\Jobs;

use Illuminate\Pagination\LengthAwarePaginator;
use SuperV\Platform\Domains\UI\Table\TableBuilder;

class LoadPaginationJob
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

        $pageName = $table->getOption('prefix') . 'page';
        $perPage = $table->getOption('limit') ?: 10;
        $page = superv('request')->get($pageName);

        $path = '/' . app('request')->path();
        $paginator = new LengthAwarePaginator(
            $table->getEntries(),
            $table->getOption('total_results', 0),
            $perPage,
            $page,
            compact('path', 'pageName')
        );

        $pagination = $paginator->toArray();
        $pagination['links'] = $paginator->appends(app('request')->all())->render();

        $table->setData('pagination', $pagination);
    }
}