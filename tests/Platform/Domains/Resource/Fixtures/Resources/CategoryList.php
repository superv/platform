<?php

namespace Tests\Platform\Domains\Resource\Fixtures\Resources;

use SuperV\Platform\Domains\Resource\Table\Contracts\Table;

class CategoryList
{
    public static $identifier = 'testing.categories.lists:default';

    public function resolved(Table $table)
    {
        $_SERVER['__hooks::list.resolved'] = $table->getDataUrl();
    }
}
