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
        $response = $this->getJsonUser(route('sv.data.init'));
        $response->assertOk();

        $payload = $response->decodeResponseJson('data');

        $this->assertEquals(['id', 'name', 'email'], array_keys($payload['user']));
    }
}
