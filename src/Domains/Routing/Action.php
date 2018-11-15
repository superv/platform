<?php

namespace SuperV\Platform\Domains\Routing;

use Illuminate\Routing\Router;
use SuperV\Platform\Support\Concerns\Hydratable;

class Action
{
    use Hydratable;

    protected $uses;

    protected $as;

    protected $uri;

    protected $verb;

    protected $defaultVerb = 'get';

    /** @var \SuperV\Platform\Domains\Port\Port */
    protected $port;

    protected $domain;

    protected $prefix;

    /** @var array */
    protected $middleware = [];

    public function build()
    {
        if (str_contains($this->uri, '@')) {
            list($this->verb, $this->uri) = explode('@', $this->uri);
        }

        $this->portify();

        return $this;
    }

    protected function portify()
    {
        if (! $this->port) {
            return;
        }

        $this->domain = $this->port->hostname();
        $this->prefix = $this->port->prefix();

        $this->mergeMiddlewares($this->port->middlewares());
    }

    /**
     * @param \SuperV\Platform\Domains\Port\Port $port
     * @return Action
     */
    public function port($port)
    {
        $this->port = $port;

        return $this;
    }

    protected function mergeMiddlewares($middlewares)
    {
        if (! is_array($middlewares)) {
            return;
        }
        $this->middleware = array_merge($middlewares, $this->middleware);
    }

    /**
     * @return mixed
     */
    public function verb()
    {
        return $this->verb ?? $this->defaultVerb;
    }

    public function register(Router $route)
    {
        return $route->{$this->verb()}($this->uri, $this->toArray());
    }

    public function toArray()
    {
        return array_filter([
            'uses'       => $this->uses,
            'as'         => $this->as,
            'uri'        => $this->uri,
            'verb'       => $this->verb,
            'port'       => $this->port ? $this->port->slug() : null,
            'domain'     => $this->domain,
            'prefix'     => $this->prefix,
            'middleware' => $this->middleware,
        ]);
    }

    /** @return static */
    public static function make($uri, $action)
    {
        if (! is_array($action)) {
            $action = ['uses' => $action];
        }

        return (new static)->hydrate(array_set($action, 'uri', $uri));
    }
}