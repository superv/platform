<?php

namespace Tests\Platform;

use Composer\Autoload\ClassLoader;

class ComposerLoader
{
    /** @var ClassLoader */
    protected $loader;
    protected $composer;

    public function __construct()
    {
        foreach (spl_autoload_functions() as $loader) {
            if ($loader[0] instanceof ClassLoader) {
                $this->loader = $loader[0];
            }
        }

        if (!$this->loader) {
            throw new \Exception("The ClassLoader could not be found.");
        }
    }

    public static function load($path)
    {
        return (new self())->loadFromPath($path);
    }

    public function getComposerJson($path)
    {
        if (!file_exists($path . '/composer.json')) {
            throw new \Exception("Composer file does not exist at {$path}/composer.json");
        }

        if (!$composer = json_decode(file_get_contents($path . '/composer.json'), true)) {
            throw new \Exception("A JSON syntax error was encountered in {$path}/composer.json");
        }


        if (!array_key_exists('autoload', $composer)) {
            return false;
        }

        return $composer;
    }

    public function loadFromPath($path)
    {
        $composer = $this->getComposerJson($path);

        if (!array_key_exists('autoload', $composer)) {
            return null;
        }

        $this->loadComposer($composer, $path);

        $this->loader->register();
    }

    public function loadComposer(array $composer, $path)
    {
        foreach (array_get($composer['autoload'], 'psr-4', []) as $namespace => $autoload) {
            $this->loader->addPsr4($namespace, $path . '/' . $autoload, false);
        }
    }
}