<?php

namespace Tests\Platform\Support;

use SuperV\Platform\Support\Negotiator\Negotiator;
use SuperV\Platform\Support\Negotiator\Providing;
use SuperV\Platform\Support\Negotiator\Requirement;
use Tests\Platform\TestCase;

interface RequiresPlane extends Requirement
{
    public function setPlane($plane);
}

interface RequiresMoney extends Requirement
{
    public function setMoney($money);
}

interface ProvidesPlane extends Providing
{
    public function getPlane();
}

interface ProvidesMoney extends Providing
{
    public function getMoney();
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

class ConcreteRequirer implements RequiresPlane, ProvidesMoney
{
    public $plane;

    public function setPlane($plane)
    {
        $this->plane = $plane;
    }

    public function getMoney()
    {
        return '123';
    }
}

class ConcreteProvider implements ProvidesPlane, RequiresMoney
{
    public $money;

    public function getProvidings(): array
    {
        return [RequiresPlane::class];
    }

    public function getPlane()
    {
        return 'Boink 404';
    }

    public function setMoney($money)
    {
        $this->money = $money;
    }
}



