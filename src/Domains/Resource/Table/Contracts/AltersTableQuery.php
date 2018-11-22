<?php

namespace SuperV\Platform\Domains\Resource\Table\Contracts;

use Closure;

interface AltersTableQuery
{
    public function alterQuery($query);

    public function getAlterQueryCallback(): Closure;
}