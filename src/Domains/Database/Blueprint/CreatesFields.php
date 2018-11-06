<?php

namespace SuperV\Platform\Domains\Database\Blueprint;

use SuperV\Platform\Domains\Database\ColumnDefinition;

trait CreatesFields
{
    public function email($name)
    {
        return $this->string($name)->fieldType('email');
    }

    public function file($name)
    {
        return $this->addColumn(null, $name)->fieldType('file')->ignore();
    }

    public function select($name): ColumnDefinition
    {
        return $this->string($name)->fieldType('select');
    }
}