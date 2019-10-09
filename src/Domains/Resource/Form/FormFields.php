<?php

namespace SuperV\Platform\Domains\Resource\Form;

use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldTypeInterface;
use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\Field\FieldRules;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormFieldInterface;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;
use SuperV\Platform\Domains\Resource\Form\FormField as ConcreteFormField;
use SuperV\Platform\Exceptions\PlatformException;
use SuperV\Platform\Support\Composer\Payload;

class FormFields extends Collection
{
    public function __construct($items = [])
    {
        parent::__construct($items);
    }

    public function visible(): FormFields
    {
        return $this->filter(function (FieldInterface $field) {
            return ! $field->isHidden();
        });
    }

    public function bound(): FormFields
    {
        return $this->filter(function (FieldInterface $field) {
            return ! $field->isUnbound();
        });
    }

    public function keys()
    {
        return $this->map(function (FieldInterface $field) {
            return $field->getColumnName();
        })->all();
    }

    /**
     * @param      $key
     * @param null $default
     * @return FormFieldInterface
     */
    public function get($key, $default = null)
    {
        return parent::get($key, $default);
    }

    public function rules(?EntryContract $entry = null)
    {
        return $this->visible()
                    ->keyBy(function (FieldInterface $field) {
                        return $field->getColumnName();
                    })
                    ->map(function (FieldInterface $field) use ($entry) {
                        $rules = (new FieldRules($field, $entry));

                        $fieldType = $field->getFieldType();

                        if ($entry) {
                            if ($entry->exists() && method_exists($fieldType, 'updateRules')) {
                                $fieldType->updateRules($rules);
                            }
                            if (! $entry->exists() && method_exists($fieldType, 'createRules')) {
                                $fieldType->createRules($rules);
                            }
                        }

                        return $rules->get();
                    })
                    ->filter()
                    ->all();
    }

    public function saving(FormInterface $form, FormFields $fields)
    {
        $fields->visible()->each(function (FormFieldInterface $field) use ($form) {
            $fieldType = $field->getFieldType();
            $fieldType->saving($form);

            if ($callback = $field->getCallback('before_saving')) {
                app()->call($callback, ['form' => $form, 'fieldType' => $fieldType]);
            }

            if ($form->isCreating() && $callback = $field->getCallback('before_creating')) {
                app()->call($callback, ['form' => $form, 'fieldType' => $fieldType]);
            }

            if ($form->isUpdating() && $callback = $field->getCallback('before_updating')) {
                app()->call($callback, ['form' => $form, 'fieldType' => $fieldType]);
            }
        });
    }

    public function validating(FormInterface $form, FormFields $fields)
    {
        $fields->visible()
               ->each(function (FormFieldInterface $field) use ($form) {
                   if ($callback = $field->getCallback('before_validating')) {
                       app()->call($callback, ['form' => $form, 'field' => $field]);
                   }
               });
    }

    public function composed(FormInterface $form, FormFields $fields, Payload $payload)
    {
        $fields->visible()
               ->fieldTypes()
               ->each(function (FieldTypeInterface $fieldType) use ($form, $payload) {
                   $fieldType->formComposed($payload, $form);
               });
    }

    public function saved(FormInterface $form)
    {
        $this->visible()
             ->fieldTypes()
             ->each(function (FieldTypeInterface $fieldType) use ($form) {
                 $fieldType->saved($form);
             });
    }

    public function mergeFields($fields)
    {
        $this->items = $this->merge($fields)
                            ->keyBy(function (FieldInterface $field) {
                                return $field->getName();
                            })->all();
    }

    public function addField(FormFieldInterface $field): FormFields
    {
        // Fields added on the fly should be marked as temporal
        //
        $field->setTemporal(true);

        return $this->put($field->getName(), $field);
    }

    public function addFieldFromArray(array $params): FormFieldInterface
    {
        $field = ConcreteFormField::make($params);
        $field->addFlag('unbound');
        $this->addField($field);

        return $field;
    }

    public function addFromFieldEntry(FieldModel $fieldEntry): FormFieldInterface
    {
        return $this->addFieldFromArray($fieldEntry->toArray());
    }

    public function hide($names): FormFields
    {
        if (! is_array($names)) {
            $names = func_num_args() === 1 ? [$names] : func_get_args();
        }

        array_map(function ($name) {
            if (! $field = $this->field($name)) {
                PlatformException::runtime("Field [{$name}] does not exist");
            }
            $field->hide();
        }, $names);

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