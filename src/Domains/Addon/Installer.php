<?php

namespace SuperV\Platform\Domains\Addon;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Kernel;
use RuntimeException;
use SuperV\Platform\Domains\Addon\Events\AddonInstalledEvent;
use SuperV\Platform\Domains\Addon\Jobs\MakeAddonModel;
use SuperV\Platform\Domains\Addon\Jobs\SeedAddon;
use SuperV\Platform\Exceptions\PathNotFoundException;
use SuperV\Platform\Exceptions\PlatformException;
use SuperV\Platform\Exceptions\ValidationException;
use SuperV\Platform\Support\Concerns\HasPath;

class Installer
{
    use HasPath;

    protected $name;

    protected $namespace;

    protected $path;

    /** @var \Illuminate\Contracts\Console\Kernel */
    protected $console;

    /** @var \Illuminate\Console\Command */
    protected $command;

    /** @var \SuperV\Platform\Domains\Addon\Addon */
    protected $addon;

    /** @var array * */
    protected $composerJson;

    /** @var \SuperV\Platform\Domains\Addon\Features\MakeAddonRequest */
    protected $request;

    protected $addonType = 'addon';

    protected $vendor;

    protected $identifier;

    public function __construct(Kernel $console)
    {
        $this->console = $console;
    }

    /**
     * Install addon
     *
     * @throws \SuperV\Platform\Exceptions\PathNotFoundException
     */
    public function install()
    {
        $this->ensureNotInstalledBefore();

//        if (! preg_match('/^([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)$/', $this->namespace)) {
//            throw new \Exception('Identifier should be in this format: {vendor}.{package}: '.$this->namespace);
//        }

//        list($this->vendor, $pluralType) = explode('.', $this->namespace);


        if (! $this->path) {
            $this->path = \Platform::config('addons.location').sprintf("/%s/%s/%s", $this->vendor, str_plural($this->addonType), $this->name);
        }

        $this->validatePath();

//        if ($this->locator) {
//            $this->path = $this->locator->locate($this->namespace, $this->getAddonType());
//        }

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
        if ($addon = AddonModel::byIdentifier($this->getName(), $this->getNamespace())) {
            throw new RuntimeException(sprintf("Addon already installed: [%s]", $this->getName()));
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

    public function setAddonType($addonType)
    {
        $this->addonType = $addonType;

        return $this;
    }
//
//    public function vendor()
//    {
//        list($vendor,) = explode('/', $this->identifier);
//
//        return $vendor;
//    }

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

    public function getName()
    {
        return $this->name;
    }

    /**
     * Set addon namespace
     *
     * @param string $name
     * @return Installer
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param mixed $namespace
     */
    public function setNamespace($namespace): Installer
    {
        $this->namespace = $namespace;

        return $this;
    }

    public function setIdentifier($identifer)
    {
        $this->identifier = $identifer;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function parseFromIdentifier($identifier)
    {
        try {
            list($this->vendor, $pluralType, $this->name) = explode('.', $identifier);
            $this->addonType = str_singular($pluralType);

            $this->identifier = $identifier;
        } catch (Exception $e) {
            PlatformException::fail(sprintf("Can not parse from identifier [%s]: ", $identifier, $e->getMessage()));
        }

        return $this;
    }

    protected function getVendor()
    {
        return $this->vendor;
    }

    public function setVendor($vendor)
    {
        $this->vendor = $vendor;

        return $this;
    }

    protected function make()
    {
        try {
            $maker = new MakeAddonModel(
                $this->getVendor(),
                $this->getName(),
                $this->getAddonType()
            );

            $maker->setIdentifier($this->getIdentifier());

            $entry = $maker->make();

            $entry->fill([
                'path'          => $this->relativePath(),
                'psr_namespace' => $this->getPsrNamespace(),
                'enabled'       => true,
            ]);

            $entry->save();
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
            foreach ($subAddons as $identifier => $path) {
                /** @var \SuperV\Platform\Domains\Addon\Installer $installer */
                $installer = app(self::class);
                $installer->parseFromIdentifier($identifier)
                          ->setPath($this->path.'/'.$path)
                          ->install();
            }
        }
    }

    /**
     * Load composer config from addon path
     *
     * @return array|string
     */
    public function getComposerJson()
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

    public static function resolve(): Installer
    {
        return app(static::class);
    }
}
