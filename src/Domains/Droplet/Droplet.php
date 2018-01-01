<?php

namespace SuperV\Platform\Domains\Droplet;

use JsonSerializable;
use SuperV\Platform\Domains\Droplet\Model\DropletModel;

class Droplet implements JsonSerializable
{
    /** @var DropletModel */
    protected $model;

    protected $commands;

    protected $type;

    protected $manifests = [];

    protected $seeders = [];

    protected $sortOrder = 10;

    public function __construct(DropletModel $model = null)
    {
        $this->model = $model;
    }

    /** @return DropletServiceProvider */
    public function newServiceProvider()
    {
        $model = $this->getServiceProvider();

        if (! class_exists($model)) {
            throw new \InvalidArgumentException("Provider class does not exist: {$model}");
        }

        return new $model(app(), $this);
    }

    public function getServiceProvider()
    {
        return get_class($this).'ServiceProvider';
    }

    public function getSlug()
    {
        return $this->model->slug;
    }

    public function getName()
    {
        return $this->model->getName();
    }

    public function getCommand($command)
    {
        return array_get($this->commands, $command);
    }

    public function getPath($path = null)
    {
        return $this->model->getPath($path);
    }

    public function getBasePath($path = null)
    {
        return base_path($this->model->getPath($path));
    }

    public function getResourcePath($path)
    {
        return $this->getPath("resources/{$path}");
    }

    public function getConfigPath($path)
    {
        return $this->getPath("config/{$path}");
    }

    public function getType()
    {
        return $this->type;
    }

    public function isType($type)
    {
        return $this->type == $type;
    }

    /**
     * @return DropletModel
     */
    public function getModel(): DropletModel
    {
        return $this->model;
    }

    public function setModel(DropletModel $model)
    {
        $this->model = $model;

        return $this;
    }

    public function getModelId()
    {
        return $this->model->getId();
    }

    /**
     * @return mixed
     */
    public function getSeeders()
    {
        return $this->seeders;
    }

    /**
     * @param mixed $seeders
     *
     * @return Droplet
     */
    public function setSeeders($seeders)
    {
        $this->seeders = $seeders;

        return $this;
    }

    public function getNamespace()
    {
        return $this->model->getNamespace();
    }

    public function destroy()
    {
        $this->model->delete();
    }

    public function toArray()
    {
        return $this->model->toArray();
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
