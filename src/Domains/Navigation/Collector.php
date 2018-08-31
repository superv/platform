<?php

namespace SuperV\Platform\Domains\Navigation;

use Illuminate\Support\Collection;

interface Collector
{
    public function collect(string $slug): Collection;
}