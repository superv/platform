<?php

namespace SuperV\Platform\Domains\Resource;

use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\Model\ResourceEntryModel;
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

    protected function __construct(string $handle)
    {
        $this->handle = $handle;
    }

    protected function getFieldsProviderXXX()
    {
        return function () {
            $self = ResourceFactory::make('sv_resources')
                                   ->newQuery()
                                   ->with('fields')
                                   ->where('handle', $this->model->getHandle())
                                   ->first();

            return $self->fields->map(function (ResourceEntryModel $fieldEntry) {
                return FieldFactory::createFromArray($fieldEntry->toArray());
            });
        };
    }

    protected function getFieldsProvider()
    {
        return function () {
            $fields = $this->model->getFields()
                               ->map(function (FieldModel $fieldEntry) {
                                   $field = FieldFactory::createFromEntry($fieldEntry);

                                   return $field;
                               });

            return $fields ?? collect();
        };
    }

    protected function getRelationsProvider()
    {
        return function () {
            return $this->model->getResourceRelations()
                               ->map(function (RelationModel $relation) {
                                   $relation = (new RelationFactory)->make($relation);

                                   return $relation;
                               })
                               ->keyBy(function (Relation $relation) { return $relation->getName(); });
        };
    }

    protected function get()
    {
        if (! $this->model = ResourceModel::withHandle($this->handle)) {
            throw new PlatformException("Resource model entry not found for [{$this->handle}]");
        }

        $attributes = array_merge($this->model->toArray(), [
            'handle'    => $this->model->getHandle(),
            'fields'    => $this->getFieldsProvider(),
            'relations' => $this->getRelationsProvider(),
//            'relation_provider' => function (string $name, ?ResourceEntry $entry = null) {
//                $relationEntry = RelationModel::query()
//                                              ->where('resource_id', $this->model->id)
//                                              ->where('name', $name)
//                                              ->first();
//
//                $relation = (new RelationFactory)->make($relationEntry);
//                if ($entry && $relation instanceof AcceptsParentResourceEntry) {
//                    $relation->acceptParentResourceEntry($entry);
//                }
//
//                return $relation;
//            },
        ]);

        return $attributes;
    }

    public static function attributesFor(string $handle): array
    {
        return (new static($handle))->get();
    }

    /** @return \SuperV\Platform\Domains\Resource\Resource */
    public static function make(string $handle)
    {
        return new Resource(static::attributesFor($handle));
    }
}