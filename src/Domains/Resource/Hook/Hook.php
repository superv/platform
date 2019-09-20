<?php

namespace SuperV\Platform\Domains\Resource\Hook;

use Log;
use SuperV\Platform\Contracts\Dispatcher;
use SuperV\Platform\Exceptions\PlatformException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Hook
{
    protected $map = [];

    /**
     * @var \SuperV\Platform\Contracts\Dispatcher
     */
    protected $dispatcher;

    protected static $locks = [];

    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function flush()
    {
        $this->map = [];

        static::$locks = [];
    }

    /**
     * Return mapped hook path for the identifier
     *
     * @param      $identifier
     * @param null $key
     * @return string|null
     */
    public function get($identifier, $key = null)
    {
        if (! isset($this->map[$identifier])) {
            return null;
        }

        if ($key) {
            return $this->map[$identifier][$key];
        }

        return $this->map[$identifier];
    }

    /**
     * Scan path for hooks
     *
     * @param $path
     */
    public function scan($path): Hook
    {
        if (! file_exists($path)) {
            PlatformException::runtime(sprintf("Path not found: %s", $path));
        }

        /** @var SplFileInfo $file */
        foreach ((new Finder)->in($path)->files() as $file) {
            if (! $namespace = get_ns_from_file($file->getPathname())) {
                continue;
            }

            $className = str_replace('.php', '', $file->getFilename());
            $hookHandler = $namespace.'\\'.$className;

            if (! ($identifier = isset($hookHandler::$identifier) ? $hookHandler::$identifier : null)) {
                continue;
            }

            $this->register($identifier, $hookHandler, $className);
        }

        return $this;
    }

    public function register($identifier, $hookHandler, $className)
    {
        $_identifier = sv_identifier($identifier);

        $subKey = null;

//        $parts = explode('.', $identifier);
//        if (count($parts) > 2) {
//            $identifier = sprintf('%s.%s', $parts[0], $parts[1]);
//
//            if (str_contains($parts[2], ':')) {
//                list($hookType, $subKey) = explode(':', $parts[2]);
//            } else {
//                $hookType = $parts[2];
//            }
//        } else {
//            $parts = explode('_', snake_case($className));
//            $hookType = end($parts);
//        }

        if ($_identifier->getNodeCount() > 2) {
            $hookType = (string)$_identifier->getType();
            $subKey = $_identifier->getTypeId();
            $identifier = $_identifier->getParent();
        } else {
            $parts = explode('_', snake_case($className));
            $hookType = end($parts);
            $subKey2 = null;
        }

//        if ($subKey !== $subKey2 || $hookType !== $hookType2) {
//            dd($identifier, $subKey, $subKey2);
//        }
//

        if (! isset($this->map[$identifier])) {
            $this->map[$identifier] = [];
        }

        if (! $subKey) {
            $this->map[$identifier][$hookType] = $hookHandler;
        } else {
            if (! isset($this->map[$identifier][$hookType][$subKey])) {
                $this->map[$identifier][$hookType][$subKey] = [];
            }

            $this->map[$identifier][$hookType][$subKey] = $hookHandler;
        }

        $this->hookType($identifier, $hookType, $hookHandler, $subKey);

        return $this;
    }

    public function hookType($identifier, $type, $hookHandler, $subKey = null)
    {
        if (! class_exists($hookHandler)) {
            return Log::error("Hook handler class does not exist: ".$hookHandler);
        }
        $typeHook = str_replace_last("\\Hook", "\\".studly_case($type.'_hook'), get_class($this));
        if (class_exists($typeHook)) {
            app($typeHook)->hook($identifier, $hookHandler, $subKey);
        }
    }

    /** @return static */
    public static function resolve()
    {
        return app(static::class);
    }
}
