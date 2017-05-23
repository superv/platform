<?php namespace SuperV\Platform\Domains\Droplet;

use SuperV\Platform\Domains\Droplet\Model\DropletModel;

class Droplet
{
    /** @var  DropletModel */
    protected $model;

    protected $commands;

    public function __construct(DropletModel $model = null)
    {
        $this->model = $model;
    }

    public static function from(DropletModel $model)
    {
        return superv($model->droplet(), ['model' => $model]);
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

    public function identifier()
    {
        return $this->model->vendor . '.' . $this->model->name;
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