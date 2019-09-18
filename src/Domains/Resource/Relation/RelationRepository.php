<?php

namespace SuperV\Platform\Domains\Resource\Relation;

use SuperV\Platform\Domains\Resource\ResourceModel;

class RelationRepository
{
    public function create(ResourceModel $resource, RelationConfig $config)
    {
    }

    /** * @return static */
    public static function make()
    {
        return app(static::class);
    }
}
