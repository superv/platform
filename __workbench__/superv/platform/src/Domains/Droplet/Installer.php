<?php

namespace SuperV\Platform\Domains\Droplet;

use Illuminate\Contracts\Console\Kernel;
use SuperV\Platform\Exceptions\PathNotFoundException;

class Installer
{
    protected $slug;

    protected $path;

    /**
     * @var \Illuminate\Contracts\Console\Kernel
     */
    protected $console;

    public function __construct(Kernel $console)
    {
        $this->console = $console;
    }

    public function install()
    {
        if (! $this->path || ! file_exists(base_path($this->path))) {
            throw new PathNotFoundException("Path not found for droplet {$this->slug}");
        }
        $droplet = new DropletModel([
            'name'      => $this->name(),
            'slug'      => $this->slug,
            'path'      => $this->path,
            'type'      => $this->type(),
            'namespace' => $this->namespace(),
            'enabled'   => true,
        ]);

        $droplet->save();

        app()->register($droplet->resolveDroplet()->resolveProvider());

        $this->console->call('migrate', ['--scope' => $droplet->slug]);
    }

    public function type()
    {
        $composer = json_decode(file_get_contents(base_path($this->path.'/composer.json')), true);

        return explode('-', $composer['type'])[1];
    }

    public function namespace()
    {
        $composer = json_decode(file_get_contents(base_path($this->path.'/composer.json')), true);

        $namespace = array_keys(array_get($composer['autoload'], 'psr-4'))[0];

        return rtrim($namespace, '\\');
    }

    public function name()
    {
        $composer = json_decode(file_get_contents(base_path($this->path.'/composer.json')), true);

        return studly_case(str_replace('-', '_', explode('/', $composer['name'])[1]));
    }

    /**
     * @param string $slug
     *
     * @return Installer
     */
    public function slug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @param string $path
     *
     * @return Installer
     */
    public function path($path)
    {
        $this->path = $path;

        return $this;
    }
}