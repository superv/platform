<?php

namespace SuperV\Platform\Domains\Resource\Form;

class FieldLocation
{
    public $row;

    public function setRow($row): FieldLocation
    {
        $this->row = $row;

        return $this;
    }

    public static function make()
    {
        return new static;
    }

    public static function atRow($row)
    {
        return static::make()->setRow($row);
    }
}