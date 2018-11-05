<?php

namespace SuperV\Platform\Domains\Database;

use SuperV\Platform\Domains\Resource\Blueprint as ResourceBlueprint;

/**
 * @method \Illuminate\Database\Schema\Builder create($table, \Closure $callback)
 * @method static $this table($table, \Closure $callback)
 * @method static $this drop($table)
 * @method static $this dropIfExists($table)
 */
class Schema
{
    /**
     * Model of the table
     *
     * @var string
     */
    protected $model;

    /** @var \SuperV\Platform\Domains\Database\Blueprint  */
    protected $resource;

    protected $translatable = false;

    /** @var \Illuminate\Database\Schema\Builder */
    protected $builder;

    /** @var \SuperV\Modules\Nucleo\Domains\Prototype\Prototype */
    protected $prototype;

    protected $columns;

    public $doNotDo;

    public function __construct()
    {
        if (! $this->builder) {
            $this->builder = new Builder(\DB::connection(), $this);

            $this->builder->blueprintResolver(function ($table, $callback) {
                return new Blueprint($table, $callback, $this);
            });
        }

        $this->resource = new ResourceBlueprint();
    }

    public function resource(): ResourceBlueprint
    {
        return $this->resource;
    }

    public function getResource(): ResourceBlueprint
    {
        return $this->resource;
    }

    public static function nucleo($table, $callback)
    {
        $schema = new static;
        $schema->doNotDo = true;

        return call_user_func_array([$schema, 'create'], [$table, $callback]);
    }

    /**
     * @param bool $translatable
     * @return Schema
     */
    public function translatable(bool $translatable): Schema
    {
        $this->translatable = $translatable;

        return $this;
    }

    /**
     * @return bool
     */
    public function isTranslatable(): bool
    {
        return $this->translatable;
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this, $name)) {
            return call_user_func_array([$this, $name], $arguments);
        }

        if (method_exists($this->builder, $name)) {
            return call_user_func_array([$this->builder, $name], $arguments);
        }

        throw new \InvalidArgumentException('Method not found: '.$name);
    }

    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([new static, $name], $arguments);
    }

    /**
     * @return \Illuminate\Database\Schema\Builder
     */
    public function builder()
    {
        return $this->builder;
    }

    /**
     * @param string $model
     */
    public function setModel(string $model): void
    {
        $this->model = $model;
    }

    /**
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }
}