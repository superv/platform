<?php

namespace SuperV\Platform\Domains\UI\Components;

use SuperV\Platform\Domains\Resource\Table\Table;
use SuperV\Platform\Support\Composer\Composition;

class TableComponent extends BaseUIComponent
{
    protected $name = 'sv-table';

    /** @var \SuperV\Platform\Domains\Resource\Table\Table */
    protected $table;

    public function getName(): string
    {
        return $this->name;
    }

    public function uuid(): string
    {
        return $this->table->getConfig()->uuid();
    }

    public static function from(Table $table): self
    {
        $static = new static;
        $static->table = $table;
        $static->props->merge($table->getConfig()->compose()->get());
        $static->card();

        return $static;
    }
}