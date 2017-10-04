<?php

namespace SuperV\Platform\Domains\Entry;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
use Robbo\Presenter\PresentableInterface;
use SuperV\Platform\Domains\Entry\Traits\PresentableTrait;
use SuperV\Platform\Domains\Entry\Traits\RoutableTrait;
use SuperV\Platform\Domains\Model\EloquentModel;

class EntryModel extends EloquentModel implements PresentableInterface
{
    use RoutableTrait, PresentableTrait;

    public static $routeKeyname = 'id';

    protected $fields = [];

    protected $relationships = [];

    protected $cache;

    protected $modelSlug;

    protected $onCreate;

    protected $hasUUID;

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

        if ($instance->hasUUID) {
            $instance->incrementing = false;
            static::creating(function (Model $model) {
                if (! isset($model->attributes[$model->getKeyName()])) {
                    $model->incrementing = false;
                    $uuid = Uuid::uuid4();
                    $model->attributes[$model->getKeyName()] = str_replace('-', '', $uuid->toString());
                }
            });
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
     * @return array
     */
    public function getRelationships(): array
    {
        return $this->relationships;
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

    /**
     * @return mixed
     */
    public function getSlug()
    {
        if ($this->modelSlug) {
            return $this->modelSlug;
        }

        return str_replace("\\", ".", strtolower(get_class($this)));
    }
}
