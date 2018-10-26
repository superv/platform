<?php

namespace SuperV\Platform\Domains\Database;

/**
 * @method \Illuminate\Database\Schema\Builder create($table, \Closure $callback)
 * @method static $this table($table, \Closure $callback)
 * @method static $this drop($table)
 * @method static $this dropIfExists($table)
 */
class Schema
{
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
}