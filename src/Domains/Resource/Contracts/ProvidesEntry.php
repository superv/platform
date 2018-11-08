<?php

namespace SuperV\Platform\Domains\Resource\Contracts;

use SuperV\Platform\Domains\Resource\Model\Entry;

interface ProvidesEntry
{
    public function getEntry(): Entry;
}