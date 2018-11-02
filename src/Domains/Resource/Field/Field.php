<?php

namespace SuperV\Platform\Domains\Resource\Field;

use SuperV\Platform\Domains\Resource\Field\Types\FieldType;

interface Field
{
    public function getFieldType(): FieldType;

}