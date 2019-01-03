<?php

namespace SuperV\Platform\Domains\Addon;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Kernel;
use RuntimeException;
use SuperV\Platform\Domains\Addon\Contracts\AddonLocator;
use SuperV\Platform\Domains\Addon\Events\AddonInstalledEvent;
use SuperV\Platform\Exceptions\PathNotFoundException;
use SuperV\Platform\Support\Concerns\HasPath;

class Installer
{
    use HasPath;

    protected $slug;

    protected $path;

    /** @var \Illuminate\Contracts\Console\Kernel */
    protected $console;

    /** @var \Illuminate\Console\Command */
    protected $command;

    /** @var \SuperV\Platform\Domains\Addon\Addon */
    protected $addon;

    /** @var array * */
    protected $composerJson;

    /** @var AddonLocator */
    protected $locator;

    public function __construct(Kernel $console)
    {
        $this->console = $console;
    }

    public function setLocator(AddonLocator $locator)
    {
        $this->locator = $locator;

        return $this;
    }

    /**
     * Install addon
     *
     * @throws \SuperV\Platform\Exceptions\PathNotFoundException
     */
    public function install()
    {
        $this->ensureNotInstalledBefore();

        if ($this->locator) {
            $this->path = $this->locator->locate($this->slug);
        }

        $this->validate();

        $this->make();

        $this->register();

        $this->migrate();

        $this->installSubAddons();

        AddonInstalledEvent::dispatch($this->addon);

        return $this;
    }

    public function ensureNotInstalledBefore()
    {
        if ($addon = AddonModel::bySlug($this->getSlug())) {
            throw new RuntimeException(sprintf("Addon already installed: [%s]", $this->getSlug()));
        }
    }

    /**
     * Return the installed addon
     *
     * @return \SuperV\Platform\Domains\Addon\Addon
     */
    public function getAddon()
    {
        return $this->addon;
    }

    /**
     * Parse addon type from composer config
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
     * Parse addon name from composer config
     *
     * @return string
     */
    public function name()
    {
        return studly_case(str_replace('-', '_', explode('/', $this->composer('name'))[1]));
    }

    /**
     * Set addon slug
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
     * Set addon path
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
     * Validate addon parameters
     */
    protected function validate()
    {
        $realPath = $this->realPath();

        if (! $realPath) {
            throw new \InvalidArgumentException("Path can not be empty");
        }

        if (! file_exists($realPath)) {
            throw new PathNotFoundException("Path does not exist: [{$realPath}]");
        }

        if (! $this->composer('type')) {
            throw new \Exception('Composer type not provided in composer.json');
        }

        if (! str_is('*.*.*', $this->slug)) {
            throw new \Exception('Slug should be snake case and formatted like: {vendor}.{type}.{name}');
        }
    }

    /**
     * Make addon entry
     */
    protected function make()
    {
        $entry = AddonModel::query()->create([
            'name'      => $this->name(),
            'vendor'    => $this->vendor(),
            'slug'      => $this->slug,
            'path'      => $this->relativePath(),
            'type'      => $this->type(),
            'namespace' => $this->namespace(),
            'enabled'   => true,
        ]);

        /** @var \SuperV\Platform\Domains\Addon\AddonModel $entry */
        $this->addon = $entry->resolveAddon();
    }

    /**
     * Register addon service provider
     */
    protected function register()
    {
        app()->register($this->addon->resolveProvider());
    }

    /**
     * Migrate addon migrations
     */
    protected function migrate()
    {
        $this->console->call(
            'migrate',
            ['--scope' => $this->addon->slug(), '--force' => true],
            $this->command ? $this->command->getOutput() : null
        );
    }

    /**
     * Install sub addons
     */
    protected function installSubAddons()
    {
        if ($subAddons = $this->addon->installs()) {
            foreach ($subAddons as $slug => $path) {
                app(self::class)->setSlug($slug)
                                ->setPath($this->path.'/'.$path)
                                ->install();
            }
        }
    }

    /**
     * Load composer config from addon path
     *
     * @param null $key
     * @return array|string
     */
    protected function composer($key = null)
    {
        if (! $this->composerJson) {
            $composerFile = $this->realPath().'/composer.json';
            if (! file_exists($composerFile)) {
                return null;
            }
            $this->composerJson = json_decode(file_get_contents($composerFile), true);
        }

        return $key ? array_get($this->composerJson, $key) : $this->composerJson;
    }

    public function getSlug()
    {
        return $this->slug;
    }
}