<?php

namespace SuperV\Platform\Domains\Resource\Field;

use SuperV\Platform\Domains\Resource\Field\Contracts\RequiresDbColumn;
use SuperV\Platform\Exceptions\PlatformException;

class FieldFactory
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Field\FieldModel
     */
    protected $fieldEntry;

    /**
     * @var array
     */
    protected $params;

    protected $flags = ['searchable', 'unique', 'required', 'nullable'];

    /**
     * @param string|null $resolveFrom
     * @return \SuperV\Platform\Domains\Resource\Field\Contracts\Field | \SuperV\Platform\Domains\Resource\Form\Contracts\FormField
     */
    public static function createFromEntry(FieldModel $entry, string $resolveFrom = null)
    {
        $factory = new static;
        $factory->params = $entry->toArray();

        return $factory->create($resolveFrom);
    }

    /**
     * @param array       $params
     * @param string|null $resolveFrom
     * @return \SuperV\Platform\Domains\Resource\Field\Contracts\Field | \SuperV\Platform\Domains\Resource\Form\Contracts\FormField
     */
    public static function createFromArray(array $params, string $resolveFrom = null)
    {
        $factory = new static;
        $factory->params = $params;

        return $factory->create($resolveFrom);
    }

    /**
     * @param string|null $resolveFrom
     * @return \SuperV\Platform\Domains\Resource\Field\Contracts\Field | \SuperV\Platform\Domains\Resource\Form\Contracts\FormField
     */
    protected function create(string $resolveFrom = null)
    {
        if (! isset($this->params['name'])) {
            PlatformException::fail('Missing parameter [name] for field');
        }

        if (str_contains($this->params['type'], '\\') && class_exists($this->params['type'])) {
            $fieldTypeClass = $this->params['type'];
        } else {
            $fieldTypeClass = FieldType::resolveTypeClass($this->params['type']);
        }

        /** @var \SuperV\Platform\Domains\Resource\Field\FieldType $fieldType */
        $fieldType = new $fieldTypeClass();

        $fieldClass = $resolveFrom ?? Field::class;

        /** @var \SuperV\Platform\Domains\Resource\Field\Field $field */
        $field = new $fieldClass($fieldType, $this->params);

        if ($fieldType instanceof RequiresDbColumn) {
            if (! $field->hasFlag('nullable')) {
                $field->addFlag('required');
            }
        }

        return $field;
    }
}
