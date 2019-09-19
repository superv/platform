<?php

namespace Tests\Platform\Domains\Resource\Form\v2\Helpers;

use SuperV\Platform\Domains\Resource\Form\FormField;

class FormFieldFake extends FormField
{
    public static function fake($identifier = null, array $params = ['type' => 'text'])
    {
        if (! $identifier) {
            $identifier = uuid();
        }

        return static::make(array_merge(compact('identifier'), $params));
    }
}
