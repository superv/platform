<?php namespace SuperV\Platform\Domains\Droplet;

use Composer\Autoload\ClassLoader;
use SuperV\Platform\Domains\Droplet\Data\DropletModel;

class DropletLoader
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
    
    public function locate(DropletModel $model)
    {
        $composer = $this->getComposerJson($model->getPath());
        
        if (!array_key_exists('autoload', $composer)) {
            return false;
        }
        
        foreach (array_get($composer['autoload'], 'psr-4', []) as $namespace => $autoload) {
            if (rtrim($autoload, '/') == 'src') {
                $model->setNamespace(rtrim($namespace, '\\'));
                $model->setType(array_get($composer, 'type', ''));
                $model->setName(array_get($composer, 'name', ''));
                $model->setVendor(array_get($composer, 'vendor', ''));
                
                return true;
            }
        }
        
        return false;
    }
    
    public function getComposerJson($path)
    {
        if (!file_exists($path . '/composer.json')) {
            throw new \Exception("Composer file does not exist at {$path}/composer.json");
        }
        
        if (!$composer = json_decode(file_get_contents($path . '/composer.json'), true)) {
            throw new \Exception("A JSON syntax error was encountered in {$path}/composer.json");
        }
        
        return $composer;
    }
    
    public function load($path)
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