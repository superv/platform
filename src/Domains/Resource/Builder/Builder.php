<?php

namespace SuperV\Platform\Domains\Resource\Builder;

use Closure;
use SuperV\Platform\Domains\Resource\Database\ResourceRepository;
use SuperV\Platform\Domains\Resource\Events\ResourceCreatedEvent;
use SuperV\Platform\Domains\Resource\Form\FormRepository;
use SuperV\Platform\Domains\Resource\Jobs\CreateNavigation;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceConfig;
use SuperV\Platform\Domains\Resource\ResourceFactory;

class Builder
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Database\ResourceRepository
     */
    protected $repository;

    /**
     * @var \SuperV\Platform\Domains\Resource\Builder\Blueprint
     */
    protected $blueprint;

    public function __construct(ResourceRepository $repository)
    {
        $this->repository = $repository;
    }

    public function save(Blueprint $blueprint): Resource
    {
        $this->blueprint = $blueprint;

        $config = $this->buildConfig();

        // create resource
        //
        $resourceEntry = $this->repository->create($config, $this->blueprint->isPivot());

        // Run Field Blueprints
        //
        $blueprint->getFields()
                  ->map(function (FieldBlueprint $field) use ($config, $resourceEntry) {
                      $field->run($resourceEntry);
                  });

        $blueprint->getDriver()->run($blueprint);

        //  create nav
        //
        if ($nav = $config->getNav()) {
            $section = CreateNavigation::resolve($config)
                                       ->create($nav, $resourceEntry->getId());

            $section->update(['namespace' => $config->getNamespace()]);
        }

        // dispatch event
        //
        ResourceCreatedEvent::fire($resourceEntry);

        // create forms
        //
        FormRepository::createForResource($blueprint->getIdentifier());

        return ResourceFactory::make($blueprint->getIdentifier());
    }

    public static function blueprint($identifier, Closure $callback = null)
    {
        $blueprint = static::resolveBlueprint($identifier);

        if ($callback) {
            app()->call($callback, ['resource' => $blueprint]);
        }

        if (empty($blueprint->getDriver()->getPrimaryKeys())) {
            $blueprint->id();
        }

        return $blueprint;
    }

    public static function resolveBlueprint($identifier): Blueprint
    {
//        [$namespace, $handle] = explode('.', $identifier);

        $resource = Blueprint::resolve();
        $resource->identifier($identifier);
//        $resource->namespace($namespace);
//        $resource->handle($handle);

        return $resource;
    }

    public static function create($identifier, Closure $callback = null): \SuperV\Platform\Domains\Resource\Resource
    {
        return static::resolve()->_create($identifier, $callback);
    }

    protected function _create($identifier, Closure $callback = null): Resource
    {
        return $this->save($this->blueprint($identifier, $callback));
    }

    protected function buildConfig(): ResourceConfig
    {
        // build config from blue print
        //
        $config = ResourceConfig::make([
                'name'         => $this->blueprint->getHandle(),
                'handle'       => $this->blueprint->getHandle(),
                'namespace'    => $this->blueprint->getNamespace(),
                'driver'       => $this->blueprint->getDriver()->toArray(),
                'nav'          => $this->blueprint->getNav(),
                'resource_key' => $this->blueprint->getKey(),
                'key_name'     => $this->blueprint->getKeyName(),
            ]
        );

        /**
         * Entry field
         *
         * @var \SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface $entryLabelField
         */
        $entryLabelField = $this->blueprint->getFields()
                                           ->first(function (FieldBlueprint $field) use ($config) {
                                               return $field->isEntryLabel();
                                           });
        if ($entryLabelField) {
            $config->entryLabel('{'.$entryLabelField->getHandle().'}');
            $config->entryLabelField($entryLabelField->getHandle());
        } else {
            $config->entryLabel('{'.$config->getKeyName().'}');
        }

        return $config;
    }

    /** * @return static */
    public static function resolve()
    {
        return app(static::class);
    }
}