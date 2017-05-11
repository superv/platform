<?php namespace SuperV\Platform\Domains\Feature;

use ReflectionClass;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

trait JobDispatcherTrait
{
    /**
     * beautifier function to be called instead of the
     * laravel function dispatchFromArray.
     * When the $arguments is an instance of Request
     * it will call dispatchFrom instead.
     *
     * @param string                         $job
     * @param array|\Illuminate\Http\Request $arguments
     * @param array                          $extra
     *
     * @return mixed
     */
    public function run($job, $arguments = [], $extra = [])
    {
        if ($arguments instanceof Request) {
            $result = $this->dispatch($this->marshal($job, $arguments, $extra));
        } else {
            if (!is_object($job)) {
                $job = $this->marshal($job, new Collection(), $arguments);
            }
            

            $result = $this->dispatch($job);
        }

        return $result;
    }

    /**
     * Run the given job in the given queue.
     *
     * @param string     $job
     * @param array      $arguments
     * @param Queue|null $queue
     *
     * @return mixed
     */
    public function runInQueue($job, array $arguments = [], $queue = 'default')
    {
        // instantiate and queue the job
        $reflection = new ReflectionClass($job);
        $jobInstance = $reflection->newInstanceArgs($arguments);
        $jobInstance->onQueue((string) $queue);

        return $this->dispatch($jobInstance);
    }
}
