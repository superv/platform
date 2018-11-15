<?php

namespace SuperV\Platform\Domains\Resource\Model\Contracts;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;

interface ResourceEntry extends EntryContract
{
    public static function newInstance($handle): ResourceEntry;

    public function getEntry(): EntryContract;
}