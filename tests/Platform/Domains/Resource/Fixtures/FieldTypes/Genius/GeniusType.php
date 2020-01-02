<?php

namespace Tests\Platform\Domains\Resource\Fixtures\FieldTypes\Genius;

use SuperV\Platform\Domains\Resource\Field\FieldType;

class GeniusType extends FieldType
{
    protected $handle = 'genius';

    protected $component = 'sv_genius_field';

    protected function boot()
    {
        parent::boot();
    }
}