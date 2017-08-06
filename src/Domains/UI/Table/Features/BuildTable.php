<?php namespace SuperV\Platform\Domains\UI\Table\Features;

use SuperV\Platform\Domains\Feature\Feature;
use SuperV\Platform\Domains\UI\Table\Jobs\BuildTableRowsJob;
use SuperV\Platform\Domains\UI\Table\Jobs\LoadPaginationJob;
use SuperV\Platform\Domains\UI\Table\Jobs\SetTableEntriesJob;
use SuperV\Platform\Domains\UI\Table\Row;
use SuperV\Platform\Domains\UI\Table\Table;
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

        /**
         *  Build table data
         */
        $table = superv(Table::class);
//        $table = (new Table())->setData((new $model)->paginate(10));

        $table->setButtons($builder->getButtons());
        $builder->setTable($table);

        $this->dispatch(new SetTableEntriesJob($builder));

        $this->dispatch(new LoadPaginationJob($builder));

        $this->dispatch(new BuildTableRowsJob($builder));

        /**
         *  Generate Table Columns
         */
        $table = $builder->getTable();
        $collection = $table->getEntries();

        if (!$collection->first()) {
            return [];
        }

        $model = $collection->first();
        // These are the Laravel basic timestamp fields which we don't want to display, by default
        $timestamp_fields = ['created_at', 'updated_at', 'deleted_at'];

        // Grab the basic fields from the first model
        $fields = array_keys($model->toArray());

        // Remove the timestamp fields
        $fields = array_diff($fields, $timestamp_fields);
        if ($model->isSortable) {
            $fields = array_unique(array_merge($fields, $model->getSortable()));
        }

        $table->setColumns($fields);

//        $this->dispatch(new SetTableModel($builder));
//        $this->dispatch(new SetTableStream($builder));
//        $this->dispatch(new SetDefaultParameters($builder));
//        $this->dispatch(new SetRepository($this->builder));

        /*
         * Build table views and mark active.
         */
//        $this->dispatch(new BuildViews($this->builder));
//        $this->dispatch(new SetActiveView($this->builder));

        /**
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