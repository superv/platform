<?php

namespace SuperV\Platform\Domains\Resource\Extension;

use Illuminate\Events\Dispatcher;
use SuperV\Platform\Domains\Resource\Extension\Contracts\ExtendsMatchingResources;
use SuperV\Platform\Domains\Resource\Extension\Contracts\ExtendsMultipleResources;
use SuperV\Platform\Domains\Resource\Extension\Contracts\ExtendsResource;
use SuperV\Platform\Domains\Resource\Extension\Contracts\ObservesRetrieved;
use SuperV\Platform\Domains\Resource\Extension\Contracts\ObservesSaved;
use SuperV\Platform\Domains\Resource\Extension\Contracts\ObservesSaving;
use SuperV\Platform\Domains\Resource\Model\Events\EntryRetrievedEvent;
use SuperV\Platform\Domains\Resource\Model\Events\EntrySavedEvent;
use SuperV\Platform\Domains\Resource\Model\Events\EntrySavingEvent;
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
            return $this->registerSingle($extension);
        }

        if ($extension instanceof ExtendsMultipleResources) {
            $pattern = $extension->pattern();
            foreach ((array)$pattern as $key) {
                static::$map[$key] = $extension;
            }
        }
    }

    protected function registerSingle(ExtendsResource $extension)
    {
        static::$map[$extension->extends()] = $extension;

        if ($extension instanceof ObservesRetrieved) {
            $this->events->listen(EntryRetrievedEvent::class, function (EntryRetrievedEvent $event) use ($extension) {
                $extension->retrieved($event->entry);
            });
        }

        if ($extension instanceof ObservesSaving) {
            $this->events->listen(EntrySavingEvent::class, function (EntrySavingEvent $event) use ($extension) {
                $extension->saving($event->entry);
            });
        }

        if ($extension instanceof ObservesSaved) {
            $this->events->listen(EntrySavedEvent::class, function (EntrySavedEvent $event) use ($extension) {
                $extension->saved($event->entry);
            });
        }
    }

    public static function get($handle)
    {
        if ($extension = (static::$map[$handle] ?? null)) {
            return $extension;
        }

        // check for wildcard
        foreach (static::$map as $key => $extension) {
            if (fnmatch($key, $handle)) {
                return $extension;
            }
        }

        return null;
    }

    public static function extend(Resource $resource)
    {
        if ($extension = Extension::get($resource->getHandle())) {
            if ($extension instanceof ExtendsResource) {
                $extension->extend($resource);
            } elseif ($extension instanceof ExtendsMultipleResources) {
                $method = camel_case('extend_'.$resource->getHandle());
                $extension->$method($resource);
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
}