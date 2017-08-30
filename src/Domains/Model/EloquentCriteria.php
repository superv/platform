<?php

namespace SuperV\Platform\Domains\Model;

use SuperV\Platform\Support\Decorator;
use Illuminate\Database\Eloquent\Builder;

class EloquentCriteria
{
    /**
     * Additional available methods.
     *
     * @var array
     */
    protected $available = [
        'whereBetween',
        'whereNotBetween',
        'whereIn',
        'whereNotIn',
        'whereNull',
        'whereNotNull',
        'whereDate',
        'whereMonth',
        'whereDay',
        'whereYear',
        'whereColumn',
    ];

    /**
     * Safe builder methods.
     *
     * @var array
     */
    private $disabled = [
        'delete',
        'update',
    ];

    /**
     * The query builder.
     *
     * @var Builder|\Illuminate\Database\Query\Builder
     */
    protected $query;

    /**
     * Set the get method.
     *
     * @var string
     */
    protected $method;

    /**
     * Create a new EntryCriteria instance.
     *
     * @param Builder $query
     * @param string  $method
     */
    public function __construct(Builder $query, $method = 'get')
    {
        $this->query = $query;
        $this->method = $method;
    }

    public function paginate($perPage = 15, array $columns = ['*'])
    {
        return (new Decorator())->decorate($this->query->paginate($perPage, $columns));
    }

    public function get(array $columns = ['*'])
    {
        return (new Decorator())->decorate($this->query->{$this->method}($columns));
    }

    public function count(array $columns = ['*'])
    {
        return (new Decorator())->decorate($this->query->count($columns));
    }

    public function sum($column)
    {
        return (new Decorator())->decorate($this->query->sum($column));
    }

    public function max($column)
    {
        return (new Decorator())->decorate($this->query->max($column));
    }

    public function min($column)
    {
        return (new Decorator())->decorate($this->query->min($column));
    }

    public function avg($column)
    {
        return (new Decorator())->decorate($this->query->avg($column));
    }

    public function find($identifier, array $columns = ['*'])
    {
        return (new Decorator())->decorate($this->query->find($identifier, $columns));
    }

    public function findBy($column, $value, array $columns = ['*'])
    {
        $this->query->where($column, $value);

        return (new Decorator())->decorate($this->query->first($columns));
    }

    public function first(array $columns = ['*'])
    {
        return (new Decorator())->decorate($this->query->first($columns));
    }

    protected function methodIsSafe($name)
    {
        return ! in_array($name, $this->disabled);
    }

    protected function methodExists($name)
    {
        return method_exists($this->query->getQuery(), $name);
    }

    public function __get($name)
    {
        return $this->__call($name, []);
    }

    public function __call($name, $arguments)
    {
        if ($this->methodExists($name) && $this->methodIsSafe($name)) {
            call_user_func_array([$this->query, $name], $arguments);

            return $this;
        }

        if (starts_with($name, 'findBy') && $column = snake_case(substr($name, 6))) {
            call_user_func_array([$this->query, 'where'], array_merge([$column], $arguments));

            return $this->first();
        }

        if (starts_with($name, 'where') && $column = snake_case(substr($name, 5))) {
            call_user_func_array([$this->query, 'where'], array_merge([$column], $arguments));

            return $this;
        }

        return $this;
    }

    /**
     * Return the string.
     *
     * @return string
     */
    public function __toString()
    {
        return '';
    }
}
