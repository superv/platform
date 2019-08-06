<?php

namespace SuperV\Platform\Domains\Resource\Field\Contracts;

use SuperV\Platform\Domains\Database\Schema\Blueprint;

interface AltersDatabaseTable
{
    public function alterBlueprint(Blueprint $blueprint, array $config = []);
}
