<?php

namespace Tests\Platform\Domains\Resource\Hook;

/**
 * Class ObserverHookTest
 *
 * @package Tests\Platform\Domains\Resource
 * @group   resource
 */
class ObserverHookTest extends HookTestCase
{
    function test_observes_before_creating()
    {
        $_SERVER['__hooks::observer.creating'] = null;
        $this->blueprints()->categories()->fake();
        $this->assertEquals([
            'resource' => 'testing.categories',
            'exists'   => false,
        ], $_SERVER['__hooks::observer.creating']);
    }

    function test_observes_after_created()
    {
        $_SERVER['__hooks::observer.created'] = null;
        $this->blueprints()->categories()->fake();
        $this->assertEquals([
            'resource' => 'testing.categories',
            'exists'   => true,
        ], $_SERVER['__hooks::observer.created']);
    }

    function test_observes_after_retrieved()
    {
        $_SERVER['__hooks::observer.retrieved'] = null;
        $post = $this->makeResource('testing.orders', ['title:text'])->fake();
        $post->fresh();

        $this->assertEquals([
            'resource' => 'testing.orders',
        ], $_SERVER['__hooks::observer.retrieved']);
    }

    function test_observes_after_deleted()
    {
        $_SERVER['__hooks::observer.deleted'] = null;
        $post = $this->makeResource('testing.orders', ['title:text'])->fake();
        $post->delete();

        $this->assertEquals([
            'resource' => 'testing.orders',
            'fresh'    => null,
        ], $_SERVER['__hooks::observer.deleted']);
    }

    function test_observes_before_saving()
    {
        $posts = $this->makeResource('testing.posts', ['title:text']);
        $post = $posts->create(['title' => 'Post']);
        $this->assertEquals('Post Saving', $post->title);
    }

    function test_observes_after_saved()
    {
        $this->makeResource('testing.orders', ['title:text'])->fake();;

        $this->assertEquals([
            'resource' => 'testing.orders',
            'saved'    => true,
        ], $_SERVER['__hooks::observer.saved']);
    }
}
