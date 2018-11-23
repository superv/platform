<?php

namespace Tests\Platform\Support\Composer;

use SuperV\Platform\Support\Composer\Composable;
use Tests\Platform\TestCase;

class ComposerTest extends TestCase
{
    function test__bsmllh()
    {
        $obj = new TestComposable(['data']);

        $this->assertEquals(['data'], sv_compose($obj));
    }

    function test__parses_before_composing()
    {
        $obj = new TestComposable(['url' => 'users/{user.id}']);

        $this->assertEquals(
            ['url' => 'users/123'],
            sv_compose($obj, ['user' => ['id' => 123]])
        );
    }

    function test__childrens()
    {
        $action = new TestComposable(['url' => '{resource}/{entry}'], ['entry' => 123]);
        $parent = new TestComposable(['context' => '{context}',
                                                'action'  => $action], ['resource' => 'users']);

        $composed = sv_compose($parent, ['context' => 'table']);

        $this->assertEquals([
            'context' => 'table',
            'action'  => [
                'url' => 'users/123',
            ],
        ], $composed);
    }

    function test__childrens_two_level()
    {
        $tic = new TestComposable(['url' => '{parent}/tics/{id}'], ['id' => 123]);
        $tac = new TestComposable(['url' => '{parent}/tacs/{id}'], ['id' => 456]);
        $parent = new TestComposable(['owner' => '{owner}', 'tic' => $tic, 'tac' => $tac], ['parent' => 'abc']);

        $composed = sv_compose($parent, ['owner' => 'tdd']);

        $this->assertEquals([
            "owner" => "tdd",
            "tic"   => [
                "url" => "abc/tics/123",
            ],
            "tac"   => [
                "url" => "abc/tacs/456",
            ],
        ], $composed);
    }
}

class TestComposable implements Composable
{
    public $data = [];

    /**
     * @var array
     */
    public $tokens;


    public function __construct(array $data, array $tokens = [])
    {
        $this->data = $data;
        $this->tokens = $tokens;
    }

    public function compose(\SuperV\Platform\Support\Composer\Tokens $tokens = null)
    {
        return sv_compose($this->data, $tokens->merge($this->tokens));
    }
}