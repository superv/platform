<?php

namespace SuperV\Platform\Support\Concerns;

use Closure;

trait FiresCallbacks
{
    protected $callbacks = [];

    public function on($trigger, Closure $callback)
    {
        if (! isset($this->callbacks[$trigger])) {
            $this->callbacks[$trigger] = [];
        }

        $this->callbacks[$trigger][] = $callback;

        return $this;
    }

    public function fire($trigger, array $parameters = [])
    {
        /*
         * Next, check if the method
         * exists and run it if it does.
         */
        $method = camel_case('on_'.$trigger);

        if (method_exists($this, $method)) {
            app()->call([$this, $method], $parameters);
        }

        /*
         * Finally, run through all of
         * the registered callbacks.
         */
        foreach (array_get($this->callbacks, $trigger, []) as $callback) {
            if (is_string($callback) || $callback instanceof \Closure) {
                app()->call($callback, $parameters);
            }

            if (method_exists($callback, 'handle')) {
                app()->call([$callback, 'handle'], $parameters);
            }
        }

        return $this;
    }

    /**
     * Return if the callback exists.
     *
     * @param $trigger
     * @return bool
     */
    public function hasCallback($trigger)
    {
        return isset($this->callbacks[$trigger]);
    }

    public function getCallback($trigger): ?Closure
    {
        if (! $this->hasCallback($trigger)) {
            return null;
        }

        return $this->callbacks[$trigger][0];
    }

    /**
     * Return if the listener exists.
     *
     * @param $trigger
     * @return bool
     */
    public function hasListener($trigger)
    {
        return isset(self::$listeners[get_class($this).'::'.$trigger]);
    }
}
