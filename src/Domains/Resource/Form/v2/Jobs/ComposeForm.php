<?php

namespace SuperV\Platform\Domains\Resource\Form\v2\Jobs;

use SuperV\Platform\Domains\Resource\Form\Contracts\FormField;
use SuperV\Platform\Domains\Resource\Form\v2\Contracts\FieldComposer;
use SuperV\Platform\Domains\Resource\Form\v2\Contracts\Form;
use SuperV\Platform\Support\Composer\Payload;

class ComposeForm
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Form\v2\Contracts\FieldComposer
     */
    protected $fieldComposer;

    public function __construct(FieldComposer $fieldComposer)
    {
        $this->fieldComposer = $fieldComposer;
    }

    public function handle(Form $form)
    {
        return new Payload([
            'identifier' => $form->getIdentifier(),
            'url'        => $form->getUrl(),
            'method'     => $form->getMethod(),
            'fields'     => $form->getFields()
                                 ->map(function (FormField $field) use ($form) {
                                     return $this->fieldComposer->toForm($form, $field);
                                 })
                                 ->values()->all(),
        ]);
    }

    /**
     * @return static
     */
    public static function resolve()
    {
        return app(static::class);
    }
}
