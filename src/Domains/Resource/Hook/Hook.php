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

    public function flush()
    {
        $this->map = [];
        static::$locks = [];
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
            $hookHandler = $namespace.'\\'.$className;

            if (! ($identifier = isset($hookHandler::$identifier) ? $hookHandler::$identifier : null)) {
                continue;
            }

            $this->register($identifier, $hookHandler, $className);
        }

        return $this;
    }

    public function hookType($identifier, $type, $hookHandler, $subKey = null)
    {
        $typeHook = str_replace_last("\\Hook", "\\".studly_case($type.'_hook'), get_class($this));
        if (class_exists($typeHook)) {
            app($typeHook)->hook($identifier, $hookHandler, $subKey);
        }
    }

    public function register($identifier, $hookHandler, $className)
    {
        if (str_is('*::*::*', $identifier)) {
            $parts = explode('.', $identifier);
            $identifier = sprintf('%s.%s', $parts[0], $parts[1]);

            list($hookType, $subKey) = explode('.', $parts[2]);
        } else {
            $parts = explode('_', snake_case($className));
            $hookType = end($parts);

            $subKey = null;
        }

        if (! isset($this->map[$identifier])) {
            $this->map[$identifier] = [];
        }

        if (! $subKey) {
            $this->map[$identifier][$hookType] = $hookHandler;
        } else {
            if (! isset($this->map[$identifier][$subKey])) {
                $this->map[$identifier][$hookType][$subKey] = [];
            }

            $this->map[$identifier][$hookType][$subKey] = $hookHandler;
        }

        $this->hookType($identifier, $hookType, $hookHandler, $subKey);

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
//        if (! $baseNamespace = static::base($resource->getIdentifier())) {
//            return;
//        }

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
//        if (! $baseNamespace = static::base($resource->getIdentifier())) {
//            return;
//        }

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

    /** @return static */
    public static function resolve()
    {
        return app(static::class);
    }
}
