<?php

namespace SuperV\Platform\Domains\Resource\Extension;

use Illuminate\Events\Dispatcher;
use SuperV\Platform\Domains\Resource\Extension\Contracts\ExtendsResource;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Exceptions\PlatformException;

class Extension
{
    /**
     * @var \Illuminate\Events\Dispatcher
     */
    protected $events;

    protected static $map = [];

    public function __construct(Dispatcher $events)
    {
        $this->events = $events;
    }

    public function __invoke($extension)
    {
        if (is_string($extension)) {
            if (! class_exists($extension)) {
                throw new PlatformException("Extension class not found: [{$extension}]");
            }
            $extension = app($extension);
        }

        if ($extension instanceof ExtendsResource) {
            $this->registerSingle($extension);
        }
    }

    public static function get($handle)
    {
        return static::$map[$handle] ?? null;
    }

    public static function extend(Resource $resource)
    {
        if ($extension = static::get($resource->getIdentifier())) {
            if ($extension instanceof ExtendsResource) {
                $extension->extend($resource);
            }
        }
    }

    public static function register($extension)
    {
        app(static::class)($extension);
    }

    public static function unregister($extension)
    {
        unset(static::$map[app($extension)->extends()]);
    }

    public static function flush()
    {
        static::$map = [];
    }

    protected function registerSingle(ExtendsResource $extension)
    {
        static::$map[$extension->extends()] = $extension;
    }
}
