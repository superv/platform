<?php

namespace SuperV\Platform\Domains\Resource\Field;

use Current;
use SuperV\Platform\Domains\Auth\Access\Action;
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

    public function get($identifier)
    {
        $field = $this->model->newQuery()->whereIdentifier($identifier)->first();

        if (Current::user()->can($field->getIdentifier())) {
            return $field;
        }

        return new GhostField();
    }

    public function create(array $attributes = []): FieldModel
    {
        if (! $identifier = array_get($attributes, 'identifier')) {
            ValidationException::throw('identifier', 'Field identifier is required');
        }

        if (! preg_match('/^(\w+)\.(\w+)\.fields:(\w+)$/', $identifier)) {
            ValidationException::throw('identifier', 'Field identifier format not valid: ['.$identifier.']');
        }

        if (! isset($attributes['label'])) {
            $attributes['label'] = str_unslug($attributes['name']);
        }

        $field = $this->model->newQuery()->create($attributes);

        if (! starts_with($identifier, 'platform.')) {
            $identifier = sv_identifier($identifier);
            Action::query()->create([
//                'namespace' => explode('.fields:', $identifier)[0].'.fields',
'namespace' => $identifier->getParent(),
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
