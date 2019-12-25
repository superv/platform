<?php

namespace SuperV\Platform\Domains\Resource\Builder;

use SuperV\Platform\Domains\Resource\Field\Types\Boolean\BooleanType;
use SuperV\Platform\Domains\Resource\Field\Types\DateTime\DateTimeType;
use SuperV\Platform\Domains\Resource\Field\Types\File\Blueprint as FileTypeBlueprint;
use SuperV\Platform\Domains\Resource\Field\Types\File\FileType;
use SuperV\Platform\Domains\Resource\Field\Types\Number\NumberField;
use SuperV\Platform\Domains\Resource\Field\Types\RelatesToMany\Blueprint as RelatesToManyTypeBlueprint;
use SuperV\Platform\Domains\Resource\Field\Types\RelatesToMany\RelatesToManyType;
use SuperV\Platform\Domains\Resource\Field\Types\RelatesToOne\Blueprint as RelatesToOneTypeBlueprint;
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
    public function relatesToOne(string $related, string $fieldName): RelatesToOneTypeBlueprint
    {
        $blueprint = $this->addField($fieldName, RelatesToOneType::class);

        return $blueprint->related($related);
    }

    public function relatesToMany(string $related, string $fieldName): RelatesToManyTypeBlueprint
    {
        $blueprint = $this->addField($fieldName, RelatesToManyType::class);

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
        return $this->addField($fieldName, BooleanType::class, $label);
    }

    public function file(string $fieldName, string $label = null): FileTypeBlueprint
    {
        return $this->addField($fieldName, FileType::class, $label);
    }
}