<?php

namespace SuperV\Platform\Domains\Routing;

use Hub;

class Router
{
    /**
     * @var \SuperV\Platform\Domains\Routing\RouteRegistrar
     */
    protected $registrar;

    /** @var */
    protected $files;

    /**
     * File name for catch-all port
     *
     * @var string
     */
    protected $wildcard = 'all-ports|global';

    public function __construct(RouteRegistrar $loader)
    {
        $this->registrar = $loader;
    }

    public function portFilesIn($path, $port = null)
    {
//        dump($path);
//        $path = sv_real_path($path); //disabled due to problems on windows

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
            if (in_array($port, explode('|', $this->wildcard))) {
                $this->registrar->globally(true);
            } elseif (! $port = Hub::get($port)) {
                continue;
            } else {
                $this->registrar->globally(false);
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

    /** @return static */
    public static function resolve()
    {
        return app(static::class);
    }
}
