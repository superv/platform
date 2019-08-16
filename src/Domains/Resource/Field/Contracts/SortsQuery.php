<?php

namespace SuperV\Platform\Domains\Resource\Field\Contracts;

interface SortsQuery
{
    public function sortQuery($query, $direction);
}
