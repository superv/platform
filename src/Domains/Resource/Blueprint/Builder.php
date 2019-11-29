<?php

namespace SuperV\Platform\Domains\Resource\Blueprint;

use Closure;
use SuperV\Platform\Domains\Resource\Database\ResourceRepository;
use SuperV\Platform\Domains\Resource\Events\ResourceCreatedEvent;
use SuperV\Platform\Domains\Resource\Form\FormRepository;
use SuperV\Platform\Domains\Resource\Jobs\CreateNavigation;
use SuperV\Platform\Domains\Resource\ResourceConfig;

class Builder
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Database\ResourceRepository
     */
    protected $repository;

    public function __construct(ResourceRepository $repository)
    {
        $this->repository = $repository;
    }

    public function build($identifier, Closure $callback = null)
    {
        $this->save($this->resource($identifier, $callback));
    }

    public function save(Blueprint $blueprint)
    {
        // build config from blue print
        $config = ResourceConfig::make([
                'name'      => $blueprint->getHandle(),
                'handle'    => $blueprint->getHandle(),
                'namespace' => $blueprint->getNamespace(),
                'driver'    => $blueprint->getDriver()->toArray(),
                'nav'       => $blueprint->getNav(),
            ]
        );

        // Entry field
        $entryLabelField = $blueprint->getFields()
                                     ->first(function (FieldBlueprint $field) use ($config) {
                                         return $field->isEntryLabel();
                                     });
        if ($entryLabelField) {
            $config->entryLabel('{'.$entryLabelField->getName().'}');
            $config->entryLabelField($entryLabelField->getName());
        }

        // create resource
        $resourceEntry = $this->repository->create($config);

        // driver actions
        $blueprint->getFields()
                  ->map(function (FieldBlueprint $field) use ($config, $resourceEntry) {
                      $field->run($resourceEntry);
                  });

        $blueprint->getDriver()->run($blueprint);

        //  create nav
        if ($nav = $config->getNav()) {
            $section = CreateNavigation::resolve($config)
                                       ->create($nav, $resourceEntry->getId());

            $section->update(['namespace' => $config->getNamespace()]);
        }

        // dispatch event, create forms
        ResourceCreatedEvent::fire($resourceEntry);
        FormRepository::createForResource($blueprint->getIdentifier());
    }

    public static function resource($identifier, Closure $callback = null)
    {
        $blueprint = static::blueprint($identifier);

        if ($callback) {
            app()->call($callback, ['resource' => $blueprint]);
        }

        return $blueprint;
    }

    public static function blueprint($identifier): Blueprint
    {
        [$namespace, $handle] = explode('.', $identifier);

        $resource = Blueprint::resolve();
        $resource->namespace($namespace);
        $resource->handle($handle);
        $resource->databaseDriver()
                 ->table($handle)
                 ->primaryKey('id');

        return $resource;
    }

    public static function run($identifier, Closure $callback = null)
    {
        static::resolve()->build($identifier, $callback);
    }

    /** * @return static */
    public static function resolve()
    {
        return app(static::class);
    }
}