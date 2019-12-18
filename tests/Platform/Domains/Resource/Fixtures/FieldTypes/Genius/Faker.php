<?php

namespace Tests\Platform\Domains\Resource\Fixtures\FieldTypes\Genius;

use SuperV\Platform\Domains\Resource\Field\Contracts\FakerInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;
use SuperV\Platform\Domains\Resource\Resource;

class Faker implements FakerInterface
{
    public function fake(Resource $resource, FieldInterface $field)
    {
    }
}