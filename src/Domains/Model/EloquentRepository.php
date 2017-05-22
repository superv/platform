<?php namespace SuperV\Platform\Domains\Model;

use SuperV\Platform\Contracts\Container;

abstract class EloquentRepository
{
    protected $model;

    public function __construct(Container $container)
    {
        $class = str_singular(get_class($this)) . 'Model';
        $this->model = $container->make($class);
    }

    public function all()
    {
        return $this->collection($this->model->all());
    }

    public function find($id)
    {
        if (is_string($id)) {
            return $this->withSlug($id);
        }

        return $this->model->find($id);
    }

    public function findAll(array $ids)
    {
        return $this->collection($this->model->whereIn('id', $ids)->get());
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

    public function count()
    {
        return $this->model->count();
    }

    public function save(EloquentModel $entry)
    {
        return $entry->save();
    }

    public function update(array $attributes = [])
    {
        return $this->model->update($attributes);
    }

    public function delete(EloquentModel $entry)
    {
        return $entry->delete();
    }

    public function withSlug($slug)
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function enabled()
    {
        return $this->collection($this->model->where('enabled', true)->get());
    }

    protected function collection($items)
    {
        return collect($items);
    }
}