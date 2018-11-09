<?php

namespace SuperV\Platform\Domains\Resource\Field;

use SuperV\Platform\Domains\Resource\Field\Types\FieldType;
use SuperV\Platform\Domains\Resource\Resource;

class FieldDefactory
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

    public function make($field)
    {
        if (is_string($field)) {
            $field = $this->resolveFromString($field);
        } elseif ($field instanceof FieldConfig) {
            $config = $field;
            $field = $this->resolveFromString($config->getFieldName());
            $field->mergeRules($config->getRules());
            $field->mergeConfig($config->getConfig());
        } elseif ($field instanceof FieldModel) {
            $field = $this->resolveFromFieldEntry($field);
        }

//        if ($field instanceof NeedsEntry) {
//            $entry = $this->resource->getEntry();
//            $field->setEntry(new Entry($entry));
//        }

        return $field;
    }

    protected function resolveFromString($name)
    {
        $fieldEntry = FieldModel::query()->where('name', $name)->where('resource_id', $this->resource->id())->first();

        return $this->resolveFromFieldEntry($fieldEntry);
    }

    protected function resolveFromFieldEntry(FieldModel $fieldEntry): FieldType
    {
        $class = FieldType::resolveClass($fieldEntry->getType());

        return $class::fromEntry($fieldEntry);
    }
}