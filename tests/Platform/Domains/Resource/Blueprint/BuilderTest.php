<?php

namespace Tests\Platform\Domains\Resource\Blueprint;

use Event;
use SuperV\Platform\Domains\Resource\Blueprint\Blueprint;
use SuperV\Platform\Domains\Resource\Blueprint\Builder;
use SuperV\Platform\Domains\Resource\Driver\DatabaseDriver;
use SuperV\Platform\Domains\Resource\Driver\DriverInterface;
use SuperV\Platform\Domains\Resource\Events\ResourceCreatedEvent;
use SuperV\Platform\Domains\Resource\Field\Types\TextField;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Domains\Resource\ResourceModel;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class BuilderTest extends ResourceTestCase
{
    function test__creates_fields()
    {
        Builder::run('testing.posts', function (Blueprint $resource) {
            $resource->addField('title', TextField::class)->useAsEntryLabel();
        });

        $resource = ResourceFactory::make('testing.posts');
        $this->assertNotNull($resource->getField('title'));

        $this->assertEquals('title', $resource->config()->getEntryLabelField());
        $this->assertEquals('{title}', $resource->config()->getEntryLabel());
    }

    function test__creates_resource()
    {
        Builder::run('testing.posts', function (Blueprint $resource) {
            $resource->databaseDriver()
                     ->table('tbl_posts', 'default')
                     ->primaryKey('post_id');
        });

        $resource = ResourceModel::withIdentifier('testing.posts');
        $this->assertNotNull($resource);

        $this->assertEquals('posts', $resource->getHandle());
        $this->assertEquals('database@default://tbl_posts', $resource->getDsn());
        $this->assertTableExists('tbl_posts');
    }

    function test__dispatches_event_when_resource_is_created()
    {
        Event::fake([ResourceCreatedEvent::class]);

        Builder::run('testing.posts');

        Event::assertDispatched(ResourceCreatedEvent::class, function (ResourceCreatedEvent $event) {
            $this->assertInstanceOf(ResourceModel::class, $event->resourceEntry);
            $this->assertEquals('testing.posts', $event->resourceEntry->getIdentifier());

            return true;
        });
    }

    function test__invokes_driver()
    {
        $blueprint = Builder::blueprint('testing.posts');

        $driverMock = $this->bindPartialMock(DriverInterface::class, DatabaseDriver::resolve());
        $driverMock->expects('run')->with($blueprint);

        $blueprint->driver($driverMock);

        Builder::resolve()->save($blueprint);
    }
}