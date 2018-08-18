<?php

namespace SuperV\Platform\Domains\UI\Table\Features;

use Illuminate\Foundation\Bus\DispatchesJobs;
use SuperV\Platform\Domains\UI\Table\Column;
use SuperV\Platform\Domains\UI\Table\Jobs\LoadPagination;
use SuperV\Platform\Domains\UI\Table\Jobs\SetTableEntries;
use SuperV\Platform\Domains\UI\Table\Jobs\SetTableModel;
use SuperV\Platform\Domains\UI\Table\TableBuilder;

class BuildTable
{
    use DispatchesJobs;

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

        $this->dispatch(new SetTableModel($builder));

        $builder->getTable()->setButtons($builder->getButtons());

        $this->dispatch(new SetTableEntries($builder));

        $this->dispatch(new LoadPagination($builder));

        /**
         * TODO.ali: clean up!
         */
        $columns = $builder->getColumns();
        foreach($columns as &$column) {
            $column = new Column($column['slug'], $column['label']);
        }
        $builder->setColumns($columns);
        $builder->getTable()->setColumns($columns);

        $this->dispatch(new BuildRows($builder));

    }
}
