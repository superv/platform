<?php

namespace SuperV\Platform\Domains\Resource\Field;

use Current;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldTypeInterface;
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

    /**
     * @param string|null $resolveFrom
     * @return \SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface | \SuperV\Platform\Domains\Resource\Form\Contracts\FormFieldInterface
     */
    public static function createFromEntry(FieldModel $entry, string $resolveFrom = null)
    {
        $factory = new static;
        $factory->params = $entry->toArray();

        return $factory->create($resolveFrom);
    }

    public static function withIdentifier(string $identifier)
    {
        $fieldEntry = FieldModel::withIdentifier($identifier);

        return static::createFromEntry($fieldEntry);
    }

    /**
     * @param array       $params
     * @param string|null $resolveFrom
     * @return \SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface | \SuperV\Platform\Domains\Resource\Form\Contracts\FormFieldInterface
     */
    public static function createFromArray(array $params, string $resolveFrom = null)
    {
        $params['identifier'] = $params['identifier'] ?? $params['handle'];

        $factory = new static;
        $factory->params = $params;

        return $factory->create($resolveFrom);
    }

    /**
     * @param string|null $resolveFrom
     * @return \SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface | \SuperV\Platform\Domains\Resource\Form\Contracts\FormFieldInterface
     */
    protected function create(string $resolveFrom = null)
    {
        $this->validate();

        if (Current::hasUser() && ! Current::user()->can($this->params['identifier'])) {
            $resolveFrom = GhostField::class;
        }

        if (! isset($this->params['handle'])) {
            $this->params['handle'] = $this->params['identifier'];
        }

        $this->resolveFieldTypeInstance();

        $field = $this->resolveFieldInstance($resolveFrom);

        if ($field->getFieldType() instanceof RequiresDbColumn) {
            if (! $field->hasFlag('nullable')) {
                $field->addFlag('required');
            }
        }

        $field->fireEvent('resolved');

        return $field;
    }

    protected function resolveFieldTypeInstance(): void
    {
        $type = $this->params['type'];

        if (is_object($type) && $type instanceof FieldTypeInterface) {
            $this->params['field_type'] = $type;

            return;
        }

        if (str_contains($type, '\\') && class_exists($type)) {
            $fieldTypeClass = $type;
        } else {
            $fieldTypeClass = FieldType::resolveTypeClass($type);
        }

        $this->params['field_type'] = $fieldTypeClass::resolve();
    }

    protected function resolveFieldInstance(?string $resolveFrom): FieldInterface
    {
        $fieldClass = $resolveFrom ?? Field::class;

        /** @var \SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface $field */
        $field = app($fieldClass);

//        $field = new $fieldClass($this->params);

        return $field->init($this->params);
    }

    protected function validate(): void
    {
        if (! isset($this->params['identifier'])) {
            PlatformException::fail('Missing parameter [identifier] for field');
        }
    }
}
