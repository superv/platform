<?php

namespace SuperV\Platform\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Route;

class BaseController extends Controller
{
    use DispatchesJobs;

    /** @var Request */
    protected $request;

    /** @var Route */
    protected $route;

    /** @var \Illuminate\Contracts\Events\Dispatcher */
    protected $events;

    public function __construct()
    {
        $this->request = app('request');
        $this->events = app('events');
        $this->route = $this->request->route();
    }

    public static function at($method)
    {
        return get_called_class().'@'.$method;
    }
}
