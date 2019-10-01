<?php

namespace SuperV\Platform\Domains\Resource\Hook\Contracts;

use SuperV\Platform\Domains\Resource\Resource\IndexFields;
use SuperV\Platform\Domains\Resource\Table\Contracts\TableInterface;

interface ListConfigHook
{
    public function config(TableInterface $table, IndexFields $fields);
}
