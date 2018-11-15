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

class NegotiatorTest extends TestCase
{
    function test__handshakes_requirer_with_provider()
    {
        $provider = new ConcreteProvider;
        $requirer = new ConcreteRequirer;

        $this->assertNull($requirer->plane);
        $this->assertNull($provider->money);

        (new Negotiator())->handshake([
            $provider,
            $requirer,
        ]);

        $this->assertEquals('Boink 404', $requirer->plane);
        $this->assertEquals('123', $provider->money);
    }
}

class ConcreteRequirer implements AcceptsPlane, ProvidesMoney
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

class ConcreteProvider implements ProvidesPlane, AcceptsMoney
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



