<?php

namespace SuperV\Platform\Domains\Droplet;

use SuperV\Platform\Domains\Droplet\Model\DropletModel;

class Droplet
{
    protected $title = 'Droplet';

    protected $link = '/';

    protected $icon = 'droplet';

    protected $navigation = false;

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

    public static function from(DropletModel $model)
    {
        return app($model->droplet(), ['model' => $model]);
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

    public function identifier()
    {
        return "{$this->model->getVendor()}.{$this->model->getName()}";
    }

    public function getCommand($command)
    {
        return array_get($this->commands, $command);
    }

    public function getPath($path = null)
    {
        return $this->model->getPath($path);
    }

    public function getResourcePath($path)
    {
        return $this->getPath("resources/{$path}");
    }

    public function getConfigPath($path)
    {
        return $this->getPath("config/{$path}");
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    public function isType($type)
    {
        return $this->type == $type;
    }

    /**
     * @return mixed
     */
    public function getManifests()
    {
        return $this->manifests;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @return bool
     */
    public function isNavigation(): bool
    {
        return $this->navigation;
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

    /**
     * @return mixed
     */
    public function getCommands()
    {
        return $this->commands;
    }

    public function getSortOrder()
    {
        return $this->sortOrder;
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


}
