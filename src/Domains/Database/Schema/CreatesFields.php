<?php

namespace SuperV\Platform\Domains\Database\Schema;

trait CreatesFields
{
    public function email($name)
    {
        return $this->string($name)->fieldType('email');
    }

    public function file($name)
    {
        return $this->addColumn(null, $name)->fieldType('file')->ignore()->nullable();
    }

    public function select($name): ColumnDefinition
    {
        return $this->string($name)->fieldType('select');
    }
}