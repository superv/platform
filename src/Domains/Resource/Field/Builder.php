<?php

namespace SuperV\Platform\Domains\Resource\Field;

use SuperV\Platform\Domains\Resource\Contracts\HasResource;
use SuperV\Platform\Domains\Resource\Resource;

class Builder
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Resource
     */
    protected $resource;

    protected $base = 'SuperV\Platform\Domains\Resource\Field\Types';

    public function __construct(Resource $resource)
    {
        $this->resource = $resource;
    }

    public function build($field)
    {
        if (is_string($field)) {
            $field = $this->resolveFromString($field);
        } elseif ($field instanceof FieldConfig) {
            $config = $field;
            $field = $this->resolveFromString($config->getFieldName());
            $field->setRules($config->getRules());
            $field->setConfig($config->getConfig());
        } elseif ($field instanceof FieldModel) {
            $field = $this->resolveFromFieldEntry($field);
        }

        if ($field instanceof HasResource) {
            $field->setResource($this->resource);
        }

        return $field;
    }

    protected function resolveFromString($name)
    {
        $fieldEntry = FieldModel::query()->where('name', $name)->where('resource_id', $this->resource->id())->first();

        return $this->resolveFromFieldEntry($fieldEntry);
    }

    protected function resolveFromFieldEntry(FieldModel $fieldEntry): Field
    {
        $class = Field::resolveClass($fieldEntry->getType());

        return $class::fromEntry($fieldEntry);
    }
}