<?php

namespace Tests\Platform\Domains\Resource;

use Event;
use Exception;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Schema\ColumnDefinition;
use SuperV\Platform\Domains\Database\Schema\Schema;
use SuperV\Platform\Domains\Resource\Events\ResourceCreatedEvent;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceConfig as Config;
use SuperV\Platform\Domains\Resource\ResourceDriver;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Domains\Resource\ResourceModel;
use Tests\Platform\Domains\Resource\Fixtures\TestUser;

/**
 * Class CreateResourceTest
 *
 * @package Tests\Platform\Domains\Resource
 * @group   resource
 */
class CreateResourceTest extends ResourceTestCase
{
    function test__creates_resource_model_entry_when_a_table_is_created()
    {
        Schema::create('test_users', function (Blueprint $table) {
            $table->increments('id');
        });

        $this->assertDatabaseHas('sv_resources', [
            'name'       => 'test_users',
            'identifier' => 'platform.test_users',
        ]);
        $resourceEntry = ResourceModel::withIdentifier('platform.test_users');
        $this->assertNotNull($resourceEntry);
        $this->assertNotNull($resourceEntry->uuid);
        $this->assertEquals('test_users', $resourceEntry->getName());
        $this->assertEquals('platform.test_users', $resourceEntry->getIdentifier());
        $this->assertEquals('platform', $resourceEntry->getNamespace());

        $this->assertEquals([
            'type'   => 'database',
            'params' => [
                'connection' => 'sqlite',
                'table'      => 'test_users',
            ],
        ], $resourceEntry->getConfigValue('driver'));

        $this->assertEquals('database@sqlite://test_users', $resourceEntry->getDsn());
    }

    function test__saves_resource_model_class_if_provided()
    {
        Schema::create('test_users', function (Blueprint $table, Config $resource) {
            $table->increments('id');
            $resource->model(TestUser::class);
        });

        $this->assertEquals(TestUser::class, ResourceModel::withIdentifier('platform.test_users')->getModelClass());
        $this->assertInstanceOf(TestUser::class, ResourceFactory::make('platform.test_users')->newEntryInstance());
    }

    function test__driver_config()
    {
        $resource = $this->create('core_servers', function (Blueprint $table, Config $config) {
            $table->increments('id');
        });

        $config = $resource->config();
        $driver = $config->getDriver();
        $this->assertInstanceOf(ResourceDriver::class, $driver);
        $this->assertEquals('core_servers', $driver->getParam('table'));
        $this->assertEquals('sqlite', $driver->getParam('connection'));
        $this->assertEquals('database', $driver->getType());
    }

    function test__custom_name()
    {
        $resource = $this->create('core_servers', function (Blueprint $table, Config $config) {
            $config->setName('servers');
            $table->increments('id');
        });

        $config = $resource->config();
        $this->assertEquals('platform.servers', $config->getIdentifier());

        $this->assertTrue((\Schema::connection('sqlite')->hasTable('core_servers')));
    }

    function test__custom_namespace()
    {
        $resource = $this->create('core_servers', function (Blueprint $table, Config $config) {
            $config->setNamespace('custom');
            $config->setName('servers');
            $table->increments('id');
        });

        $config = $resource->config();
        $this->assertEquals('custom.servers', $config->getIdentifier());

        $this->assertTrue(Resource::exists('custom.servers'));
    }

    function test__custom_connection()
    {
        file_put_contents($this->basePath('sv-testing.sqlite'), '');
        $resource = $this->create('core_servers2', function (Blueprint $table, Config $config) {
            $table->increments('id');
        }, 'sqlite2');

        $this->assertTrue((\Schema::connection('sqlite2')->hasTable('core_servers2')));

        $this->assertEquals('database@sqlite2://core_servers2', $resource->getDsn());

        $server = $resource->fake();
        $this->assertEquals('sqlite2', $server->getConnectionName());
    }

    function test__name_is_different_from_table_name()
    {
        $this->create('core_locations', function (Blueprint $table, Config $config) {
            $table->increments('id');

            $table->belongsToMany('servers', 'servers')->pivotForeignKey('location_id')
                  ->pivotRelatedKey('server_id')
                  ->pivotTable('testing.ore_location_servers');
        });

        $this->create('core_servers', function (Blueprint $table, Config $config) {
            $config->setName('servers');
            $table->increments('id');

            $table->belongsToMany('core_locations', 'locations')->pivotForeignKey('server_id')
                  ->pivotRelatedKey('location_id')
                  ->pivotTable('testing.core_location_servers');
        });

        $resource = ResourceFactory::make('platform.servers');
        $this->assertNotNull($resource);

        $this->assertEquals('core_servers', $resource->config()->getDriver()->getParam('table'));

        $server = ResourceFactory::make('platform.servers')->create([]);
        $this->assertTrue($server->exists());
        $this->assertEquals('core_servers', $server->getTable());
    }

