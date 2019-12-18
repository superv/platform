<?php

namespace SuperV\Platform\Domains\Resource\Field\Contracts;

use SuperV\Platform\Domains\Resource\Resource;

interface FakerInterface
{
    public function fake(Resource $resource, FieldInterface $field);
}