<?php

namespace SuperV\Platform\Domains\Droplet\Model;

class DropletModel extends DropletEntryModel
{
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    public function getPath($path = null)
    {
        return $this->path.($path ? '/'.$path : '');
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
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
