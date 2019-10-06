<?php

namespace SuperV\Platform\Domains\Resource\Form;

use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormFieldInterface;
use SuperV\Platform\Domains\Resource\Form\FormField as ConcreteFormField;

class FormFields extends Collection
{
    public function visible(): FormFields
    {
        return $this->filter(function (FormFieldInterface $field) {
            return ! $field->isHidden();
        });
    }

    public function mergeFields($fields)
    {
        $this->items = $this->merge($fields)->all();
    }

    public function addField(FormFieldInterface $field): FormFields
    {
        // Fields added on the fly should be marked as temporal
        //
        $field->setTemporal(true);

        return $this->push($field);
    }

    public function addFieldFromArray(array $params)
    {
        $params['identifier'] = $params['identifier'] ?? $params['name'];
        $this->addField(ConcreteFormField::make($params));
    }

    public function addFromFieldEntry(FieldModel $fieldEntry)
    {
        $this->addFieldFromArray($fieldEntry->toArray());
    }

    public function hide(string $name): FormFields
    {
        $this->field($name)->hide();

        return $this;
    }

    public function field(string $name): ?FormFieldInterface
    {
        return $this->first(function (FormFieldInterface $field) use ($name) {
            return $field->getName() === $name;
        });
    }

    public function fieldTypes(): FormFields
    {
        return $this->map(function (FormFieldInterface $field) {
            return $field->getFieldType();
        });
    }
}