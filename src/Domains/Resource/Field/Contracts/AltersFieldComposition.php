<?php

namespace SuperV\Platform\Domains\Resource\Field\Contracts;

use SuperV\Platform\Support\Composition;

interface AltersFieldComposition
{
    public function alterComposition(Composition $composition);
}