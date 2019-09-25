<?php

namespace Tests\Platform\Domains\Resource\Fixtures;

use SuperV\Platform\Domains\Resource\Database\Entry\ResourceEntry;

class TestUser extends ResourceEntry
{
    protected $table = 'test_users';
}