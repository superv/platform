<?php

namespace SuperV\Platform\Domains\Resource\Hook;

use SuperV\Platform\Contracts\Dispatcher;
use SuperV\Platform\Domains\Resource\Hook\Contracts\HookByRole;
use SuperV\Platform\Domains\Resource\Hook\Contracts\HookHandlerInterface;
use SuperV\Platform\Exceptions\PlatformException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class HookManager
{
    protected $map = [];

    /**
     * @var \SuperV\Platform\Contracts\Dispatcher
     */
    protected $dispatcher;

    protected $callbacks = [];

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
    public function scan($path)
    {
        if (! file_exists($path)) {
            PlatformException::runtime(sprintf("Path not found: %s", $path));
        }

        $this->callbacks = [];

        /** @var SplFileInfo $file */
        foreach ((new Finder)->in($path)->files() as $file) {
            if (! $namespace = get_ns_from_file($file->getPathname())) {
                continue;
            }

            $className = str_replace('.php', '', $file->getFilename());
            $hookClass = $namespace.'\\'.$className;

            if (! ($identifier = isset($hookClass::$identifier) ? $hookClass::$identifier : null)) {
                sv_console(sprintf("Identifier not found for hook [%s]", $hookClass));
                continue;
            }

            if (! class_exists($hookClass)) {
                return sv_console("Hook handler class does not exist: ".$hookClass);
            }
            if ($this->hasContract($hookClass, HookByRole::class)) {
                $this->callbacks[] = function () use ($className, $hookClass, $identifier) {
                    $this->register($identifier, $hookClass, $className);
                };
            } else {
                $this->register($identifier, $hookClass, $className);
            }
        }

        $this->registerDeferredHooks();
    }

    public function register($identifier, $hookClass, $className)
    {
        [$identifier, $hookType, $subKey] = $this->parseIdentifier($identifier, $hookClass, $className);

        $hookHandler = $this->resolveHookHandler($hookType);

        if ($hookHandler) {
            $hookHandler->hook($identifier, $hookClass, $subKey);
        }

    }

    public function resolveHookHandler($hookType): ?HookHandlerInterface
    {
        $hookHandlerClass = $this->getHookHandlerClass($hookType);
        if (class_exists($hookHandlerClass)) {
            return app($hookHandlerClass);
        }

        return null;
    }

    public function getHookHandlerClass($hookType): string
    {
        $hookHandler = str_replace_last("\\HookManager", "\\".studly_case($hookType.'_hook_handler'), get_class($this));

        return $hookHandler;
    }

    protected function hasContract($class, $contract): bool
    {
        $contracts = class_implements($class);

        return in_array($contract, $contracts);
    }

    /**
     * @param $identifier
     * @param $hookClass
     * @param $className
     * @return array
     */
    protected function parseIdentifier($identifier, $hookClass, $className): array
    {
        $_identifier = sv_identifier($identifier);
        $subKey = null;

        if ($_identifier->getNodeCount() > 2) {
            $hookType = (string)$_identifier->getType();
            $subKey = $_identifier->getTypeId();
            $identifier = $_identifier->getParent();
        } else {
            $parts = explode('_', snake_case($className));
            $last = end($parts);

            if (in_array($last, ['list', 'form', 'observer', 'page'])) {
                $hookType = $last;
            } else {
                $hookType = 'resource';
            }

            if ($this->hasContract($hookClass, HookByRole::class)) {
                $subKey = $hookClass::getRole();
            } else {
                $subKey = null;
            }
        }

        return [$identifier, $hookType, $subKey];
    }

    protected function registerDeferredHooks(): void
    {
        foreach ($this->callbacks as $callback) {
            $callback();
        }
    }

    /** @return static */
    public static function resolve()
    {
        return app(static::class);
    }
}
