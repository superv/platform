<?php

namespace Tests\Platform\Domains\Resource\Database;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\ResourceConfig;
use Tests\Platform\Domains\Resource\ResourceTestCase;

/**
 * Class EntryTest
 *
 * @package Tests\Platform\Domains\Resource
 * @group   resource
 */
class EntryEventsTest extends ResourceTestCase
{
    protected $dispatchedEvents = [];

    function test__dispatches_event_before_creating()
    {
        $eventName = sprintf("testing.posts::entry.creating");
        app('events')->listen(
            $eventName, function (EntryContract $entry) use ($eventName) {
            $this->assertEquals('testing.posts', $entry->getResourceIdentifier());
            $this->assertFalse($entry->exists());
            $this->assertEquals(['title' => 'My New Post'], $entry->toArray());
            $this->dispatchedEvents[$eventName] = true;
        });
        $this->createPostEntry(['title' => 'My New Post']);
        $this->assertArrayHasKey($eventName, $this->dispatchedEvents);
    }

    function test__dispatches_event_after_created()
    {
        $eventName = sprintf("testing.posts::entry.created");
        app('events')->listen(
            $eventName, function (EntryContract $entry) use ($eventName) {
            $this->assertEquals('testing.posts', $entry->getResourceIdentifier());
            $this->assertTrue($entry->exists());
            $this->assertEquals('My New Post', $entry->getAttribute('title'));
            $this->dispatchedEvents[$eventName] = true;
        });
        $this->createPostEntry(['title' => 'My New Post']);
        $this->assertArrayHasKey($eventName, $this->dispatchedEvents);
    }

    function test__dispatches_event_before_saving()
    {
        $eventName = sprintf("testing.posts::entry.saving");
        $myPost = $this->createPostEntry();
        app('events')->listen(
            $eventName, function (EntryContract $entry) use ($eventName) {
            $this->assertEquals('testing.posts', $entry->getResourceIdentifier());
            $this->assertEquals('My Post', $entry->fresh()->title);
            $this->assertEquals('My Post v2', $entry->getAttribute('title'));
            $this->dispatchedEvents[$eventName] = true;
        });

        $myPost->update(['title' => 'My Post v2']);
        $this->assertArrayHasKey($eventName, $this->dispatchedEvents);
    }

    function test__dispatches_event_after_saved()
    {
        $eventName = sprintf("testing.posts::entry.saved");
        $myPost = $this->createPostEntry();
        app('events')->listen(
            $eventName, function (EntryContract $entry) use ($eventName) {
            $this->assertEquals('My Post v2', $entry->fresh()->title);
            $this->assertEquals('My Post v2', $entry->getAttribute('title'));
            $this->dispatchedEvents[$eventName] = true;
        });

        $myPost->update(['title' => 'My Post v2']);
        $this->assertArrayHasKey($eventName, $this->dispatchedEvents);
    }

    function test__dispatches_event_when_deleted()
    {
        $eventName = sprintf("testing.posts::entry.deleted");
        $myPost = $this->createPostEntry();
        app('events')->listen(
            $eventName, function (EntryContract $entry) use ($eventName) {
            $this->assertEquals('testing.posts', $entry->getResourceIdentifier());
            $this->assertNull($entry->fresh());
            $this->dispatchedEvents[$eventName] = true;
        });

        $myPost->delete();
        $this->assertArrayHasKey($eventName, $this->dispatchedEvents);
    }

    function test__dispatches_event_when_retrieved()
    {
        $eventName = sprintf("testing.posts::entry.retrieved");
        $myPost = $this->createPostEntry();
        app('events')->listen(
            $eventName, function (EntryContract $entry) use ($myPost, $eventName) {
            $this->assertEquals('testing.posts', $entry->getResourceIdentifier());
            $this->assertEquals($myPost->toArray(), $entry->toArray());

            $this->dispatchedEvents[$eventName] = true;
        });

        sv_resource('testing.posts')->find($myPost->getId());
        $this->assertArrayHasKey($eventName, $this->dispatchedEvents);
    }

    function test__saves_created_by_field_when_an_entry_is_created()
    {
        $this->withoutExceptionHandling();

        $posts = $this->create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->createdBy();
        });

        $this->postJsonUser($posts->router()->createForm(), ['title' => 'Some Post'])->assertOk();

        $this->assertEquals($this->testUser->id, $posts->first()->created_by_id);
    }

    function test__saves_updated_by_field_when_an_entry_is_updated()
    {
        $this->withoutExceptionHandling();

        $posts = $this->create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->updatedBy();
        });

        $post = $posts->create(['title' => 'Some Post']);

        $this->postJsonUser($post->router()->updateForm(), ['title' => 'Updated Post'])->assertOk();

        $this->assertEquals($this->testUser->id, $posts->first()->updated_by_id);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->dispatchedEvents = [];
    }

    protected function createPostEntry(array $attributes = []): EntryContract
    {
        $myPost = $this->create('posts', function (Blueprint $table, ResourceConfig $config) {
            $config->setNamespace('testing');
            $table->id();
            $table->string('title');
        })->fake(array_merge(['title' => 'My Post'], $attributes));

        return $myPost;
    }
}
