<?php

namespace SuperV\Platform\Domains\Resource\Form;

use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
use SuperV\Platform\Domains\Resource\Field\FieldComposer;
use SuperV\Platform\Support\Composer\Payload;

class FormComposer
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Form\Form
     */
    protected $form;

    public function __construct(Form $form)
    {
        $this->form = $form;
    }

    public function payload()
    {
        $payload = new Payload([
            'url'    => $this->form->getUrl(),
            'method' => $this->form->getMethod(),
            'fields' => $this->composeFields(),
        ]);
        $this->form->fire('composed', ['form' => $this, 'payload' => $payload]);

        return $payload;
    }

    protected function composeFields()
    {
        $composed = collect();

        foreach ($this->form->getFields() as $handle => $fields) {
            $composed = $composed->merge(
                $fields
                    ->filter(function (Field $field) {
                        return ! $field->isHidden() && ! $field->hasFlag('form.hide');
                    })
                    ->map(function (Field $field) use ($handle) {
                        return array_filter(
                            (new FieldComposer($field))->forForm($this->form->getEntryForHandle($handle) ?? null)->get()
                        );
                    })
            );
        }

        return $composed->values()->all();
    }

    public static function make(Form $form)
    {
        return (new static($form))->payload();
    }
}