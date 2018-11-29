<?php

namespace SuperV\Modules\Nucleo\Domains\Resource\Table\Filter;

use Illuminate\Database\Eloquent\Builder;
use SuperV\Modules\Nucleo\Domains\Resource\Resource;
use SuperV\Platform\Support\Composer\Composable;
use SuperV\Platform\Support\Concerns\HasConfig;

class Filter implements Composable
{
    use HasConfig;

    /**
     * @var string
     */
    protected $slug;

    protected $type = 'text';

    protected $placeholder;

    protected $default;

    /** @var \SuperV\Modules\Nucleo\Domains\Resource\Resource */
    protected $resource;

    /** @var \Closure */
    protected $callback;

    public static function make($slug)
    {
        $filter = new static();
        $filter->slug = $slug;

        return $filter;
    }

    public function apply(Builder $query, $value)
    {
        if ($this->callback) {
            return ($this->callback)($query, $value);
        }

        if (str_contains($this->slug, '.')) {
            return $this->applyRelationQuery($query, $this->slug, $value);
        }
        $query->where($this->slug, '=', $value);
    }

    public function build()
    {
    }

    protected function applyRelationQuery(Builder $query, $slug, $value, $operator = '=', $method = 'whereHas')
    {
        list($relation, $column) = explode('.', $slug);
        $query->{$method}($relation, function (Builder $query) use ($column, $value, $operator) {
            $query->where($column, $operator, $value);
        });
    }


    public function placeholder()
    {
        return $this->placeholder ?: ucfirst($this->slug);
    }

    /**
     * @param string $type
     * @return Filter
     */
    public function type(string $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function slug(): string
    {
        return $this->slug;
    }

    /**
     * @param \SuperV\Modules\Nucleo\Domains\Resource\Resource $resource
     * @return Filter
     */
    public function resource(Resource $resource)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * @param \Closure $callback
     * @return Filter
     */
    public function callback(\Closure $callback)
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * @param mixed $default
     * @return Filter
     */
    public function default($default)
    {
        $this->default = $default;

        return $this;
    }

    public function compose(array $params = [])
    {
        return array_filter(
            [
                'name'        => $this->slug,
                'type'        => $this->type,
                'placeholder' => $this->placeholder(),
                'config'      => $this->config(),
                'default'     => $this->default,
            ]
        );
    }
}