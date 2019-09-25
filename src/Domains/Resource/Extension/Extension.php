<?php

namespace SuperV\Platform\Domains\Resource\Extension;

use Illuminate\Events\Dispatcher;
use SuperV\Platform\Domains\Resource\Database\Entry\Events\EntryCreatingEvent;
use SuperV\Platform\Domains\Resource\Database\Entry\Events\EntryRetrievedEvent;
use SuperV\Platform\Domains\Resource\Database\Entry\Events\EntrySavedEvent;
use SuperV\Platform\Domains\Resource\Database\Entry\Events\EntrySavingEvent;
use SuperV\Platform\Domains\Resource\Extension\Contracts\ExtendsMultipleResources;
use SuperV\Platform\Domains\Resource\Extension\Contracts\ExtendsResource;
use SuperV\Platform\Domains\Resource\Extension\Contracts\ObservesEntryCreating;
use SuperV\Platform\Domains\Resource\Extension\Contracts\ObservesEntryRetrieved;
use SuperV\Platform\Domains\Resource\Extension\Contracts\ObservesEntrySaved;
use SuperV\Platform\Domains\Resource\Extension\Contracts\ObservesEntrySaving;
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
        } elseif ($extension instanceof ExtendsMultipleResources) {
            $pattern = $extension->pattern();
            foreach ((array)$pattern as $key) {
                static::$map[$key] = $extension;
            }
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
        if ($extension = Extension::get($resource->getIdentifier())) {
            if ($extension instanceof ExtendsResource) {
                $extension->extend($resource);
            } elseif ($extension instanceof ExtendsMultipleResources) {
                $method = camel_case('extend_'.$resource->getIdentifier());
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

    public static function flush()
    {
        static::$map = [];
    }

    protected function registerSingle(ExtendsResource $extension)
    {
        static::$map[$extension->extends()] = $extension;

        if ($extension instanceof ObservesEntryRetrieved) {
            $this->events->listen(EntryRetrievedEvent::class,
                function (EntryRetrievedEvent $event) use ($extension) {
                    if ($event->entry->getResourceIdentifier() === $extension->extends()) {
                        $extension->retrieved($event->entry);
                    }
                });
        }

        if ($extension instanceof ObservesEntryCreating) {
            $this->events->listen(EntryCreatingEvent::class,
                function (EntryCreatingEvent $event) use ($extension) {
                    if ($event->entry->getResourceIdentifier() === $extension->extends()) {
                        $extension->creating($event->entry);
                    }
                });
        }

        if ($extension instanceof ObservesEntrySaving) {
            $this->events->listen(EntrySavingEvent::class,
                function (EntrySavingEvent $event) use ($extension) {
                    if ($event->entry->getResourceIdentifier() === $extension->extends()) {
                        $extension->saving($event->entry);
                    }
                });
        }

        if ($extension instanceof ObservesEntrySaved) {
            $this->events->listen(EntrySavedEvent::class,
                function (EntrySavedEvent $event) use ($extension) {
                    if ($event->entry->getResourceIdentifier() === $extension->extends()) {
                        $extension->saved($event->entry);
                    }
                });
        }
    }
}
