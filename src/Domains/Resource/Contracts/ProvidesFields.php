<?php

namespace SuperV\Platform\Domains\Resource\Contracts;

use Illuminate\Support\Collection;

interface ProvidesFields
{
    public function getFields(): Collection;
}