<?php

namespace SuperV\Platform\Domains\Resource\Field\Composer;

use SuperV\Platform\Support\Composer\Payload;

interface FormComposerInterface
{
    public function compose(): Payload;
}