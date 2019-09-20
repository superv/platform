<?php

namespace SuperV\Platform\Domains\Resource\Hook\Contracts;

use SuperV\Platform\Domains\Resource\Table\Contracts\Table;

interface ListDataHook
{
    public function data(Table $table);
}
