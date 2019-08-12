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
    protected $baseNamespace;

    public function __construct(string $path, string $baseNamespace)
    {
        $this->path = $path;
        $this->baseNamespace = $baseNamespace;
    }

    public function handle(Finder $finder)
    {
        if (! file_exists($this->path)) {
            return;
        }

        /** @var SplFileInfo $directory */
        foreach ($finder->in($this->path)->directories()  as $directory) {
            $class = Path::parseClass($this->baseNamespace, $this->path, $directory);
            Hook::register(snake_case($directory->getBasename()), $class);
        }

    }
}