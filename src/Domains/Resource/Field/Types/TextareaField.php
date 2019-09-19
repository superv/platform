<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Resource\Field\Contracts\RequiresDbColumn;
use SuperV\Platform\Domains\Resource\Field\FieldType;
use SuperV\Platform\Support\Composer\Payload;

class TextareaField extends FieldType implements RequiresDbColumn
{
    protected function boot()
    {
        $this->field->on('form.composing', $this->composer());
    }

    protected function composer()
    {
        return function (Payload $payload) {
            if ($this->getConfigValue('rich') === true) {
                $payload->set('meta.rich', true);
            }
        };
    }
}
