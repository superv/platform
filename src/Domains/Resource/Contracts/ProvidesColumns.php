<?php

namespace SuperV\Platform\Domains\Resource\Contracts;

interface ProvidesColumns
{
    public function provideColumns(): \Illuminate\Support\Collection;
}