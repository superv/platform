<?php

namespace SuperV\Platform\Domains\Resource\Contracts;

interface ProvidesRoute
{
    public function provideRoute(string $name);
}