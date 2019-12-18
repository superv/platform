<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\RelatesToOne;

use SuperV\Platform\Domains\Resource\Field\Contracts\FakerInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceFactory;

class Faker implements FakerInterface
{
    public function fake(Resource $resource, FieldInterface $field)
    {
        $relatedResource = ResourceFactory::make($field->getConfigValue('related'));

        if ($relatedResource->count() === 0) {
            if ($relatedResource->getIdentifier() === $resource->getIdentifier()) {
                return rand(1, 5); // otherwise causes dead recursion
            } else {
                $relatedResource->fake([]);
            }
        }

        return $relatedResource->newQuery()->inRandomOrder()->value('id');
    }
}