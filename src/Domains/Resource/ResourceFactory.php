<?php

namespace SuperV\Platform\Domains\Resource;

use SuperV\Platform\Exceptions\PlatformException;

class ResourceFactory
{
    public static function make($slug)
    {
        if (!$modelEntry = ResourceModel::withSlug($slug)) {
            throw new PlatformException("Resource model entry not found for [{$slug}]");
        }

        $resource = new Resource();

        $resource->hydrate($modelEntry->toArray());

        $resource->setFields($modelEntry->getFields());

        return $resource;
    }
}