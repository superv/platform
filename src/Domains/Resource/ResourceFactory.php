<?php

namespace SuperV\Platform\Domains\Resource;

use SuperV\Platform\Domains\Resource\Contracts\NeedsEntry;
use SuperV\Platform\Domains\Resource\Contracts\Requirements\AcceptsParentResourceEntry;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\Model\ResourceEntry;
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

    protected function getFieldsProvider()
    {
        return function () {
            return $this->model->getFields()
                               ->map(function (FieldModel $fieldEntry) {
                                   $field = FieldFactory::createFromEntry($fieldEntry);

                                   return $field;
                               });
        };
    }

    protected function getRelationsProvider() {

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
        if (! $this->model = ResourceModel::withSlug($this->handle)) {
            throw new PlatformException("Resource model entry not found for [{$this->handle}]");
        }

        return array_merge($this->model->toArray(), [
            'handle'            => $this->model->getSlug(),
//            'fields'            => $this->getFieldsProvider(),
            'relations'         => $this->getRelationsProvider(),
            'relation_provider' => function (string $name, ?ResourceEntry $entry = null)  {
                $relationEntry = RelationModel::query()
                                              ->where('resource_id', $this->model->id)
                                              ->where('name', $name)
                                              ->first();

                $relation = (new RelationFactory)->make($relationEntry);
                if ($entry && $relation instanceof AcceptsParentResourceEntry) {
                    $relation->acceptParentResourceEntry($entry);
                }

                return $relation;
            },
        ]);
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