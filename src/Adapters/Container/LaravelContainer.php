<?php namespace SuperV\Platform\Adapters\Container;

use Illuminate\Contracts\Foundation\Application;
use SuperV\Platform\Contracts\Container;

class LaravelContainer implements Container
{
    /**
     * @var Application
     */
    protected $app;
    
    public function __construct(Application $app)
    {
        $this->app = $app;
    }
    
    public function make($abstract)
    {
        return $this->app->make($abstract);
    }
}
