<?php

namespace SuperV\Platform\Domains\UI\Table\Features;

use SuperV\Platform\Domains\UI\Table\Row;
use SuperV\Platform\Domains\UI\Table\RowCollection;
use SuperV\Platform\Domains\UI\Table\TableBuilder;

class BuildRows
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

        foreach ($entries as $entry) {
            $rows->push((new Row($this->builder, $entry, $table->getColumns(), $table->getButtons()))->make());
        }

        $table->setRows($rows);
    }
}
