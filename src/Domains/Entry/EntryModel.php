<?php namespace SuperV\Platform\Domains\Entry;

use SuperV\Platform\Domains\Model\EloquentModel;

class EntryModel extends EloquentModel
{
    public function flushCache() { }

    protected static function boot()
    {
        $instance = new static;

        $class = get_class($instance);
        $events = $instance->getObservableEvents();
        $observer = substr($class, 0, -5) . 'Observer';
        $observing = class_exists($observer);

        if ($events && $observing) {
            self::observe(app($observer));
        }

        if ($events && !$observing) {
            self::observe(EntryObserver::class);
        }

        parent::boot();
    }
}