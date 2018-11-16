<?php

namespace SuperV\Platform\Domains\Resource\Model\Contracts;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Resource;

interface ResourceEntry extends EntryContract
{
    public static function newInstance(string $handle): ResourceEntry;

    public function getEntry(): EntryContract;

    public function getResource(): Resource;
}