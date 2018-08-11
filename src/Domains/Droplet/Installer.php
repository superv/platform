<?php

namespace SuperV\Platform\Domains\Droplet;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Kernel;
use SuperV\Platform\Domains\Droplet\Contracts\DropletLocator;
use SuperV\Platform\Domains\Droplet\Events\DropletInstalledEvent;
use SuperV\Platform\Exceptions\PathNotFoundException;

class Installer
{
    protected $slug;

    protected $path;

    /** @var \Illuminate\Contracts\Console\Kernel */
    protected $console;

    /** @var \Illuminate\Console\Command */
    protected $command;

    /** @var \SuperV\Platform\Domains\Droplet\Droplet */
    protected $droplet;

    /** @var array * */
    protected $composerJson;

    /** @var DropletLocator */
    protected $locator;

    public function __construct(Kernel $console)
    {
        $this->console = $console;
    }

    public function setLocator(DropletLocator $locator)
    {
        $this->locator = $locator;

        return $this;
    }

    /**
     * Install droplet
     *
     * @throws \SuperV\Platform\Exceptions\PathNotFoundException
     */
    public function install()
    {
        if ($this->locator) {
            $this->path = $this->locator->locate($this->slug);
        }

        $this->validate();

        $this->make();

        $this->register();

        $this->migrate();

        $this->installSubDroplets();

        DropletInstalledEvent::dispatch($this->droplet);

        return $this;
    }

    /**
     * Return the installed droplet
     *
     * @return \SuperV\Platform\Domains\Droplet\Droplet
     */
    public function getDroplet()
    {
        return $this->droplet;
    }

    /**
     * Parse droplet type from composer config
     *
     * @return string
     */
    public function type()
    {
        return explode('-', $this->composer('type'))[1];
    }

    public function vendor()
    {
        list($vendor, ,) = explode('.', $this->slug);

        return $vendor;
    }

    /**
     * Parse PHP Namespace from composer config
     *
     * @return string
     */
    public function namespace()
    {
        $namespace = array_keys($this->composer('autoload.psr-4'))[0];

        return rtrim($namespace, '\\');
    }

    /**
     * Parse droplet name from composer config
     *
     * @return string
     */
    public function name()
    {
        return studly_case(str_replace('-', '_', explode('/', $this->composer('name'))[1]));
    }

    /**
     * Set droplet slug
     *
     * @param string $slug
     * @return Installer
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Set droplet path
     *
     * @param string $path
     * @return Installer
     */
    public function setPath($path)
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

    /**
     * Validate droplet parameters
     */
    protected function validate()
    {
        if (! $this->path) {
            throw new \InvalidArgumentException("Path can not be empty");
        }

        if (! file_exists(base_path($this->path))) {
            throw new PathNotFoundException("Path does not exist: [{$this->path}]");
        }

        if (!$this->composer('type')) {
            throw new \Exception('Composer type not provided in composer.json');
        }

        if (! str_is('*.*.*', $this->slug)) {
            throw new \Exception('Slug should be snake case and formatted like: {vendor}.{type}.{name}');
        }


    }

    /**
     * Make droplet entry
     */
    protected function make()
    {
        $entry = DropletModel::query()->create([
            'name'      => $this->name(),
            'vendor'    => $this->vendor(),
            'slug'      => $this->slug,
            'path'      => $this->path,
            'type'      => $this->type(),
            'namespace' => $this->namespace(),
            'enabled'   => true,
        ]);

        $this->droplet = $entry->resolveDroplet();
    }

    /**
     * Register droplet service provider
     */
    protected function register()
    {
        app()->register($this->droplet->resolveProvider());
    }

    /**
     * Migrate droplet migrations
     */
    protected function migrate()
    {
        $this->console->call(
            'migrate',
            ['--scope' => $this->droplet->slug(), '--force' => true],
            $this->command ? $this->command->getOutput() : null
        );
    }

    /**
     * Install sub droplets
     */
    protected function installSubDroplets()
    {
        if ($subDroplets = $this->droplet->installs()) {
            foreach ($subDroplets as $slug => $path) {
                app(self::class)->setSlug($slug)
                                ->setPath($this->path.'/'.$path)
                                ->install();
            }
        }
    }

    /**
     * Load composer config from droplet path
     *
     * @param null $key
     * @return array|string
     */
    protected function composer($key = null)
    {
        if (! $this->composerJson) {
            $composerFile = base_path($this->path.'/composer.json');
            if (! file_exists($composerFile)) {
                return null;
            }
            $this->composerJson = json_decode(file_get_contents($composerFile), true);
        }

        return $key ? array_get($this->composerJson, $key) : $this->composerJson;
    }
}