<?php

namespace SuperV\Platform\Domains\Resource\Form;

use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\ResourceFactory;

class FormFactory
{
    public function create($namespace, $name, array $attributes = [])
    {
        $formEntry = FormModel::create(array_merge([
            'uuid'       => $namespace,
            'namespace'  => $namespace,
            'identifier' => $namespace.'::forms.'.$name,
            'name'       => $name,
        ], $attributes));

        return $formEntry;
    }

    public static function createForResource($identifier)
    {
        $resource = ResourceFactory::make($identifier);

        $formEntry = static::resolve()->create(
            $resource->getIdentifier(), 'default', [
            'resource_id' => $resource->id(),
            'title'       => $resource->getLabel().' Form',
        ]);

        $resource->getFieldEntries()
                 ->map(function (FieldModel $field) use ($formEntry) {
                     $formEntry->fields()->attach($field->getId());
                 });
    }

    /** @return static */
    public static function resolve()
    {
        return app()->make(static::class, func_get_args());
    }
}
