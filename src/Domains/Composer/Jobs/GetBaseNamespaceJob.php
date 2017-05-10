<?php namespace SuperV\Platform\Domains\Composer\Jobs;

class GetBaseNamespaceJob
{
    /**
     * @var array
     */
    private $composer;
    
    public function __construct(array $composer)
    {
        $this->composer = $composer;
    }
    
    public function handle()
    {
        foreach (array_get($this->composer['autoload'], 'psr-4', []) as $namespace => $autoload) {
            if (rtrim($autoload, '/') == 'src') {
               return rtrim($namespace, "\\");
            }
        }
        
        throw new \Exception('Base namespace was not found in composer autoload');
    }
}