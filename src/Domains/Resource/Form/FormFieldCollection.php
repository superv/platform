<?php

namespace SuperV\Platform\Domains\Resource\Form;

use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormFieldInterface;

class FormFieldCollection extends Collection
{
    public function visible(): FormFieldCollection
    {
        return $this->filter(function (FormFieldInterface $field) {
            return ! $field->isHidden();
        });
    }

    public function mergeFields($fields)
    {
        $this->items = $this->merge($fields)->all();
    }

    public function addField(FormFieldInterface $field): FormFieldCollection
    {
        // Fields added on the fly should be marked as temporal
        //
        $field->setTemporal(true);

        return $this->push($field);
    }

    public function hide(string $name): FormFieldCollection
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

    public function fieldTypes(): FormFieldCollection
    {
        return $this->map(function (FormFieldInterface $field) {
            return $field->getFieldType();
        });
    }
}