    function test__creates_field_when_a_database_column_is_created()
    {
        $resource = $this->makeResourceModel('testing.test_users', ['username', 'age:integer', 'bio:text']);
        $this->assertEquals(3, $resource->fields()->count());

        $usernameField = $resource->getField('username');
//        $this->assertEquals('testing.test_users.fields', $usernameField->getNamespace());
        $this->assertEquals('testing.test_users.fields:username', $usernameField->getIdentifier());
        $this->assertEquals('username', $usernameField->getName());
        $this->assertEquals('string', $usernameField->getColumnType());

        $ageField = $resource->getField('age');
        $this->assertEquals('integer', $ageField->getColumnType());
    }

    function test__fields_are_unique_per_resource()
    {
        $resourceEntry = $this->makeResourceModel('test_users', ['name']);
        $this->assertEquals(1, $resourceEntry->fields()->count());

        $this->expectException(Exception::class);
        $resourceEntry->makeField('name');
    }

    function test__saves_field_rules()
    {
        $resource = $this->create('test_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->rules(['min:6', 'max:32']);
            $table->string('email')->rules('email|unique');
        });

        $this->assertArrayContains(['min:6', 'max:32'], $resource->getField('name')->getRules());
        $this->assertArrayContains(['email', 'unique'], $resource->getField('email')->getRules());
    }

    function test__saves_field_type()
    {
        Schema::create('test_users', function (Blueprint $table) {
            $table->increments('id');
            $table->select('status')->options(['closed' => 'Closed', 'open' => 'Open'])->default('open');
        });

        $resourceEntry = ResourceModel::withIdentifier('platform.test_users');

        $statusField = $resourceEntry->getField('status');
        $this->assertEquals('string', $statusField->getColumnType());
        $this->assertEquals('select', $statusField->getType());
        $this->assertEquals(['closed' => 'Closed', 'open' => 'Open'], $statusField->getConfigValue('options'));
    }

    function test__updates_field_rules()
    {
        Schema::create('test_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->rules(['min:6', 'max:32']);
            $table->string('email')->rules('email|unique');
        });

        Schema::table('test_users', function (Blueprint $table) {
            $table->string('name')->change()->rules(['min:16', 'max:64']);
        });
        $resourceEntry = ResourceModel::withIdentifier('platform.test_users');
        $nameField = $resourceEntry->getField('name');

        $this->assertArrayContains(['min:16', 'max:64'], $nameField->getRules());
    }

    function test__deletes_field_when_a_column_is_dropped()
    {
        $resourceEntry = $this->makeResourceModel('test_users', ['name', 'title']);

        $this->assertNotNull($resourceEntry->getField('name'));
        $this->assertNotNull($resourceEntry->getField('title'));

        Schema::table('test_users', function (Blueprint $table) {
            $table->dropColumn(['title', 'name']);
        });
        $resourceEntry->load('fields');
        $this->assertNull($resourceEntry->getField('name'));
        $this->assertNull($resourceEntry->getField('title'));
    }

    function test__deletes_fields_when_a_resource_is_deleted()
    {
        $resourceEntry = $this->makeResourceModel('test_users', ['name', 'title']);

        $this->assertEquals(2, $resourceEntry->fields()->count());

        $resourceEntry->delete();

        $this->assertEquals(0, $resourceEntry->fields()->count());
    }

    function test__marks_required_columns()
    {
        $resource = $this->makeResource('test_users', ['name', 'title' => 'nullable']);

        $this->assertTrue($resource->getField('title')->hasFlag('nullable'));
        $this->assertTrue($resource->getField('name')->isRequired());
    }

    function test__marks_unique_columns()
    {
        $resource = $this->create('test_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->unique();
        });

        $email = $resource->getField('email');
        $this->assertTrue($email->isUnique());

        /** make sure we call the parent method for db unique index **/
        $columnDefinition = new ColumnDefinition(Config::make());
        $columnDefinition->unique();
        $this->assertTrue($columnDefinition->unique);
    }

    function test__marks_searchable_columns()
    {
        $resource = $this->makeResource('test_users', ['name', 'title' => 'searchable']);

        $this->assertTrue($resource->getField('title')->hasFlag('searchable'));
    }

    function test__save_column_default_value()
    {
        Schema::create('test_users', function (Blueprint $table) {
            $table->string('title')->default('User');
        });

        $resource = ResourceModel::withIdentifier('platform.test_users');
        $this->assertEquals('User', $resource->getField('title')->getDefaultValue());
    }

    function test__dispatches_event_when_created()
    {
        Event::fake([ResourceCreatedEvent::class]);

        $this->blueprints()->posts();

        Event::assertDispatched(ResourceCreatedEvent::class, function (ResourceCreatedEvent $event) {
            $this->assertInstanceOf(ResourceModel::class, $event->resourceEntry);
            $this->assertEquals('platform.t_posts', $event->resourceEntry->getIdentifier());

            return true;
        });
    }

    protected function tearDown()
    {
        @unlink($this->basePath('sv-testing.sqlite'));
        parent::tearDown();
    }
}

