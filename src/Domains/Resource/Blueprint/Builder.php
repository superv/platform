<?php

namespace SuperV\Platform\Domains\Resource\Blueprint;

use Closure;
use SuperV\Platform\Domains\Resource\Database\ResourceRepository;
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

    public function build($identifier, Closure $callback)
    {
        $this->save($this->resource($identifier, $callback));
    }

    public function save(Blueprint $blueprint)
    {
        $config = ResourceConfig::make([
                'name'      => $blueprint->getHandle(),
                'handle'    => $blueprint->getHandle(),
                'namespace' => $blueprint->getNamespace(),
                'driver'    => $blueprint->getDriver()->toArray(),
            ]
        );

        $this->repository->create($config);

        $blueprint->getDriver()->run($blueprint);
    }

    public static function resource($identifier, Closure $callback)
    {
        app()->call($callback, [
            'resource' => $blueprint = static::blueprint($identifier),
        ]);

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

    /** * @return static */
    public static function resolve()
    {
        return app(static::class);
    }
}