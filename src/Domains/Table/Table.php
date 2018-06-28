<?php

namespace SuperV\Platform\Domains\Table;

use Illuminate\Support\Collection;
use SuperV\Platform\Support\Concerns\HasOptions;

class Table
{
    use HasOptions;

    protected $model;

    protected $filters;

    protected $entries;

    protected $columns;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $data;

    public function __construct(Collection $data, Collection $options)
    {
        $this->options = $options;
        $this->data = $data;
    }

    /**
     * @param mixed $model
     * @return Table
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @param mixed $entries
     * @return Table
     */
    public function setEntries($entries)
    {
        $this->entries = $entries;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function setData($key, $value)
    {
        $this->data->put($key, $value);

        return $this;
    }

    /**
     * @return Collection
     */
    public function getData()
    {
        return $this->data;
    }
}