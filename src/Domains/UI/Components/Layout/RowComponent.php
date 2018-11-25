<?php

namespace SuperV\Platform\Domains\UI\Components\Layout;

use SuperV\Platform\Domains\UI\Components\BaseComponent;
use SuperV\Platform\Domains\UI\Components\ComponentContract;

class RowComponent extends BaseComponent
{
    protected $name = 'sv-row';

    public function getName(): string
    {
        return $this->name;
    }

    public function addColumn(ComponentContract $column): self
    {
        $this->props->push($column, 'columns');

        return $this;
    }
}