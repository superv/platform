<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\Checkbox;

use SuperV\Platform\Domains\Resource\Field\Types\Boolean\BooleanField;

class CheckboxField extends BooleanField
{
    protected $handle = 'checkbox';

    protected $component = 'sv_checkbox_field';
}
