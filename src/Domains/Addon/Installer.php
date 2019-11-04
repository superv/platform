<?php

namespace SuperV\Platform\Domains\Addon;

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

    /**
     * @var \SuperV\Platform\Domains\Addon\AddonModel
     */
    protected $addonEntry;

    protected $path;

    /** @var \Illuminate\Contracts\Console\Kernel */
    protected $console;

    /** @var \Illuminate\Console\Command */
    protected $command;

    /** @var \SuperV\Platform\Domains\Addon\Addon */
    protected $addon;

    protected $addonType = 'addon';

    protected $identifier;

    public function __construct(Kernel $console)
    {
        $this->console = $console;
    }

    /**
     * Install addon
     *
     * @return \SuperV\Platform\Domains\Addon\Installer
     * @throws \SuperV\Platform\Exceptions\PathNotFoundException
     */
    public function install()
    {
        $this->validatePath();

        $maker = new MakeAddonModel(
            $this->getIdentifier(),
            $this->getAddonType()
        );

        $this->addonEntry = $maker->make();

//        dd($this->addonEntry->toArray());

        $this->addonEntry->fill([
            'identifier' => $this->addonEntry->getName(),
            'path'       => $this->relativePath(),
            'enabled'    => true,
        ]);

        $this->ensureNotInstalledBefore();

        try {
            $this->addonEntry->save();
        } catch (ValidationException $e) {
            PlatformException::fail(sprintf("Could not create addon model [%s]", $e->getErrorsAsString()));
        }

        $this->addon = $this->addonEntry->resolveAddon();

        $this->register($this->addon);

        $this->migrate($this->addon);

        AddonInstalledEvent::dispatch($this->addon);

        return $this;
    }

    public function validatePath()
    {
        if (! $this->path) {
            throw new \InvalidArgumentException("Path can not be empty");
        }

        if (! $this->relativePath()) {
            throw new \InvalidArgumentException("Can not determine relative path");
        }

        if (! file_exists($this->realPath())) {
            throw new PathNotFoundException(sprintf("Path does not exist: [%s]", $this->path));
        }
    }

    public function seed()
    {
        optional($this->command)->comment('Seeding Addon ['.$this->addon->getIdentifier().']');

        SeedAddon::dispatch($this->addon);
    }

    public function ensureNotInstalledBefore()
    {
        if ($addon = AddonModel::byIdentifier($this->getIdentifier())) {
            throw new RuntimeException(sprintf("Addon already installed: [%s]", $this->getIdentifier()));
        }
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

    /**
     * Set addon path
     *
     * @param string $path
     * @return Installer
     */
    public function setPath($path)
    {
        if (! starts_with($path, '/')) {
            $path = base_path($path);
        }

        $this->path = $path;

        $this->validatePath();

        if (! $composer = get_json($path, 'composer')) {
            throw new \Exception(" Can not get composer from path:: ".$path);
        }
        $name = array_get($composer, 'name');
        if (! $name) {
            throw new \Exception('Name parameter in composer.json not found');
        }

        if (! preg_match('/^([a-zA-Z0-9_]+)\/([a-zA-Z0-9_\-]+)$/', $name)) {
            throw new \Exception('Name parameter in composer.json should be formatted like: {vendor}/{package}: '.$name);
        }
        list($vendor, $addonName) = explode('/', $name);

        $type = array_get($composer, 'type');
        if (! $type || ! preg_match('/^superv-([a-zA-Z]+)$/', $type)) {
            throw new \Exception('Type parameter in composer.json should be formatted like: superv-{type}: '.$type);
        }

        list(, $this->addonType) = explode('-', $type);

        $this->identifier = sprintf('%s.%s', $vendor, $addonName);



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

    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    public static function resolve(): Installer
    {
        return app(static::class);
    }

    /**
     * Register addon service provider
     *
     * @param \SuperV\Platform\Domains\Addon\Addon $addon
     */
    protected function register(Addon $addon)
    {
        app()->register($addon->resolveProvider());
    }

    /**
     * Migrate addon migrations
     *
     * @param \SuperV\Platform\Domains\Addon\Addon $addon
     */
    protected function migrate(Addon $addon)
    {
        $this->console->call(
            'migrate',
            ['--namespace' => $addon->getIdentifier(), '--force' => true],
            $this->command ? $this->command->getOutput() : null
        );
    }

    /**
     * Install sub addons
     */
    protected function installSubAddons()
    {
//        if ($subAddons = $this->addon->installs()) {
//            foreach ($subAddons as $identifier => $path) {
//                /** @var \SuperV\Platform\Domains\Addon\Installer $installer */
//                $installer = app(self::class);
//                $installer->parseFromIdentifier($identifier)
//                          ->setPath($this->path.'/'.$path)
//                          ->install();
//            }
//        }
    }
}
