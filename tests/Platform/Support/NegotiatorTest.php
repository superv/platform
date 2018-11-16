<?php

namespace Tests\Platform\Support;

use SuperV\Platform\Support\Negotiator\Negotiator;
use Tests\Platform\TestCase;

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

class NegotiatorTest extends TestCase
{
    function test__gets_value_from_provider_sets_to_acceptor()
    {
        $provider = new SolidProvider;
        $acceptor = new SolidAcceptor;

        $this->assertNull($acceptor->plane);
        $this->assertNull($provider->money);

        Negotiator::deal($provider, $acceptor);

        $this->assertEquals('Boink 404', $acceptor->plane);
        $this->assertEquals('123', $provider->money);
    }

    function test__set_provider_to_provider_acceptor()
    {
        $provider = new SolidRouteProvider;
        $acceptor = new SolidRouteProviderAcceptor('edit');
        $this->assertNull($acceptor->getRouteUrl());

        Negotiator::deal($provider, $acceptor);

        $this->assertEquals($provider->provideRoute('edit'), $acceptor->getRouteUrl());

    }
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

    public function acceptPlane($plane)
    {
        $this->plane = $plane;
    }

    public function provideMoney()
    {
        return '123';
    }
}

class SolidProvider implements ProvidesPlane, AcceptsMoney
{
    public $money;

    public function providePlane()
    {
        return 'Boink 404';
    }

    public function acceptMoney($money)
    {
        $this->money = $money;
    }
}



