<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Support\Composer\Payload;

class ModalFormField extends Field
{
    protected function boot()
    {
        $this->on('form.composing', $this->composer());
    }

    protected function composer()
    {
        return function (Payload $payload) {
            $payload->set('config.form', $this->getConfigValue('form'));
        };
    }
}