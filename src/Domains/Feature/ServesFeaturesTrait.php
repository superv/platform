<?php namespace SuperV\Platform\Domains\Feature;

use ArrayAccess;
use Exception;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionParameter;

trait ServesFeaturesTrait
{
    use DispatchesJobs;

    /**
     * Serve the given feature with the given arguments.
     *
     * @param \SuperV\Platform\Domains\Feature\Feature|string $feature
     * @param array                                           $arguments
     *
     * @return mixed
     */
    public function serve($feature, $arguments = [])
    {
        if (!is_object($feature)) {
            $feature = $this->marshal($feature, new Collection(), $arguments);
        }

        if ($middlewares = $feature->getMiddlewares()) {
            foreach($middlewares as $middleware) {
                $this->dispatch(superv($middleware));
            }
        }

        return $this->dispatch($feature);
    }

    /**
     * Marshal a command from the given array accessible object.
     *
     * @param string       $command
     * @param \ArrayAccess $source
     * @param array        $extras
     *
     * @return mixed
     */
    protected function marshal($command, ArrayAccess $source, array $extras = [])
    {
        $injected = [];

        $reflection = new ReflectionClass($command);

        if ($constructor = $reflection->getConstructor()) {
            $injected = array_map(function ($parameter) use ($command, $source, $extras) {
                return $this->getParameterValueForCommand($command, $source, $parameter, $extras);
            }, $constructor->getParameters());
        }

        return $reflection->newInstanceArgs($injected);
    }

    /**
     * Get a parameter value for a marshaled command.
     *
     * @param string               $command
     * @param \ArrayAccess         $source
     * @param \ReflectionParameter $parameter
     * @param array                $extras
     *
     * @return mixed
     * @throws Exception
     */
    protected function getParameterValueForCommand(
        $command,
        ArrayAccess $source,
        ReflectionParameter $parameter,
        array $extras = []
    ) {
        if (array_key_exists($parameter->name, $extras)) {
            return $extras[$parameter->name];
        }

        if (isset($source[$parameter->name])) {
            return $source[$parameter->name];
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new Exception("Unable to map parameter [{$parameter->name}] to command [{$command}]");
    }

    public function runInQueue($job, array $arguments = [], $queue = 'default')
    {
        // instantiate and queue the job
        $reflection = new ReflectionClass($job);
        $jobInstance = $reflection->newInstanceArgs($arguments);
        $jobInstance->onQueue((string) $queue);

        return $this->dispatch($jobInstance);
    }
}
