<?php namespace SuperV\Platform\Domains\Entry;

use SuperV\Platform\Domains\Entry\Traits\PresentableTrait;
use SuperV\Platform\Domains\Entry\Traits\RoutableTrait;
use SuperV\Platform\Domains\Model\EloquentModel;

class EntryModel extends EloquentModel
{
    use RoutableTrait, PresentableTrait;

    protected $fields = [];

    protected $relationships = [];

    protected $cache;

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

    public function getId()
    {
        return $this->getKey();
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function flushCache() { }

    /**
     * @param array $relationships
     *
     * @return EntryModel
     */
    public function setRelationships(array $relationships): EntryModel
    {
        $this->relationships = $relationships;

        return $this;
    }

    /**
     * @return array
     */
    public function getRelationships(): array
    {
        return $this->relationships;
    }
}