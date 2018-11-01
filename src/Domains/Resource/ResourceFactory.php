<?php

namespace SuperV\Platform\Domains\Resource;

class ResourceFactory
{
    public static function make($slug)
    {
        $modelEntry = ResourceModel::withSlug($slug);

        $resource = new Resource();
        $resource->hydrate($modelEntry->toArray());

        $resource->setFields($modelEntry->getFields());

        return $resource;
    }
}