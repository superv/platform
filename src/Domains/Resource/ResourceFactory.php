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
    protected $identifier;

    /**
     * @var \SuperV\Platform\Domains\Resource\ResourceModel
     */
    protected $model;

    /**
     * @var \SuperV\Platform\Domains\Database\Model\Contracts\EntryContract|null
     */
    protected $entry;

    public static $cache = [];

    protected function __construct(string $identifier, ?EntryContract $entry = null)
    {
        $this->identifier = $identifier;
        $this->entry = $entry;
    }

    public static function attributesFor(string $identifier, ?EntryContract $entry = null): array
    {
        return (new static($identifier, $entry))->get();
    }

    /**
     * @param $identifier
     * @return \SuperV\Platform\Domains\Resource\Resource|null
     * @throws \Exception
     */
    public static function make($identifier): ?Resource
    {
        if ($identifier instanceof EntryContract) {
            $identifier = $identifier->getResourceIdentifier();
        }

//        if (! str_contains($identifier, '.res.')) {
//            $identifier = str_replace_last('.', '.res.', $identifier);
//        }

        if (isset(static::$cache[$identifier])) {
            return static::$cache[$identifier];
        }

        try {
            $attributes = static::attributesFor($identifier);
            $attributes['config'] = ResourceConfig::make($attributes['config']);


            $resource = new Resource($attributes);

            Extension::extend($resource);
        } catch (Exception $e) {
            PlatformException::throw($e);

            return null;
        }

        return static::$cache[$identifier] = $resource;
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
        if (! $this->model = ResourceModel::withIdentifier($this->identifier)) {
            PlatformException::fail("Resource model entry not found for [{$this->identifier}]");
        }

        $attributes = array_merge($this->model->toArray(), [
            'name'          => $this->model->getName(),
            'dsn'           => $this->model->getDsn(),
            'identifier'    => $this->model->getIdentifier(),
            'fields'        => $this->getFieldsProvider(),
            'field_entries' => function () {
                return $this->model->getFields();
            },
            'relations'     => $this->getRelationsProvider(),
        ]);

        return $attributes;
    }

    public static function wipe()
    {
        static::$cache = [];
    }
}
