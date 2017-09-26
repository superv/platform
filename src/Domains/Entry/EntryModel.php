<?php

namespace SuperV\Platform\Domains\Entry;

use Closure;
use Robbo\Presenter\PresentableInterface;
use SuperV\Platform\Domains\Model\EloquentModel;
use SuperV\Platform\Domains\Entry\Traits\RoutableTrait;
use SuperV\Platform\Domains\Entry\Traits\PresentableTrait;

class EntryModel extends EloquentModel implements PresentableInterface
{
    use RoutableTrait, PresentableTrait;

    public static $routeKeyname = 'id';

    protected $fields = [];

    protected $relationships = [];

    protected $cache;

    protected $onCreate;

    protected static function boot()
    {
        $instance = new static;

        $class = get_class($instance);
        $events = $instance->getObservableEvents();
        $observer = substr($class, 0, -5).'Observer';
        $observing = class_exists($observer);

        if ($events && $observing) {
            self::observe(app($observer));
        }

        if ($events && ! $observing) {
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

    public function flushCache()
    {
    }

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

    /**
     * @param Closure $callback
     *
     * @return EntryModel
     */
    public function onCreate(Closure $callback)
    {
        $this->onCreate = $callback;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOnCreateCallback()
    {
        return $this->onCreate;
    }

    /**
     * @param $verb
     *
     * @return Page
     */
    public function page($verb)
    {
        /** @var Page $page */
        if ($page = superv('pages')->byModel(get_class($this))->get($verb)) {
            return $page->setEntry($this);
        }
    }
}
