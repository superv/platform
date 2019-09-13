<?php

namespace SuperV\Platform\Domains\Addon;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Kernel;
use RuntimeException;
use SuperV\Platform\Domains\Addon\Contracts\AddonLocator;
use SuperV\Platform\Domains\Addon\Events\AddonInstalledEvent;
use SuperV\Platform\Domains\Addon\Jobs\SeedAddon;
use SuperV\Platform\Exceptions\PathNotFoundException;
use SuperV\Platform\Exceptions\PlatformException;
use SuperV\Platform\Exceptions\ValidationException;
use SuperV\Platform\Support\Concerns\HasPath;

class Installer
{
    use HasPath;

    protected $identifier;

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

    protected $addonType;

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

        if (! preg_match('/^([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)$/', $this->identifier)) {
            throw new \Exception('Identifier should be in this format: {vendor}.{package}: '.$this->identifier);
        }

        $this->validatePath();

        if ($this->locator) {
            $this->path = $this->locator->locate($this->identifier, $this->getAddonType());
        }

        $this->getComposerJson();

//        $this->determineAddonType();

        $this->make();

        $this->register();

        $this->migrate();

        $this->installSubAddons();

        AddonInstalledEvent::dispatch($this->addon);

        return $this;
    }

    public function determineAddonType()
    {
        if ($this->addonType) {
            return;
        }
        if (! $type = array_get($this->composerJson, 'type')) {
            throw new \Exception('Composer type not provided in composer.json');
        }

        $this->addonType = explode('-', $type)[1];

        return $this;
    }

    public function validatePath()
    {
        $realPath = $this->realPath();

        if (! $realPath) {
            throw new \InvalidArgumentException("Path can not be empty");
        }

        if (! file_exists($realPath)) {
            throw new PathNotFoundException("Path does not exist: [{$realPath}]");
        }
    }

    public function seed()
    {
        SeedAddon::dispatch($this->addon);
    }

    public function ensureNotInstalledBefore()
    {
        if ($addon = AddonModel::byIdentifier($this->getIdentifier())) {
            throw new RuntimeException(sprintf("Addon already installed: [%s]", $this->getIdentifier()));
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

    public function getAddonType()
    {
        return $this->addonType;
    }
//
//    public function vendor()
//    {
//        list($vendor,) = explode('/', $this->identifier);
//
//        return $vendor;
//    }

    public function setAddonType($addonType)
    {
        $this->addonType = $addonType;

        return $this;
    }

    /**
     * Parse PHP Namespace from composer config
     *
     * @return string
     */
    public function getPsrNamespace()
    {
        $namespace = array_keys($this->getComposerValue('autoload.psr-4'))[0];

        return rtrim($namespace, '\\');
    }

    /**
     * Parse addon name from composer config
     *
     * @return string
     */
    public function name()
    {
        return studly_case(str_replace('-', ' ', explode('/', $this->getComposerValue('name'))[1]));
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

    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Set addon namespace
     *
     * @param string $identifier
     * @return Installer
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    public static function resolve(): Installer
    {
        return app(static::class);
    }

    protected function make()
    {
        list($vendor, $package) = array_map(
            function ($value) {
                return strtolower($value);
            },
            explode('.', $this->identifier)
        );

        try {
            $entry = AddonModel::query()->create([
                'name'          => str_unslug(studly_case($package).'_'.$this->addonType),
                'vendor'        => $vendor,
                'package'       => $package,
                'identifier'    => $this->identifier,
                'path'          => $this->relativePath(),
                'type'          => $this->getAddonType(),
                'psr_namespace' => $this->getPsrNamespace(),
                'enabled'       => true,
            ]);
        } catch (ValidationException $e) {
            PlatformException::fail(sprintf("Could not create addon model [%s]", $e->getErrorsAsString()));
        }

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
            ['--namespace' => $this->addon->getIdentifier(), '--force' => true],
            $this->command ? $this->command->getOutput() : null
        );
    }

    /**
     * Install sub addons
     */
    protected function installSubAddons()
    {
        if ($subAddons = $this->addon->installs()) {
            foreach ($subAddons as $identifier => $subAddon) {
                /** @var \SuperV\Platform\Domains\Addon\Installer $installer */
                $installer = app(self::class);
                $installer->setIdentifier($identifier)
                          ->setPath($this->path.'/'.$subAddon['path'])
                          ->setAddonType($subAddon['type'])
                          ->install();
            }
        }
    }

    /**
     * Load composer config from addon path
     *
     * @return array|string
     */
    protected function getComposerJson()
    {
        if (! $this->composerJson) {
            $composerFile = $this->realPath().'/composer.json';
            if (! file_exists($composerFile)) {
                return null;
            }

            $this->composerJson = json_decode(file_get_contents($composerFile), true);
        }

        return $this->composerJson;
    }

    protected function getComposerValue($key)
    {
        return array_get($this->getComposerJson(), $key);
    }
}
