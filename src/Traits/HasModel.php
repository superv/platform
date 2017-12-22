<?php

namespace SuperV\Platform\Traits;

trait HasModel
{
    /** @var string */
    protected $model;

    public function newModelInstance()
    {
        return app($this->model);
    }

    /**
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    public function hasModel()
    {
        return ! is_null($this->model);
    }

    /**
     * @param string $model
     *
     * @return $this
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }
}