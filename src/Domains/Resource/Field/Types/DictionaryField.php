<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Resource\Contracts\NeedsDatabaseColumn;

class DictionaryField extends FieldType implements NeedsDatabaseColumn
{
    protected $type = 'textarea';
}