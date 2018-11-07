<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Resource\Field\Field;

class Textarea extends Field
{
    public function getConfig(): array
    {
        return array_merge(['hide.table' => true], parent::getConfig());
    }

}