<?php

namespace SuperV\Platform\Domains\Resource\Field\Contracts;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;

interface ProvidesRelationQuery
{
    public function getRelationQuery(EntryContract $parent);
}