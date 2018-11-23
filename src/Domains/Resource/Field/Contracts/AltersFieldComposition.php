<?php

namespace SuperV\Platform\Domains\Resource\Field\Contracts;

use SuperV\Platform\Support\Composer\Composition;

interface AltersFieldComposition
{
    public function alterComposition(Composition $composition);
}