<?php

namespace SuperV\Platform\Domains\Resource\Builder;

use SuperV\Platform\Domains\Resource\Field\Types\Boolean\BooleanField;
use SuperV\Platform\Domains\Resource\Field\Types\DateTime\DateTimeType;
use SuperV\Platform\Domains\Resource\Field\Types\Number\NumberField;
use SuperV\Platform\Domains\Resource\Field\Types\RelatesToOne\Blueprint;
use SuperV\Platform\Domains\Resource\Field\Types\RelatesToOne\RelatesToOneType;
use SuperV\Platform\Domains\Resource\Field\Types\Select\Blueprint as SelectTypeBlueprint;
use SuperV\Platform\Domains\Resource\Field\Types\Select\SelectType;
use SuperV\Platform\Domains\Resource\Field\Types\Text\Blueprint as TextTypeBlueprint;
use SuperV\Platform\Domains\Resource\Field\Types\Text\TextType;
use SuperV\Platform\Domains\Resource\Field\Types\Textarea\TextareaField;

/**
 * Trait FieldHelpers
 *
 * @mixin \SuperV\Platform\Domains\Resource\Builder\Blueprint
 * @package SuperV\Platform\Domains\Resource\Builder
 */
trait FieldHelpers
{
    public function relatesToOne(string $related, string $fieldName): Blueprint
    {
        /** @var \SuperV\Platform\Domains\Resource\Field\Types\RelatesToOne\Blueprint $blueprint */
        $blueprint = $this->addField($fieldName, RelatesToOneType::class);

        return $blueprint->related($related);
    }

    public function select(string $fieldName, string $label = null): SelectTypeBlueprint
    {
        return $this->addField($fieldName, SelectType::class, $label);
    }

    public function text(string $fieldName, string $label = null): TextTypeBlueprint
    {
        return $this->addField($fieldName, TextType::class, $label);
    }

    public function textarea(string $fieldName, string $label = null)
    {
        return $this->addField($fieldName, TextareaField::class, $label);
    }

    public function datetime(string $fieldName, string $label = null)
    {
        return $this->addField($fieldName, DateTimeType::class, $label);
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