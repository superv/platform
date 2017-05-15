<?php namespace SuperV\Platform\Adapters;

use SuperV\Platform\Contracts\Dispatcher;

class LaravelDispatcher implements Dispatcher
{
    /**
     * @var \Illuminate\Events\Dispatcher
     */
    protected $dispatcher;
    
    public function __construct(\Illuminate\Events\Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }
    
    public function dispatch($event, $payload = [])
    {
        $this->dispatcher->fire($event, $payload);
    }
}
