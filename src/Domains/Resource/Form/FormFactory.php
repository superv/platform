<?php

namespace SuperV\Platform\Domains\Resource\Form;

use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\ResourceFactory;

class FormFactory
{
    public function create(array $attributes = [])
    {
        $formEntry = FormModel::create($attributes);

        return $formEntry;
    }

    public static function createForResource($resourceHandle)
    {
        $resource = ResourceFactory::make($resourceHandle);

        $formEntry = static::resolve()->create([
            'uuid'        => $resource->getIdentifier(),
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
        return new static(func_get_args());
    }
}
