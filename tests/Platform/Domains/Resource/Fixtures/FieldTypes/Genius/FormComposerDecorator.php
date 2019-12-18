<?php

namespace Tests\Platform\Domains\Resource\Fixtures\FieldTypes\Genius;

use SuperV\Platform\Domains\Resource\Field\Composer\FormComposerInterface;
use SuperV\Platform\Support\Composer\Payload;

class FormComposerDecorator implements FormComposerInterface
{
    public function compose(): Payload
    {
    }
}