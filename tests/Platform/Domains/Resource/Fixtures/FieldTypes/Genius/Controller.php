<?php

namespace Tests\Platform\Domains\Resource\Fixtures\FieldTypes\Genius;

use SuperV\Platform\Domains\Resource\Field\FieldController;

class Controller extends FieldController
{
    public function lookup()
    {
        return 'the-lookup-response';
    }
}