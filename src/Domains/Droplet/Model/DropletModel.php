<?php

namespace SuperV\Platform\Domains\Droplet\Model;

use SuperV\Platform\Domains\Entry\EntryModel;

class DropletModel extends EntryModel
{
    protected $table = 'platform_droplets';

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

        throw new \Exception("Droplet could not be located: {$this->name}");
    }

    public function enable()
    {
        $this->enabled = true;

        return $this;
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

    public function getNamespace()
    {
        return ucfirst(camel_case(($this->vendor == 'superv' ? 'super_v' : $this->vendor))).'\\'.ucfirst(camel_case(str_plural($this->type))).'\\'.ucfirst(camel_case($this->name));
    }

    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function droplet()
    {
        return $this->getNamespace().'\\'.studly_case("{$this->name}_{$this->type}");
    }

    public function newDropletInstance()
    {
        return app($this->droplet())->setModel($this);
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
}
