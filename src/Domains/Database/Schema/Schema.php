<?php

namespace SuperV\Platform\Domains\Database\Schema;

/**
 * @method \SuperV\Platform\Domains\Resource\ResourceConfig create($table, \Closure $callback)
 * @method static $this table($table, \Closure $callback)
 * @method static $this drop($table)
 * @method static $this dropIfExists($table)
 */
class Schema
{
    public $justRun;

    /** @var \SuperV\Platform\Domains\Database\Schema\Blueprint */
    protected $resource;

    /** @var \SuperV\Platform\Domains\Database\Schema\Builder */
    protected $builder;

    protected $columns;

    public function __construct()
    {
        if (! $this->builder) {
            $this->builder = new Builder(\DB::connection(), $this);

            $this->builder->blueprintResolver(function ($table, $callback) {
                return (new Blueprint($table, $callback, $this));
            });
        }
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

    public function builder()
    {
        return $this->builder;
    }

    public static function run($table, $callback)
    {
        $schema = new static;
        $schema->justRun = true;

        return call_user_func_array([$schema, 'create'], [$table, $callback]);
    }

    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([new static, $name], $arguments);
    }
}
