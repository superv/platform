<?php namespace SuperV\Platform\Domains\Droplet\Feature;

use Composer\Autoload\ClassLoader;
use SuperV\Platform\Domains\Droplet\Jobs\GetComposerConfig;
use SuperV\Platform\Domains\Feature\Feature;

class LoadDroplet extends Feature
{
    /** @var ClassLoader */
    protected $loader;

    protected $composer;

    /**
     * @var
     */
    private $path;

    public function __construct($path)
    {
        $this->path = $path;

        foreach (spl_autoload_functions() as $loader) {
            if ($loader instanceof \Closure) {
                continue;
            }
            if ($loader[0] instanceof ClassLoader) {
                $this->loader = $loader[0];
            }
        }

        if (!$this->loader) {
            throw new \Exception("The ClassLoader could not be found.");
        }
    }

    public function handle()
    {
        $composer = \Cache::remember('composer@'.md5($this->path), 60, function(){
            return $this->dispatch(new GetComposerConfig($this->path));
        });

        if (!array_key_exists('autoload', $composer)) {
            return null;
        }

        foreach (array_get($composer['autoload'], 'psr-4', []) as $namespace => $autoload) {
            $this->loader->addPsr4($namespace, $this->path . '/' . $autoload, false);
        }

        $this->loader->register();
    }
}