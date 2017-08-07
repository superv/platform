<?php namespace SuperV\Platform\Domains\Manifest;

abstract class Manifest
{
    protected $model;

    protected $pages = [];

    /**
     * @return mixed
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param mixed $model
     *
     * @return Manifest
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
}
}