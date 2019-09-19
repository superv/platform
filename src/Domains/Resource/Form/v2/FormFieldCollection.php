<?php

namespace SuperV\Platform\Domains\Resource\Form\v2;

use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormField;
use SuperV\Platform\Domains\Resource\Form\FormField as ConcreteFormField;

class FormFieldCollection extends Collection
{
    public function setFieldValue($identifier, $value)
    {
        $this->getField($identifier)->setValue($value);
    }

    public function getField($identifier): ?FormField
    {
        return $this->get($identifier);
    }

    public function fill($data)
    {
        if (is_array($data)) {
            foreach ($data as $identifier => $value) {
                if ($this->has($identifier)) {
                    $this->setFieldValue($identifier, $value);
                }
            }
        }

        if ($data instanceof EntryContract) {
            foreach ($data->toArray() as $name => $value) {
                $identifier = $data->getResourceIdentifier().'.fields.'.$name;

                if ($this->has($identifier)) {
                    $this->setFieldValue($identifier, $value);
                }
            }
        }
    }

    public function first(callable $callback = null, $default = null): FormField
    {
        return parent::first($callback, $default);
    }

    public function addFields(array $fields)
    {
        foreach ($fields as $field) {
            if (is_array($field)) {
                $this->addFieldFromArray($field);
            } else {
                $this->addField($field);
            }
        }
    }

    /**
     * @return FormField[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function addField(FormField $field)
    {
        if (! $field->isHidden()) {
            $this->put($field->getIdentifier(), $field);
        }
    }

    public function addFieldFromArray(array $params)
    {
        $this->addField(ConcreteFormField::make($params));
    }

    public function addFromFieldEntry(FieldModel $fieldEntry)
    {
        $this->addFieldFromArray($fieldEntry->toArray());
    }
}
