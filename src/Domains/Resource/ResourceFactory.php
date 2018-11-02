<?php

namespace SuperV\Platform\Domains\Resource;

use SuperV\Platform\Domains\Resource\Field\Builder;
use SuperV\Platform\Exceptions\PlatformException;

class ResourceFactory
{
    public static function make($slug)
    {
        if (! $modelEntry = ResourceModel::withSlug($slug)) {
            throw new PlatformException("Resource model entry not found for [{$slug}]");
        }

        $resource = new Resource();
        $resource->hydrate($modelEntry->toArray());

        if ($extension = Resource::extension($slug)) {
            $extension = app($extension);

            if (method_exists($extension, 'fields')) {
                $fields = sv_collect($extension->fields())->map(function ($field) use ($resource) {
                    return (new Builder($resource))->build($field);
                });
            }
        }

        $resource->setFields($fields ?? $modelEntry->getFields());
        $resource->setRelations($modelEntry->getResourceRelations());
        return $resource;
    }
}