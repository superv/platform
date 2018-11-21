<?php

namespace SuperV\Platform\Domains\Resource\Extension\Contracts;

interface ResourceExtension
{
    public function extend(\SuperV\Platform\Domains\Resource\Resource $resource);

    public function extends(): string;
}