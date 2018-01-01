<?php

namespace SuperV\Platform\Domains\Model;

use SuperV\Platform\Contracts\Container;
use SuperV\Platform\Domains\Entry\EntryModel;
use SuperV\Platform\Support\Collection;
use SuperV\Platform\Traits\EnforcableTrait;

class EloquentRepository implements RepositoryInterface
{
    use EnforcableTrait;

    /** @var \Illuminate\Database\Eloquent\Builder */
    protected $query;

    public function __construct(Container $container)
    {
        if (! class_exists($model = str_singular(get_class($this)).'Model')) {
            if (! class_exists($model = str_singular(get_class($this)))) {
                throw new \Exception('Repository model not found: '.$model);
            }
        }
        $this->query = $container->make($model);
    }

    /** @return Collection */
    public function all()
    {
        return $this->query->all();
    }

    /**
     * @param $id
     *
     * @return EntryModel|null
     */
    public function find($id)
    {
        if (is_string($id) && ! is_numeric($id)) {
            return $this->withSlug($id);
        }

        return $this->query->find($id);
    }

    public function first()
    {
        return $this->query->first();
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

    /** @return EntryModel */
    public function withSlug($slug)
    {
        return $this->query->where('slug', $slug)->first();
    }

    /** @return EntryModel */
    public function withUUID($uuid)
    {
        return $this->query->where('uuid', $uuid)->first();
    }

    public function enabled()
    {
        return $this->query->where('enabled', true)->get();
    }

    public function collection($items)
    {
        return collect($items);
    }

    public function truncate()
    {
        $this->query->get()->each->delete();
//       $this->query->truncate();
    }

    public function __call($name, $arguments)
    {
        if (starts_with($name, 'by')) {
            $keyName = studly_case(str_replace_first('by', '', $name));

            return $this->query->where($keyName, $arguments[0])->first();
        }

        throw new \BadMethodCallException("Method {$name} does not exists");
    }
}
