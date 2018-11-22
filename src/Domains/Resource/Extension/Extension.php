<?php

namespace SuperV\Platform\Domains\Resource\Extension;

use Illuminate\Events\Dispatcher;
use SuperV\Platform\Domains\Resource\Extension\Contracts\ObservesSaved;
use SuperV\Platform\Domains\Resource\Extension\Contracts\ObservesSaving;
use SuperV\Platform\Domains\Resource\Extension\Contracts\ResourceExtension;
use SuperV\Platform\Domains\Resource\Model\Events\EntrySavedEvent;
use SuperV\Platform\Domains\Resource\Model\Events\EntrySavingEvent;
use SuperV\Platform\Exceptions\PlatformException;

class Extension
{
    protected static $map = [];

    /**
     * @var \Illuminate\Events\Dispatcher
     */
    protected $events;

    public function __construct(Dispatcher $events)
    {
        $this->events = $events;
    }

    public function __invoke(string $class)
    {
        if (! class_exists($class)) {
            throw new PlatformException("Extension class not found: [{$class}]");
        }
        $extender = app($class);
        static::extend($extender->extends(), $extender);

        if ($extender instanceof ObservesSaving) {
            $this->events->listen(EntrySavingEvent::class, function (EntrySavingEvent $event) use ($extender) {
                $extender->saving($event->entry);
            });
        }

        if ($extender instanceof ObservesSaved) {
            $this->events->listen(EntrySavedEvent::class, function (EntrySavedEvent $event) use ($extender) {
                $extender->saved($event->entry);
            });
        }
    }

    public static function get($slug): ?ResourceExtension
    {
        return static::$map[$slug] ?? null;
    }

    public static function extend($slug, $extension)
    {
        static::$map[$slug] = $extension;
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