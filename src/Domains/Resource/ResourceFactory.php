<?php

namespace SuperV\Platform\Domains\Resource;

use Exception;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Extension\Extension;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\Resource\Relation\RelationFactory;
use SuperV\Platform\Domains\Resource\Relation\RelationModel;
use SuperV\Platform\Exceptions\PlatformException;

class ResourceFactory
{
    /**
     * @var string
     */
    protected $handle;

    /**
     * @var \SuperV\Platform\Domains\Resource\ResourceModel
     */
    protected $model;

    /**
     * @var \SuperV\Platform\Domains\Database\Model\Contracts\EntryContract|null
     */
    protected $entry;

    protected function __construct(string $handle, ?EntryContract $entry = null)
    {
        $this->handle = $handle;
        $this->entry = $entry;
    }

    public static function attributesFor(string $handle, ?EntryContract $entry = null): array
    {
        return (new static($handle, $entry))->get();
    }

    /**
     * @param $handle
     * @return \SuperV\Platform\Domains\Resource\Resource
     */
    public static function make($handle): ?Resource
    {
        if ($handle instanceof EntryContract) {
            $handle = $handle->getTable();
        }

        try {
            $attributes = static::attributesFor($handle);

            $attributes = Hook::attributes($handle, $attributes);

            if (is_array($attributes['config'])) {
                $attributes['config'] = ResourceConfig::make($attributes['config']);
            }

            $resource = new Resource($attributes);

            Extension::extend($resource);
        } catch (Exception $e) {
            if ($e->getMessage() !== 'Resource model entry not found for [sv_forms]') {
                throw $e;
            }
            return null;
        }

        return $resource;
    }

    protected function getFieldsProvider()
    {
        return function (Resource $resource) {
            $fields = $this->model->getFields()
                                  ->map(function (FieldModel $fieldEntry) use ($resource) {
                                      $field = FieldFactory::createFromEntry($fieldEntry);

                                      $field->setResource($resource);

                                      return $field;
                                  });

            return $fields ?? collect();
        };
    }

    protected function getRelationsProvider()
    {
        return function (Resource $resource) {
            $relations = $this->model->getResourceRelations()
                                     ->map(function (RelationModel $relation) use ($resource) {
                                         $relation = (new RelationFactory)->make($relation);
                                         $resource->cacheRelation($relation);

                                         return $relation;
                                     })->all();

            // get rid of eloquent collection
            //
            return collect($relations)->keyBy(function (Relation $relation) { return $relation->getName(); });
        };
    }

    protected function get()
    {
        if (! $this->model = ResourceModel::withHandle($this->handle)) {
            PlatformException::fail("Resource model entry not found for [{$this->handle}]");
        }

        $attributes = array_merge($this->model->toArray(), [
            'handle'        => $this->model->getHandle(),
            'fields'        => $this->getFieldsProvider(),
            'field_entries' => function () {
                return $this->model->getFields();
            },
            'relations'     => $this->getRelationsProvider(),
        ]);

        return $attributes;
    }
}
