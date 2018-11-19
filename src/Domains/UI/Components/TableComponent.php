<?php

namespace SuperV\Platform\Domains\UI\Components;

use SuperV\Platform\Domains\Resource\Table\TableConfig;

class TableComponent extends BaseUIComponent
{
    protected $name = 'sv-table-v2';

    /** @var \SuperV\Platform\Domains\Resource\Table\TableConfig */
    protected $config;

    public function getName(): string
    {
        return $this->name;
    }

    public function getProps(): array
    {
        return $this->config->compose()->get();
    }

    public function uuid(): string
    {
        return $this->config->uuid();
    }

    public static function from(TableConfig $config): self
    {
        $static = new static;
        $static->config = $config;

        return $static;
    }
}