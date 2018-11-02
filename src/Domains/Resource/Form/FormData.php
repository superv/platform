<?php

namespace SuperV\Platform\Domains\Resource\Form;

use SuperV\Platform\Domains\Resource\Field\Field;

class FormData
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Form\Form
     */
    protected $form;

    public function __construct(Form $form)
    {
        $this->form = $form;
    }

    public static function make(Form $form)
    {
        return new static($form);
    }

    public function getUrl()
    {
        return $this->form->getUrl();
    }

    public function getMethod()
    {
        return $this->form->getMethod();
    }

    public function getFieldKeys()
    {
        return $this->form->getFields()
                          ->map(function (Field $field) { return $field->getName(); })
                          ->all();
    }

    public function getField(string $name): ?Field
    {
        $field = collect($this->form->getFields())
            ->first(function (Field $field) use ($name) {
                return $field->getName() === $name;
            });

        return $field;
    }

    public function toArray()
    {
        return [
            'url'    => $this->form->getUrl(),
            'method' => $this->form->getMethod(),
            'fields' => collect($this->form->getFields())
                ->map(function (Field $field) { return $field->compose(); })
                ->all(),
        ];
    }
}