<?php

namespace Tests\Platform\Platform\Http;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Platform\TestCase;

class DataControllerTest extends TestCase
{
    use RefreshDatabase;

    function test__returns_initial_user_data()
    {
        $response = $this->getJsonUser('data/init');
        $response->assertOk();

        $payload = $response->decodeResponseJson('data');

        $this->assertEquals(['id', 'name', 'email'], array_keys($payload['user']));
    }
}