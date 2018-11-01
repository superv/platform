<?php

namespace SuperV\Platform\Domains\Resource\Field;

use SuperV\Platform\Domains\Resource\Field\Types\FieldType;
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

        $field->setResource($this->resource);

        return $field;
    }

    protected function resolveFromString($name)
    {
        return $this->resolveFromFieldEntry($this->resource->getFieldEntry($name));
    }

    protected function resolveFromFieldEntry(FieldModel $fieldEntry): FieldType
    {
        /** @var FieldType $class */
        $class = $this->base."\\".studly_case($fieldEntry->getFieldType().'_field');

        return $class::fromEntry($fieldEntry);
    }
}