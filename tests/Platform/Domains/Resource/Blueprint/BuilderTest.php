<?php

namespace Tests\Platform\Domains\Resource\Blueprint;

use Event;
use SuperV\Platform\Domains\Resource\Blueprint\Blueprint;
use SuperV\Platform\Domains\Resource\Blueprint\Builder;
use SuperV\Platform\Domains\Resource\Driver\DatabaseDriver;
use SuperV\Platform\Domains\Resource\Driver\DriverInterface;
use SuperV\Platform\Domains\Resource\Events\ResourceCreatedEvent;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Domains\Resource\ResourceModel;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class BuilderTest extends ResourceTestCase
{
    function test__belongs_to_relation()
    {
        Builder::run('testing.posts', function (Blueprint $resource) {
            $resource->id();

            $resource->belongsTo('testing.users', 'user')
                     ->foreignKey('user_id')
                     ->ownerKey('id');
        });

        $resource = ResourceFactory::make('testing.posts');

        $userRelation = $resource->getRelation('user');
        $this->assertNotNull($userRelation);
        $this->assertEquals('testing.users', $userRelation->getConfigValue('related_resource'));
        $this->assertEquals('user_id', $userRelation->getConfigValue('foreign_key'));
        $this->assertEquals('id', $userRelation->getConfigValue('owner_key'));

        $userField = $resource->getField('user');
        $this->assertNotNull($userField);
        $this->assertEquals('testing.users', $userField->getConfigValue('related_resource'));
        $this->assertEquals('user_id', $userField->getConfigValue('foreign_key'));
        $this->assertEquals('id', $userField->getConfigValue('owner_key'));
    }

    function test__creates_fields()
    {
        Builder::run('testing.posts', function (Blueprint $resource) {
            $resource->id();

            $resource->text('title', 'Post Title')->useAsEntryLabel();
            $resource->select('gender')->options(['male', 'female'])->hideOnView();
            $resource->text('email')->rules('email|unique')->unique();
        });

        $resource = ResourceFactory::make('testing.posts');

        $titleField = $resource->getField('title');
        $this->assertEquals('Post Title', $titleField->getLabel());
        $this->assertEquals('title', $resource->config()->getEntryLabelField());
        $this->assertEquals('{title}', $resource->config()->getEntryLabel());

        $genderField = $resource->getField('gender');
        $this->assertEquals(['options' => ['male', 'female']], $genderField->getConfig());
        $this->assertTrue($genderField->isHiddenOnView());
        $emailField = $resource->getField('email');
        $this->assertEquals(['email', 'unique'], $emailField->getRules());
        $this->assertTrue($emailField->isUnique());
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

        Builder::run('testing.posts', function (Blueprint $resource) {
            $resource->id();
        });

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