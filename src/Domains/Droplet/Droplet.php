<?php namespace SuperV\Platform\Domains\Droplet;

use SuperV\Platform\Domains\Droplet\Model\DropletModel;

class Droplet
{
    /** @var  DropletModel */
    protected $model;

    protected $commands;

    public static function from(DropletModel $model)
    {
        return superv($model->droplet());
    }

    public function newServiceProvider()
    {
        return app()->make($this->getServiceProvider(), [app(), $this]);
    }

    public function getServiceProvider()
    {
        return get_class($this) . 'ServiceProvider';
    }

    public function getSlug()
    {
        return $this->model->slug;
    }

    public function setModel(DropletModel $model)
    {
        $this->model = $model;

        return $this;
    }

    public function getCommand($command)
    {
        return array_get($this->commands, $command);
    }

    public function getPath()
    {
        return $this->model->path();
    }
}