<?php

namespace SuperV\Platform\Domains\Resource\Hook;

use SuperV\Platform\Contracts\Dispatcher;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Exceptions\PlatformException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Hook
{
    protected $map = [];

    /**
     * @var \SuperV\Platform\Contracts\Dispatcher
     */
    protected $dispatcher;

    protected static $locks = [];

    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Return mapped hook path for the identifier
     *
     * @param      $identifier
     * @param null $key
     * @return string|null
     */
    public function get($identifier, $key = null)
    {
        if (! isset($this->map[$identifier])) {
            return null;
        }

        if ($key) {
            return $this->map[$identifier][$key];
        }

        return $this->map[$identifier];
    }

    /**
     * Scan path for hooks
     *
     * @param $path
     */
    public function scan($path): Hook
    {
        if (! file_exists($path)) {
            PlatformException::runtime(sprintf("Path not found: %s", $path));
        }

        /** @var SplFileInfo $file */
        foreach ((new Finder)->in($path)->files() as $file) {
            $namespace = get_ns_from_file($file->getPathname());
            if (! $namespace) {
                continue;
            }

            $className = str_replace('.php', '', $file->getFilename());
            $class = $namespace.'\\'.$className;

            $identifier = isset($class::$identifier) ? $class::$identifier : null;

            if (! $identifier) {
                continue;
            }
            $parts = explode('_', snake_case($className));
            $type = end($parts);

            $this->register($identifier, $type, $class);
        }

        return $this;
    }

    public function hookType($identifier, $type, $hookHandler)
    {
        $typeHook = str_replace_last("\\Hook", "\\".studly_case($type.'_hook'), get_class($this));
        if (class_exists($typeHook)) {
            app($typeHook)->hook($identifier, $hookHandler);
        }
    }

    public function hookTypeeee($identifier, $type, $payload)
    {
        $typeHook = str_replace_last("\\Hook", "\\".studly_case($type.'_hook'), get_class($this));
        if (class_exists($typeHook)) {
            (new $typeHook($identifier, $this->get($identifier, $type)))->hook($payload);
        }
    }

    public function register($identifier, $key, $class)
    {
        if (! isset($this->map[$identifier])) {
            $this->map[$identifier] = [];
        }
        $this->map[$identifier][$key] = $class;

        $this->hookType($identifier, $key, $class);

        return $this;
    }

    public static function base($handle)
    {
        return static::$map[$handle] ?? null;
    }

    public static function unregister($handle)
    {
        unset(static::$map[$handle]);
    }

    public static function saving(EntryContract $entry, Resource $resource)
    {
        if (! $baseNamespace = static::base($resource->getIdentifier())) {
            return;
        }

        if ($resourceKey = $resource->config()->getResourceKey()) {
            $plural = str_plural($resourceKey);
        } else {
            $plural = $resource->getName();
        }

        $listener = $baseNamespace."\\".studly_case($plural.'_saving');
        if (class_exists($listener)) {
            $listener = app()->make($listener);
            if (method_exists($listener, 'before')) {
                call_user_func_array([$listener, 'before'], [$entry, $resource]);
            }
        }
    }

    public static function saved(EntryContract $entry, Resource $resource)
    {
        if (! $baseNamespace = static::base($resource->getIdentifier())) {
            return;
        }

        if ($resourceKey = $resource->config()->getResourceKey()) {
            $plural = str_plural($resourceKey);
        } else {
            $plural = $resource->getIdentifier();
        }

        $listener = $baseNamespace."\\".studly_case($plural.'_saving');
        // check lock
        //
        $lock = md5($listener);
        if (isset(static::$locks[$lock])) {
            return;
        }
        if (class_exists($listener)) {
            $listener = app()->make($listener);

            if (method_exists($listener, 'after')) {
                static::$locks[$lock] = true;
                call_user_func_array([$listener, 'after'], [$entry, $resource]);
                unset(static::$locks[$lock]);
            }
        }
    }

    public static function attributes($handle, array $attributes)
    {
        if (! $baseNamespace = static::$map[$handle] ?? null) {
            return $attributes;
        }

        if ($handle === 'platform::orders') {
            dd($baseNamespace);
        }

        if ($resourceKey = $attributes['config']['resource_key']) {
            $plural = str_plural($resourceKey);
        } else {
            $plural = explode('::', $handle)[1];
        }

        $configClass = $baseNamespace."\\".studly_case($plural.'_config');

        if (class_exists($configClass)) {
            $attributes['config'] = $configClass::make($attributes['config']);
        }

        return $attributes;
    }

    public static function resource_xxxx(Resource $resource)
    {
        if (! $base = static::$map[$resource->getIdentifier()] ?? null) {
            return;
        }

        $base = new $base($resource);

        if (method_exists($base, 'handle')) {
            $base->handle($resource);
        }
    }

    /** @return static */
    public static function resolve()
    {
        return app(static::class);
    }
}
