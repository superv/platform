<?php

namespace SuperV\Platform\Domains\Database\Schema;

use Doctrine\DBAL\Schema\Column;

class TableColumn
{
    /**
     * @var \Doctrine\DBAL\Schema\Column
     */
    protected $column;

    public function __construct(Column $column)
    {
        $this->column = $column;
    }

    public function getName()
    {
        return $this->column->getName();
    }

    public function isNullable()
    {
        return ! $this->column->getNotnull();
    }

    public function getDefaultValue()
    {
        return $this->column->getDefault();
    }
}