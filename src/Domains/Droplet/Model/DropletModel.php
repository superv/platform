<?php

namespace SuperV\Platform\Domains\Droplet\Model;

class DropletModel extends DropletEntryModel
{
    public function path($path = null)
    {
        if ($path) {
            $this->setAttribute('path', $path);

            return $this;
        }

        return $this->getAttribute('path');
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

    public function namespace($namespace = null)
    {
        if ($namespace) {
            $this->setAttribute('namespace', $namespace);

            return $this;
        }

        return $this->getAttribute('namespace');
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
        return $this->namespace().'\\'.studly_case("{$this->name}_{$this->type}");
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
