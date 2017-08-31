<?php

namespace SuperV\Platform\Domains\Model;

use SuperV\Platform\Contracts\Container;

class EloquentRepository implements RepositoryInterface
{
    /** @var \Illuminate\Database\Eloquent\Builder */
    protected $query;

    public function __construct(Container $container)
    {
        $model = str_singular(get_class($this)).'Model';
        if (! class_exists($model)) {
            throw new \Exception('Repository model not found: '.$model);
        }
        $this->query = $container->make($model)->query();
    }

    public function all()
    {
        return $this->query->all();
    }

    public function find($id)
    {
        if (is_string($id) && ! is_numeric($id)) {
            return $this->withSlug($id);
        }

        return $this->query->find($id);
    }

    public function in(array $ids)
    {
        return $this->query->whereIn('id', $ids)->get();
    }

    public function create(array $attributes)
    {
        return $this->query->create($attributes);
    }

    public function newQuery()
    {
        return $this->query->newQuery();
    }

    public function newInstance(array $attributes = [])
    {
        return $this->query->newInstance($attributes);
    }

    public function update(array $attributes = [])
    {
        return $this->query->update($attributes);
    }

    public function withSlug($slug)
    {
        return $this->query->where('slug', $slug)->first();
    }

    public function enabled()
    {
        $droplets = $this->query->where('enabled', true)->orderBy('type', 'DESC')->get();

        return $droplets;
//        return $this->collection($droplets);
    }

    public function collection($items)
    {
        return collect($items);
    }
}
