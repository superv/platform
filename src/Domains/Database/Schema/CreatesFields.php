<?php

namespace SuperV\Platform\Domains\Database\Schema;

/**
 * Trait CreatesFields
 *
 * @method ColumnDefinition string($column, $length = null)
 * @method ColumnDefinition unsignedInteger($column, $autoIncrement = false)
 */
trait CreatesFields
{
    public function email($name): ColumnDefinition
    {
        return $this->string($name)->fieldType('email');
    }

    public function file($name): ColumnDefinition
    {
        return $this->addColumn(null, $name)->fieldType('file')->ignore()->nullable();
    }

    public function select($name): ColumnDefinition
    {
        return $this->string($name)->fieldType('select');
    }

}