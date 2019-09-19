<?php

namespace SuperV\Platform\Domains\Resource\Field;

use DB;
use SuperV\Platform\Domains\Resource\ResourceModel;
use SuperV\Platform\Exceptions\ValidationException;

class FieldRepository
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Field\FieldModel
     */
    protected $model;

    public function __construct(FieldModel $model)
    {
        $this->model = $model;
    }

    public function create(array $attributes = []): FieldModel
    {
        if (! $identifier = array_get($attributes, 'identifier')) {
            ValidationException::throw('identifier', 'Field identifier is required');
        }

        if (! preg_match('/^(\w+)\.(\w+)\.fields:(\w+)$/', $identifier)) {
            ValidationException::throw('identifier', 'Field identifier format not valid: ['.$identifier.']');
        }

        $field = $this->model->newQuery()->create($attributes);
        if (! starts_with($identifier, 'platform.')) {
            DB::table('sv_auth_actions')->insert([
                'namespace' => explode('.fields:', $identifier)[0].'.fields',
                'slug'      => $identifier,
            ]);
        }

        return $field;
    }

    public function save(FieldModel $field)
    {
        if ($field->exists()) {
            return $field->save();
        }

        $this->create($field->toArray());

        return true;
    }

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
