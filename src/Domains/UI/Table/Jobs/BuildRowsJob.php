<?php namespace SuperV\Platform\Domains\UI\Table\Jobs;

use SuperV\Platform\Domains\UI\Table\Row;
use SuperV\Platform\Domains\UI\Table\RowCollection;
use SuperV\Platform\Domains\UI\Table\TableBuilder;

class BuildRowsJob
{
    /**
     * @var TableBuilder
     */
    private $builder;

    public function __construct(TableBuilder $builder)
    {
        $this->builder = $builder;
    }

    public function handle(RowCollection $rows)
    {
        $table = $this->builder->getTable();
        $entries = $table->getEntries();

        foreach ($entries as $model) {
            $rows->push((new Row($model, $table->getColumns(), $table->getButtons()))->make());
        }

        $table->setRows($rows);
    }
}