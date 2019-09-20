<?php

namespace SuperV\Platform\Domains\Resource\Hook\Contracts;

use SuperV\Platform\Domains\Resource\Table\Contracts\Table;

interface ListResolvedHook
{
    public function resolved(Table $table);
}
