<?php

namespace SuperV\Platform\Domains\Resource\Blueprint;

use SuperV\Platform\Domains\Resource\Field\Types\Boolean\BooleanField;
use SuperV\Platform\Domains\Resource\Field\Types\DateTime\DateTimeField;
use SuperV\Platform\Domains\Resource\Field\Types\Number\NumberField;
use SuperV\Platform\Domains\Resource\Field\Types\Select\SelectField;
use SuperV\Platform\Domains\Resource\Field\Types\Select\SelectFieldBlueprint;
use SuperV\Platform\Domains\Resource\Field\Types\Text\TextField;
use SuperV\Platform\Domains\Resource\Field\Types\Text\TextFieldBlueprint;
use SuperV\Platform\Domains\Resource\Field\Types\Textarea\TextareaField;

/**
 * Trait FieldHelpers
 *
 * @mixin \SuperV\Platform\Domains\Resource\Blueprint\Blueprint
 * @package SuperV\Platform\Domains\Resource\Blueprint
 */
trait FieldHelpers
{
    public function select(string $fieldName, string $label = null): SelectFieldBlueprint
    {
        return $this->addField($fieldName, SelectField::class, $label);
    }

    public function text(string $fieldName, string $label = null): TextFieldBlueprint
    {
        return $this->addField($fieldName, TextField::class, $label);
    }

    public function textarea(string $fieldName, string $label = null)
    {
        return $this->addField($fieldName, TextareaField::class, $label);
    }

    public function datetime(string $fieldName, string $label = null)
    {
        return $this->addField($fieldName, DateTimeField::class, $label);
    }

    public function number(string $fieldName, string $label = null)
    {
        return $this->addField($fieldName, NumberField::class, $label);
    }

    public function boolean(string $fieldName, string $label = null)
    {
        return $this->addField($fieldName, BooleanField::class, $label);
    }
}