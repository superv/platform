<?php

namespace Tests\Platform\Http;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Platform\TestCase;

class DataControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $shouldBootPlatform = true;

    function test__returns_initial_user_data()
    {
        $response = $this->getJsonUser(route('sv::data.init'));
        $response->assertOk();

        $payload = $response->decodeResponseJson('data');

        $this->assertEquals(['id', 'name', 'email'], array_keys($payload['user']));
    }

    function test__returns_navigation_data()
    {
        $this->withoutExceptionHandling();
        $this->setUpPort('api')->setNavigationSlug('acp');

        $response = $this->getJsonUser(route('sv::data.nav'));
        $response->assertOk();

        $nav = $response->decodeResponseJson('data.nav');

        $this->assertEquals('Acp', $nav['title']);

        $sections = $nav['sections'];
        $this->assertArrayHasKey('platform', $sections);
    }
}
