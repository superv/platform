<?php

namespace SuperV\Platform\Domains\Resource\Table;

class TableData
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Table\Table
     */
    protected $table;

    public function __construct(Table $table)
    {
        $this->table = $table;
    }

    public function toArray()
    {
        return [
            'rows'       => $this->table->getRows()
                                        ->map(function (TableRow $row) {
                                            return $row->compose();
                                        })
                                        ->all(),
            'pagination' => $this->table->getPagination(),
        ];
    }
}