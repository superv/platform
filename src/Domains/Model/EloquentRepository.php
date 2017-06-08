<?php namespace SuperV\Platform\Domains\Model;

use SuperV\Platform\Contracts\Container;

class EloquentRepository implements RepositoryInterface
{
    protected $model;

    public function __construct(Container $container)
    {
        $class = str_singular(get_class($this)) . 'Model';
        $this->model = $container->make($class);
    }

    public function all()
    {
        return $this->model->all();
//        return $this->collection($this->model->all());
    }

    public function find($id)
    {
        if (is_string($id)) {
            return $this->withSlug($id);
        }

        return $this->model->find($id);
    }

    public function create(array $attributes)
    {
        return $this->model->create($attributes);
    }

    public function newQuery()
    {
        return $this->model->newQuery();
    }

    public function newInstance(array $attributes = [])
    {
        return $this->model->newInstance($attributes);
    }

    public function update(array $attributes = [])
    {
        return $this->model->update($attributes);
    }

    public function withSlug($slug)
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function enabled()
    {
        $droplets = $this->model->where('enabled', true)->orderBy('type', 'DESC')->get();
        return $droplets;
//        return $this->collection($droplets);
    }

    public function collection($items)
    {
        return collect($items);
    }
}