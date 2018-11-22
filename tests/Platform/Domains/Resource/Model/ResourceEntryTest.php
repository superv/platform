<?php

namespace Tests\Platform\Domains\Resource\Model;

use SuperV\Platform\Domains\Database\Model\Entry;

class ResourceEntryTest
{
}

class TestUser extends Entry
{
    protected $table = 't_users';

    public $timestamps = false;
}