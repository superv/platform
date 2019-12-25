<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\Email;

use SuperV\Platform\Domains\Resource\Field\Contracts\ProvidesFieldComponent;
use SuperV\Platform\Domains\Resource\Field\Contracts\RequiresDbColumn;
use SuperV\Platform\Domains\Resource\Field\Contracts\SortsQuery;
use SuperV\Platform\Domains\Resource\Field\FieldType;

class EmailType extends FieldType implements RequiresDbColumn, SortsQuery, ProvidesFieldComponent
{
    protected $handle = 'email';

    protected $component = 'sv_email_field';

    public function sortQuery($query, $direction)
    {
        $query->orderBy($this->field->getColumnName(), $direction);
    }

    public function getComponentName(): string
    {
        return $this->component;
    }
}
