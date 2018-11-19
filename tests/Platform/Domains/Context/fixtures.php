<?php

namespace Tests\Platform\Domains\Context;

interface AcceptsPlane
{
    public function acceptPlane($plane);
}

interface AcceptsMoney
{
    public function acceptMoney($money);
}

interface ProvidesPlane
{
    public function providePlane();
}

interface ProvidesMoney
{
    public function provideMoney();
}

interface ProvidesRoute
{
    public function provideRoute(string $name);
}

interface AcceptsRouteProvider
{
    public function acceptRouteProvider(ProvidesRoute $routeProvider);
}

class SolidRouteProviderAcceptor implements AcceptsRouteProvider
{
    /**
     * @var string
     */
    protected $name;

    protected $routeUrl;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function acceptRouteProvider(ProvidesRoute $routeProvider)
    {
        $this->routeUrl = $routeProvider->provideRoute($this->name);
    }

    public function getRouteUrl()
    {
        return $this->routeUrl;
    }
}

class SolidRouteProvider implements ProvidesRoute
{
    public function provideRoute(string $name)
    {
        return 'sv/routes/'.$name;
    }
}

class SolidAcceptor implements AcceptsPlane, ProvidesMoney
{
    public $plane;

    public $money;

    public function acceptPlane($plane)
    {
        $this->plane = $plane;
    }

    public function provideMoney()
    {
        return $this->money;
    }
}

class SolidProvider implements ProvidesPlane, AcceptsMoney
{
    public $money;

    public $plane;

    public function providePlane()
    {
        return $this->plane;
    }

    public function acceptMoney($money)
    {
        $this->money = $money;
    }
}
