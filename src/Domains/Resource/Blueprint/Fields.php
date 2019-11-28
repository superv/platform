<?php

namespace SuperV\Platform\Domains\Resource\Blueprint;

use SuperV\Platform\Domains\Resource\Field\Types\NumberField;
use SuperV\Platform\Domains\Resource\Field\Types\TextareaField;
use SuperV\Platform\Domains\Resource\Field\Types\TextField;

class Fields
{
    protected $fields;

    public function text($name)
    {
        $this->fields[$name] = [
            'type' => TextField::class,
        ];
    }

    public function textarea($name)
    {
        $this->fields[$name] = [
            'type' => TextareaField::class,
        ];
    }

    public function number($name): FieldConfig
    {
        $this->fields[$name] = [
            'type'   => NumberField::class,
            'config' => $config = NumberField::config(),
        ];

        return $config;
    }

    /** * @return static */
    public static function resolve()
    {
        return app(static::class);
    }
}