<?php

namespace Tests\Platform\Domains\Resource\Builder;

use Event;
use SuperV\Platform\Domains\Resource\Builder\Blueprint;
use SuperV\Platform\Domains\Resource\Builder\Builder;
use SuperV\Platform\Domains\Resource\Builder\PrimaryKey;
use SuperV\Platform\Domains\Resource\Driver\DatabaseDriver;
use SuperV\Platform\Domains\Resource\Driver\DriverInterface;
use SuperV\Platform\Domains\Resource\Events\ResourceCreatedEvent;
use SuperV\Platform\Domains\Resource\Nav\Section;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Domains\Resource\ResourceModel;
use Tests\Platform\Domains\Resource\Fixtures\Models\TestPostModel;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class BuilderTest extends ResourceTestCase
{
    function test__creates_resource()
    {
        $resource = Builder::create('sv.testing.posts', function (Blueprint $resource) {
            $resource->label('The Posts');
            $resource->key('postkey');
            $resource->nav('acp.blog');
            $resource->model(TestPostModel::class);

            $resource->databaseDriver()
                     ->table('tbl_posts', 'default')
                     ->primaryKey(new PrimaryKey('post_id'));
        });

        $this->assertNotNull($resource);

        $this->assertEquals('posts', $resource->getHandle());
        $this->assertEquals('database@default://tbl_posts', $resource->getDsn());
        $this->assertTableExists('tbl_posts');

        $resourceConfig = $resource->config();
        $this->assertEquals('posts', $resourceConfig->getHandle());
        $this->assertEquals('The Posts', $resourceConfig->getLabel());
        $this->assertEquals('postkey', $resourceConfig->getResourceKey());
        $this->assertEquals('post_id', $resourceConfig->getKeyName());
        $this->assertEquals(TestPostModel::class, $resourceConfig->getModel());

        $nav = Section::get('acp.blog');
        $this->assertNotNull($nav->getChild('the_posts'));
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

    function test__invokes_driver()
    {
        $blueprint = Builder::blueprint('sv.testing.posts');

        $driverMock = $this->bindPartialMock(DriverInterface::class, DatabaseDriver::resolve());
        $driverMock->expects('run')->with($blueprint);

        $blueprint->driver($driverMock);

        Builder::resolve()->save($blueprint);
    }
}