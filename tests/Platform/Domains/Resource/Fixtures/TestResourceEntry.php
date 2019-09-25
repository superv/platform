<?php

namespace Tests\Platform\Domains\Resource\Fixtures;

use SuperV\Platform\Domains\Resource\Database\Entry\ResourceEntry;

class TestResourceEntry extends ResourceEntry
{
    protected $table = 't_entries';

    public $timestamps = false;
}
