<?php

namespace Tests\Platform\Domains\Context;

use SuperV\Platform\Domains\Context\Negotiator;
use Tests\Platform\TestCase;

require_once(__DIR__.'/fixtures.php');

class NegotiatorTest extends TestCase
{
    function test__gets_value_from_provider_sets_to_acceptor()
    {
        $provider = new SolidProvider;
        $provider->plane = 'Boink';
        $provider->money = null;

        $acceptor = new SolidAcceptor;
        $acceptor->plane = null;
        $acceptor->money = 123;

        Negotiator::deal($provider, $acceptor);

        $this->assertEquals('Boink', $acceptor->plane);
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
