<?php namespace SuperV\Platform\Domains\Droplet;

use SuperV\Platform\Domains\Droplet\Model\DropletModel;

class Droplet
{
    /** @var  DropletModel */
    protected $model;

    protected $commands;

    protected $type;

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
        $providerClass = $this->getServiceProvider();
        return new $providerClass(app(), $this);
    }

    public function getServiceProvider()
    {
        return get_class($this) . 'ServiceProvider';
    }

    public function getSlug()
    {
        return $this->model->slug;
    }

    public function getName()
    {
        return $this->model->name;
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

    public function getPath($path = null)
    {
        return $this->model->path() . ($path ? '/' . $path : '');
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }
}