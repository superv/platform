<?php

namespace SuperV\Platform\Domains\Resource\Extension\Contracts;

interface ExtendsResource
{
    public function extend(\SuperV\Platform\Domains\Resource\Resource $resource);

    public function extends(): string;
}