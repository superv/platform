<?php

namespace Tests\Platform\Domains\Resource\Builder;

use Event;
use SuperV\Platform\Domains\Resource\Builder\Blueprint;
use SuperV\Platform\Domains\Resource\Builder\Builder;
use SuperV\Platform\Domains\Resource\Builder\PrimaryKey;
use SuperV\Platform\Domains\Resource\Driver\DatabaseDriver;
use SuperV\Platform\Domains\Resource\Driver\DriverInterface;
use SuperV\Platform\Domains\Resource\Events\ResourceCreatedEvent;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Domains\Resource\ResourceModel;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class BuilderTest extends ResourceTestCase
{
    function __many_to_many_pivot()
    {
        Builder::create('sv.testing.roles', function (Blueprint $resource) {
            $resource->manyToMany('sv.testing.actions', 'actions')
                     ->pivot('sv.testing.roles_actions');
        });

        Builder::create('sv.testing.actions', function (Blueprint $resource) {
            $resource->manyToMany('sv.testing.roles', 'roles')
                     ->pivot('sv.testing.roles_actions');
        });

        $posts = ResourceFactory::make('sv.testing.roles');
        $this->assertFalse($posts->isPivot());

        $pivot = ResourceFactory::make('sv.testing.roles_actions');
        $this->assertTrue($pivot->isPivot());
    }

    function __many_to_many_relation()
    {
        Builder::create('sv.testing.roles', function (Blueprint $resource) {
            $resource->manyToMany('sv.testing.actions', 'actions')
                     ->pivot('sv.testing.roles_actions');
        });

        $resource = ResourceFactory::make('sv.testing.roles');
        $actionsRelation = $resource->getRelation('actions');
        $this->assertEquals([
            'related_resource'  => 'sv.testing.actions',
            'pivot_identifier'  => 'sv.testing.roles_actions',
            'pivot_table'       => 'roles_actions',
            'pivot_foreign_key' => 'role_id',
            'pivot_related_key' => 'action_id',
        ], $actionsRelation->getConfig());

        $pivot = ResourceFactory::make('sv.testing.roles_actions');
        $this->assertEquals('action_id', $pivot->getField('action')->getConfigValue('foreign_key'));
        $this->assertEquals('role_id', $pivot->getField('role')->getConfigValue('foreign_key'));

        $actionsField = $resource->getField('actions');
        $this->assertEquals('many_to_many', $actionsField->getType());
        $this->assertEquals($actionsRelation->getConfig(), $actionsField->getConfig());
    }

    function test__belongs_to_relation()
    {
        Builder::create('sv.testing.posts', function (Blueprint $resource) {
            $resource->belongsTo('sv.testing.users', 'user')
                     ->foreignKey('user_id')
                     ->ownerKey('id');
        });

        $resource = ResourceFactory::make('sv.testing.posts');

        $userRelation = $resource->getRelation('user');
        $this->assertNotNull($userRelation);
        $this->assertEquals('sv.testing.users', $userRelation->getConfigValue('related_resource'));
        $this->assertEquals('user_id', $userRelation->getConfigValue('foreign_key'));
        $this->assertEquals('id', $userRelation->getConfigValue('owner_key'));

        $userField = $resource->getField('user');
        $this->assertNotNull($userField);
        $this->assertEquals('sv.testing.users', $userField->getConfigValue('related_resource'));
        $this->assertEquals('user_id', $userField->getConfigValue('foreign_key'));
        $this->assertEquals('id', $userField->getConfigValue('owner_key'));
    }

    function test__creates_fields()
    {
        Builder::create('sv.testing.posts', function (Blueprint $resource) {
            $resource->text('title', 'Post Title')->useAsEntryLabel();
            $resource->select('gender')->options(['male', 'female'])->hideOnView();
            $resource->text('email')->rules('email|unique')->unique();

            $resource->boolean('active')->default(false);
        });

        $resource = ResourceFactory::make('sv.testing.posts');

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

        $this->assertFalse($resource->getField('active')->isRequired());
        $this->assertFalse($resource->getField('active')->getDefaultValue());
    }

    function test__creates_resource()
    {
        $resource = Builder::create('sv.testing.posts', function (Blueprint $resource) {
            $resource->key('postkey');
            $resource->databaseDriver()
                     ->table('tbl_posts', 'default')
                     ->primaryKey(new PrimaryKey('post_id'));
        });

//        $resource = ResourceFactory::make('sv.testing.posts');
        $this->assertNotNull($resource);

        $this->assertEquals('posts', $resource->getHandle());
        $this->assertEquals('database@default://tbl_posts', $resource->getDsn());
        $this->assertTableExists('tbl_posts');
        $this->assertEquals('postkey', $resource->config()->getResourceKey());
        $this->assertEquals('post_id', $resource->config()->getKeyName());
    }

    function test__dispatches_event_when_resource_is_created()
    {
        Event::fake([ResourceCreatedEvent::class]);

        Builder::create('sv.testing.posts', function (Blueprint $resource) {
            $resource->id();
        });

        Event::assertDispatched(ResourceCreatedEvent::class, function (ResourceCreatedEvent $event) {
            $this->assertInstanceOf(ResourceModel::class, $event->resourceEntry);
            $this->assertEquals('sv.testing.posts', $event->resourceEntry->getIdentifier());

            return true;
        });
    }

    function test__invokes_driver()
    {
        $blueprint = Builder::blueprint('sv.testing.posts');

        $driverMock = $this->bindPartialMock(DriverInterface::class, DatabaseDriver::resolve());
        $driverMock->expects('run')->with($blueprint);

        $blueprint->driver($driverMock);

        Builder::resolve()->save($blueprint);
    }
}