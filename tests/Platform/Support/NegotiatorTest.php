<?php

namespace Tests\Platform\Support;

use SuperV\Platform\Support\Negotiator\Negotiator;
use SuperV\Platform\Support\Negotiator\Provider;
use SuperV\Platform\Support\Negotiator\Requirer;
use Tests\Platform\TestCase;

class NegotiatorTest extends TestCase
{
    function test__all()
    {
        $negotiator = new Negotiator([RequiresPlane::class => ProvidesPlane::class]);

        $negotiator->handshake(
            $provider = new ConcreteProvider,
            $requirer = new ConcreteRequirer
        );

        $this->assertEquals('Boink 404', $requirer->plane);
    }
}

class ConcreteRequirer implements Requirer, RequiresPlane
{
    public $plane;

    public function getRequirements()
    {
        return [RequiresPlane::class => [$this, 'setPlane']];
    }

    public function setPlane($plane)
    {
        $this->plane = $plane;
    }
}

class ConcreteProvider implements Provider, ProvidesPlane
{
    protected $plane = 'Boink 404';

    public function getProvidings(): array
    {
        return [RequiresPlane::class => [$this, 'getPlane']];
    }

    public function getPlane()
    {
        return $this->plane;
    }
}

interface RequiresPlane
{
    public function setPlane($plane);
}

interface ProvidesPlane
{
    public function getPlane();
}

interface PlaneRequirement
{
    public function getPlane();

    public function setPlane($plane);
}