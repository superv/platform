<?php

namespace SuperV\Platform\Domains\Entry;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
use SuperV\Platform\Domains\Table\TableBuilder;

class EntryModel extends Model
{
    protected $guarded = [];

    protected $onCreate;

    protected $hasUUID;

    public static $rules;

    public static function rulesSometimes()
    {
        return static::rules(true);
    }

    public static function rules($sometimes = false)
    {
        if ($sometimes) {
            return collect(static::$rules)->map(function ($rule) {
                return 'sometimes|'.$rule;
            })->all();
        }

        return static::$rules;
    }

    protected static function boot()
    {
        $instance = new static;

        $class = get_class($instance);
        $events = $instance->getObservableEvents();
        $observer = str_replace_last('Model', '', $class).'Observer';
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

    public function flushCache()
    {
    }

    /**
     * @param Closure $callback
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

    public function compose()
    {
        return $this->toArray();
    }

    public static function getTableBuilder()
    {
        $tableBuilderClass = get_called_class().'TableBuilder';
        if (! class_exists($tableBuilderClass)) {
            $tableBuilderClass = TableBuilder::class;
        }

        return app($tableBuilderClass)->setModel(get_called_class());
    }

    public static function getEditor()
    {
        $editorClass = get_called_class().'Editor';
        if (! class_exists($editorClass)) {
            throw new \Exception('Editor not found for :'.get_called_class());
        }

        return app($editorClass, ['model' => get_called_class()]);
    }
}