<?php

namespace SuperV\Platform\Domains\Resource\Extension;

use SuperV\Platform\Domains\Resource\Hook;
use SuperV\Platform\Support\Dispatchable;
use SuperV\Platform\Support\Path;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class RegisterHooksInPath
{
    use Dispatchable;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $psrNamespace;

    /**
     * @var string
     */
    protected $namespace;

    public function __construct(string $namespace, string $path, string $psrNamespace)
    {
        $this->path = $path;
        $this->psrNamespace = $psrNamespace;
        $this->namespace = $namespace;
    }

    public function handle(Finder $finder)
    {
        if (! file_exists($this->path)) {
            return;
        }

        /** @var SplFileInfo $directory */
        foreach ($finder->in($this->path)->directories()  as $directory) {
            $class = Path::parseClass($this->psrNamespace, $this->path, $directory);
            $identifier = $this->namespace.'::'.snake_case($directory->getBasename());
            Hook::register($identifier, $class);
        }

    }
}
