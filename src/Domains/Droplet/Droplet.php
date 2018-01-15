<?php

namespace SuperV\Platform\Domains\Droplet;

use SuperV\Platform\Domains\Entry\EntryModel;
use SuperV\Platform\Traits\FiresCallbacks;

class Droplet extends EntryModel
{
    use FiresCallbacks;

    protected $table = 'droplets';

    protected $seeders = [];

    public function locate()
    {
        $paths = [
            'workbench',
            'droplets',
        ];

        $clues = [$this->path];

        foreach ($paths as $path) {
            foreach ($clues as $clue) {
                $path = starts_with($clue, $paths) ? $clue : "{$path}/{$clue}";
                if (is_dir(base_path($path))) {
                    $this->setPath($path);

                    return $this;
                }
            }
        }

        throw new \Exception("Droplet could not be located: {$this->slug}");
    }

    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    public function getPath($path = null)
    {
        return $this->path.($path ? '/'.$path : '');
    }

    public function getBasePath($path = null)
    {
        return base_path($this->getPath($path));
    }

    public function getResourcePath($path)
    {
        return $this->getPath("resources/{$path}");
    }

    public function getConfigPath($path)
    {
        return $this->getPath("config/{$path}");
    }

    public function enable()
    {
        $this->enabled = true;

        return $this;
    }

    public function getNamespace()
    {
        return ucfirst(camel_case(($this->vendor == 'superv' ? 'super_v' : $this->vendor))).'\\'.ucfirst(camel_case(str_plural($this->type))).'\\'.ucfirst(camel_case($this->name));
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

    public function droplet()
    {
        return $this->getNamespace().'\\'.studly_case("{$this->name}_{$this->type}");
    }

    /** @return self */
    public function newDropletInstance()
    {
        return app($this->droplet(), ['attributes' => $this->toArray()]);
    }

    public function isType($type)
    {
        return $this->type == $type;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getVendor()
    {
        return $this->vendor;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function getSeeders()
    {
        return $this->seeders;
    }

    public function setSeeders($seeders)
    {
        $this->seeders = $seeders;

        return $this;
    }
}
