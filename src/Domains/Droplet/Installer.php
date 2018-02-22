<?php

namespace SuperV\Platform\Domains\Droplet;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Kernel;
use SuperV\Platform\Exceptions\PathNotFoundException;

class Installer
{
    protected $slug;

    protected $path;

    /** @var \Illuminate\Contracts\Console\Kernel */
    protected $console;

    /** @var \Illuminate\Console\Command */
    protected $command;

    public function __construct(Kernel $console)
    {
        $this->console = $console;
    }

    /**
     * Install droplet
     *
     * @throws \SuperV\Platform\Exceptions\PathNotFoundException
     */
    public function install()
    {
        if (! $this->path || ! file_exists(base_path($this->path))) {
            throw new PathNotFoundException("Path not found for droplet {$this->slug}");
        }
        $dropletEntry = new DropletModel([
            'name'      => $this->name(),
            'slug'      => $this->slug,
            'path'      => $this->path,
            'type'      => $this->type(),
            'namespace' => $this->namespace(),
            'enabled'   => true,
        ]);

        $dropletEntry->save();

        $droplet = $dropletEntry->resolveDroplet();
        app()->register($droplet->resolveProvider());

        $this->console->call(
            'migrate',
            ['--scope' => $droplet->slug()],
            $this->command ? $this->command->getOutput() : null
        );

        if ($subDroplets = $droplet->installs()) {
            foreach ($subDroplets as $slug => $path) {
                app(self::class)->slug($slug)
                                ->path($this->path . '/' . $path)
                                ->install();
            }
        }
    }

    /**
     * Parse droplet type from composer config
     *
     * @return string
     */
    public function type()
    {
        $composer = json_decode(file_get_contents(base_path($this->path.'/composer.json')), true);

        return explode('-', $composer['type'])[1];
    }

    /**
     * Parse PHP Namespace from composer config
     *
     * @return string
     */
    public function namespace()
    {
        $composer = json_decode(file_get_contents(base_path($this->path.'/composer.json')), true);

        $namespace = array_keys(array_get($composer['autoload'], 'psr-4'))[0];

        return rtrim($namespace, '\\');
    }

    /**
     * Parse droplet name from composer config
     *
     * @return string
     */
    public function name()
    {
        $composer = json_decode(file_get_contents(base_path($this->path.'/composer.json')), true);

        return studly_case(str_replace('-', '_', explode('/', $composer['name'])[1]));
    }

    /**
     * Set droplet slug
     *
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
     * Set droplet path
     *
     * @param string $path
     *
     * @return Installer
     */
    public function path($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Set parent command
     *
     * @param \Illuminate\Console\Command $command
     */
    public function setCommand(Command $command)
    {
        $this->command = $command;

        return $this;
    }
}