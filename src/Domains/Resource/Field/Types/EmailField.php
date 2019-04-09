<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Resource\Contracts\NeedsDatabaseColumn;
use SuperV\Platform\Domains\Resource\Field\Field;

class EmailField extends Field implements NeedsDatabaseColumn
{

    protected function boot()
    {
    }
}