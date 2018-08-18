<?php

namespace SuperV\Platform\Domains\UI\Table\Features;

use Illuminate\Foundation\Bus\DispatchesJobs;
use SuperV\Platform\Domains\UI\Table\RowCollection;
use SuperV\Platform\Domains\UI\Table\TableBuilder;

class BuildRows
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

    public function handle(RowCollection $rows)
    {
        $table = $this->builder->getTable();
        $buttons = $this->builder->getButtons();
        $entries = $table->getEntries();

        foreach ($entries as $entry) {
            $row = [];
            foreach ($this->builder->getColumns() as $column) {
                $row[$column->getSlug()] = $entry->getAttribute($column->getSlug());
            }
//            $row['_operations'] = $this->dispatch(new MakeButtons($buttons, ['entry' => $entry]));
            $rows->push($row);

        }

        $table->setRows($rows);
    }
}
