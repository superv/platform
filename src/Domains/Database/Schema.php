<?php

namespace SuperV\Platform\Domains\Database;

use SuperV\Platform\Domains\Database\Blueprint\Blueprint;

/**
 * @method \Illuminate\Database\Schema\Builder create($table, \Closure $callback)
 * @method static $this table($table, \Closure $callback)
 * @method static $this drop($table)
 * @method static $this dropIfExists($table)
 */
class Schema
{
    /** @var \SuperV\Platform\Domains\Database\Blueprint\Blueprint  */
    protected $resource;

    /** @var \SuperV\Platform\Domains\Database\Builder */
    protected $builder;

    protected $columns;

    public $justRun;

    public function __construct()
    {
        if (! $this->builder) {
            $this->builder = new Builder(\DB::connection(), $this);

            $this->builder->blueprintResolver(function ($table, $callback) {
                return new Blueprint($table, $callback, $this);
            });
        }

//        $this->resource = new ResourceBlueprint();
    }

//    public function resource(): ResourceBlueprint
//    {
//        return $this->resource;
//    }

//    public function getResource(): ResourceBlueprint
//    {
//        return $this->resource;
//    }

    public static function nucleo($table, $callback)
    {
        $schema = new static;
        $schema->justRun = true;

        return call_user_func_array([$schema, 'create'], [$table, $callback]);
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

    public function builder()
    {
        return $this->builder;
    }
}