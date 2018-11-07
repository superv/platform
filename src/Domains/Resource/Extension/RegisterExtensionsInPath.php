<?php

namespace SuperV\Platform\Domains\Resource\Extension;

use SuperV\Platform\Support\Dispatchable;
use SuperV\Platform\Support\Path;

class RegisterExtensionsInPath
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

    public function handle()
    {
        if (! file_exists($this->path)) {
            return;
        }

        $searchIn = array_merge(
            glob($this->path.'/*Extension.php'),
            glob($this->path.'/**/*Extension.php')
        );

        /**
         * register resources and navigations
         */
        if (! empty($searchIn)) {
            foreach ($searchIn as $file) {
                $class = Path::parseClass($this->baseNamespace, $this->path, $file);
                Extension::register($class);
            }
        }
    }
}