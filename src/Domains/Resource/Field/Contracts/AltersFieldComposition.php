<?php

namespace SuperV\Platform\Domains\Resource\Field\Contracts;

use SuperV\Platform\Support\Composer\Payload;

interface AltersFieldComposition
{
    public function alterComposition(Payload $payload);
}