<?php

namespace SuperV\Platform\Domains\Resource\Hook\Contracts;

use SuperV\Platform\Domains\Resource\Table\Contracts\TableInterface;

interface ListDataHook
{
    public function data(TableInterface $table);
}
