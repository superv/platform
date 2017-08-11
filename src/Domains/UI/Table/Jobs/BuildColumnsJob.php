<?php namespace SuperV\Platform\Domains\UI\Table\Jobs;

use SuperV\Platform\Domains\UI\Table\TableBuilder;

class BuildColumnsJob
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
        if ($columns = $this->builder->getColumns()) {
            $table->setColumns($columns);
            return;
        }
        $collection = $table->getEntries();

        if (!$collection->first()) {
            return;
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
    }
}