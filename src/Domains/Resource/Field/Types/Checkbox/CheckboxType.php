<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\Checkbox;

use SuperV\Platform\Domains\Resource\Field\Types\Boolean\BooleanType;

class CheckboxType extends BooleanType
{
    protected $handle = 'checkbox';

    protected $component = 'sv_checkbox_field';
}
