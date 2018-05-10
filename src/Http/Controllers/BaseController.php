<?php

namespace SuperV\Platform\Http\Controllers;

use Illuminate\Events\Dispatcher;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\Route;
use Illuminate\Support\MessageBag;
use Illuminate\View\Factory;
use SuperV\Platform\Domains\Feature\FeatureBus;

class BaseController extends Controller
{
    use DispatchesJobs;

    /** @var Request */
    protected $request;

    /** @var Route */
    protected $route;

    /** @var Redirector */
    protected $redirect;

    /** @var Factory */
    protected $view;

    /** @var Dispatcher */
    protected $events;

    /** @var MessageBag */
    protected $messages;

    public function __construct()
    {
        $this->request = app('Illuminate\Http\Request');
        $this->redirect = app('Illuminate\Routing\Redirector');
        $this->view = app('Illuminate\Contracts\View\Factory');
        $this->events = app('Illuminate\Contracts\Events\Dispatcher');
        $this->messages = app('Illuminate\Support\MessageBag');
        $this->route = $this->request->route();
    }

    public static function at($method)
    {
        return get_called_class().'@'.$method;
    }

    protected function bus()
    {
        return app(FeatureBus::class);
    }
}