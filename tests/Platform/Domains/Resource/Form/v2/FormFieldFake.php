<?php

namespace Tests\Platform\Domains\Resource\Form\v2;

use SuperV\Platform\Domains\Resource\Form\FormField;

class FormFieldFake extends FormField
{
    public static function fake($identifier = null, $type = 'text')
    {
        if (! $identifier) {
            $identifier = uuid();
        }

        return static::make(compact('identifier', 'type'));
    }
}
