<?php

namespace SuperV\Platform\Domains\Resource\Form;

use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;
use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\Field\Jobs\ParseFieldRules;
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

    public function bound(): FormFields
    {
        return $this->filter(function (FormFieldInterface $field) {
            return ! $field->isUnbound();
        });
    }

    public function keys()
    {
        return $this->map(function (FormFieldInterface $field) {
            return $field->getColumnName();
        })->all();
    }

    public function rules(EntryContract $entry = null)
    {
        return $this->visible()
                    ->keyBy(function (FieldInterface $field) {
                        return $field->getColumnName();
                    })
                    ->map(function (FieldInterface $field) use ($entry) {
                        return (new ParseFieldRules($field))->parse($entry);
                    })
                    ->filter()
                    ->all();
    }

    public function mergeFields($fields)
    {
        $this->items = $this->merge($fields)
                            ->keyBy(function (FieldInterface $field) {
                                return $field->getColumnName();
                            })->all();
    }

    public function addField(FormFieldInterface $field): FormFields
    {
        // Fields added on the fly should be marked as temporal
        //
        $field->setTemporal(true);

        return $this->put($field->getColumnName(), $field);
    }

    public function addFieldFromArray(array $params)
    {
        $params['identifier'] = $params['identifier'] ?? $params['name'];

        $field = ConcreteFormField::make($params);
        $field->addFlag('unbound');
        $this->addField($field);
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