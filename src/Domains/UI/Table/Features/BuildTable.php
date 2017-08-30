<?php

namespace SuperV\Platform\Domains\UI\Table\Features;

use SuperV\Platform\Domains\Feature\Feature;
use SuperV\Platform\Domains\UI\Table\Jobs\BuildColumnsJob;
use SuperV\Platform\Domains\UI\Table\Jobs\BuildRowsJob;
use SuperV\Platform\Domains\UI\Table\Jobs\LoadPaginationJob;
use SuperV\Platform\Domains\UI\Table\Jobs\SetTableEntriesJob;
use SuperV\Platform\Domains\UI\Table\Jobs\SetTableModelJob;
use SuperV\Platform\Domains\UI\Table\TableBuilder;

class BuildTable extends Feature
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

        $this->dispatch(new SetTableModelJob($builder));

        $builder->getTable()->setButtons($builder->getButtons());

        $this->dispatch(new SetTableEntriesJob($builder));

        $this->dispatch(new LoadPaginationJob($builder));

        $this->dispatch(new BuildColumnsJob($builder));

        $this->dispatch(new BuildRowsJob($builder));

//        $this->dispatch(new SetTableModel($builder));
//        $this->dispatch(new SetTableStream($builder));
//        $this->dispatch(new SetDefaultParameters($builder));
//        $this->dispatch(new SetRepository($this->builder));

        /*
         * Build table views and mark active.
         */
//        $this->dispatch(new BuildViews($this->builder));
//        $this->dispatch(new SetActiveView($this->builder));

        /*
         * Set the table options going forward.
         */
//        $this->dispatch(new SetTableOptions($this->builder));
//        $this->dispatch(new SetDefaultOptions($this->builder));
//        $this->dispatch(new SaveTableState($this->builder));

        /*
         * Before we go any further, authorize the request.
         */
//        $this->dispatch(new AuthorizeTable($this->builder));

        /*
         * Build table filters and flag active.
         */
//        $this->dispatch(new BuildFilters($this->builder));
//        $this->dispatch(new SetActiveFilters($this->builder));

        /*
         * Build table actions and flag active.
         */
//        $this->dispatch(new BuildActions($this->builder));
//        $this->dispatch(new SetActiveAction($this->builder));

        /*
         * Build table headers.
         */
//        $this->dispatch(new BuildHeaders($this->builder));
//        $this->dispatch(new EagerLoadRelations($this->builder));

        /*
         * Get table entries.
         */
//        $this->dispatch(new GetTableEntries($this->builder));

        /*
         * Lastly table rows.
         */
//        $this->dispatch(new BuildRows($this->builder));
    }
}
