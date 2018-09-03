<?php

namespace SuperV\Platform\Domains\Routing;

use SuperV\Platform\Domains\Port\Port;

class Router
{
    /**
     * @var \SuperV\Platform\Domains\Routing\RouteRegistrar
     */
    protected $registrar;

    /** @var */
    protected $files;

    public function __construct(RouteRegistrar $loader)
    {
        $this->registrar = $loader;
    }

    public function portFilesIn($path, $port = null)
    {
        $path = sv_real_path($path);

        $portFiles = [];

        /** file based routes */
        foreach (glob($path.'/*.php') as $file) {
            $portFiles[sv_filename($file)][] = $file;
        }

        foreach (glob($path.'/*', GLOB_ONLYDIR) as $dir) {
            $port = sv_basename($dir);
            foreach (glob($dir.'/*.php') as $file) {
                $portFiles[$port][] = $file;
            }
        }

        return $portFiles;
    }

    public function loadFromPath($path)
    {

        foreach ($this->portFilesIn($path) as $port => $files) {
            if (! $port = Port::fromSlug($port)) {
                continue;
            }
            $this->registrar->setPort($port);
            foreach ($files as $file) {
                $routes = (array)require $file;
                if (! empty($routes)) {
                    $this->registrar->register($routes);
                }
            }
        }
    }

    public function loadFromFile($file)
    {
        $routes = require base_path($file);
        $this->registrar->register($routes);
    }
}