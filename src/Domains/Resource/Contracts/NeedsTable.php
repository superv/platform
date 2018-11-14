<?php

namespace SuperV\Platform\Domains\Resource\Contracts;

use SuperV\Platform\Domains\Resource\Table\Table;

interface NeedsTable
{
    public function setTable(Table $table);
}