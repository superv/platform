<?php

namespace SuperV\Platform\Domains\Navigation;

use SuperV\Platform\Support\Collection;

interface Collector
{
    public function collect(string $slug): Collection;
}