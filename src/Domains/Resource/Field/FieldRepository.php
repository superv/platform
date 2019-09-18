<?php

namespace SuperV\Platform\Domains\Resource\Field;

use SuperV\Platform\Domains\Resource\ResourceModel;

class FieldRepository
{
    public function getResourceField(ResourceModel $resource, string $fieldName)
    {
        if ($resource->hasField($fieldName)) {
            return $resource->getField($fieldName);
        }

        return $resource->makeField($fieldName);
    }

    /** * @return static */
    public static function resolve()
    {
        return app(static::class);
    }
}
