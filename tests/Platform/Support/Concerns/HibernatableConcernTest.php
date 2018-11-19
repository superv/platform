<?php

namespace SuperV\Platform\Support\Concerns;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Contracts\Hibernatable;
use Tests\Platform\TestCase;

class HibernatableConcernTest extends TestCase
{
    use RefreshDatabase;

    function test__do()
    {
        $bear = new TestBear;
        $this->assertNotNull($url = $bear->hibernate());

        $this->withoutExceptionHandling();
        $response = $this->getJsonUser($url);
        $response->assertOk();

        $this->assertEquals($bear->compose(), $response->decodeResponseJson('data'));
    }
}

class TestBear implements Hibernatable
{
    use HibernatableConcern;

    public function compose(array $params = [])
    {
        return ['doo' => 'fun'];
    }

}