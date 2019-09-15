<?php

namespace Tests\Platform\Domains\Resource;

use Closure;
use Event;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Events\ResourceConfigResolvedEvent as ResolvedEvent;
use SuperV\Platform\Domains\Resource\ResourceConfig as Config;
use SuperV\Platform\Domains\Resource\ResourceFactory;

/**
 * Class ResourceConfigTest
 *
 * @package Tests\Platform\Domains\Resource
 * @group   resource
 */
class ResourceConfigTest extends ResourceTestCase
{
    function test__dispatches_event_when_resolved()
    {
        $resolvedEventName = 'testing::any_table::config.resolved';
        Event::fake([ResolvedEvent::class, $resolvedEventName]);
        $any = $this->anyTable();

        Event::assertDispatched($resolvedEventName, function ($event, $config) use ($any) {
            return $any->config()->toArray() === $config->toArray();
        });

        Event::assertDispatched(ResolvedEvent::class,
            function (ResolvedEvent $event) use ($any) {
                return $any->config()->toArray() === $event->config()->toArray();
            });
    }

    function test__sortable()
    {
        $res = $this->create('t_users', function (Blueprint $table) {
            $table->increments('id');
            $table->sortable();
        });

        $this->assertTrue($res->config()->isSortable());
        $this->assertColumnExists('t_users', 'sort_order');
    }

    function test__id_column()
    {
        $res = $this->create('t_users', function (Blueprint $table) {
            $table->id();
        });

        $this->assertColumnExists('t_users', 'id');
    }

    function test__uuid()
    {
        $res = $this->create('t_users', function (Blueprint $table) {
            $table->increments('id');
            $table->hasUuid();
        });

        $this->assertColumnExists('t_users', 'uuid');
        $this->assertTrue($res->config()->hasUuid());

        $user = $res->create([]);
        $this->assertNotNull($user->uuid);
    }

    function test__saves_resource_key()
    {
        $res = $this->create('t_users', function (Blueprint $table, Config $config) {
            $table->increments('id');

            // default resource key is singular name
            $this->assertEquals('t_user', $config->getResourceKey());

            // but we can override it
            $config->resourceKey('user');
        });

        $this->assertEquals('user', $res->config()->getResourceKey());
    }

    function test__saves_primary_key()
    {
        $assertKeySaved = function (Closure $callback) {
            $this->assertEquals('entry_id', $this->randomTable($callback)->config()->getKeyName());
        };

        $assertKeySaved(function (Blueprint $table) { $table->increments('entry_id'); });
        $assertKeySaved(function (Blueprint $table) { $table->id('entry_id'); });
        $assertKeySaved(function (Blueprint $table) { $table->integer('entry_id')->primary(); });
        $assertKeySaved(function (Blueprint $table, Config $resource) {
            $resource->keyName('entry_id');
            $table->integer('entry_id');
        });

        $resource = $this->randomTable(function (Blueprint $table) {
            $table->increments('id');
        });
        $this->assertEquals('id', $resource->config()->getKeyName());
    }

    function test__builds_label_from_table_name()
    {
        $customers = $this->create('customers', function (Blueprint $table) {
            $table->increments('id');
        });

        $this->assertEquals('Customers', $customers->getLabel());
        $this->assertEquals('platform::customers.singular', $customers->getSingularLabel());
    }

    function test__builds_label_from_given()
    {
        $customers = $this->create('customers', function (Blueprint $table, Config $config) {
            $table->increments('id');

            $config->label('SuperV Customers');
            $config->singularLabel('Customer');
        });

        $this->assertEquals('SuperV Customers', $customers->getLabel());
        $this->assertEquals('platform::customers.singular', $customers->getSingularLabel());
    }

    function test__builds_label_for_resource_entry()
    {
        $res = $this->create('customers', function (Blueprint $table, Config $resource) {
            $table->increments('id');
            $table->string('first_name');
            $table->string('last_name');

            $resource->entryLabel('{last_name}, {first_name}');
        });

        $entry = $res->fake(['first_name' => 'Nicola', 'last_name' => 'Tesla']);

        $this->assertEquals('Tesla, Nicola', $res->getEntryLabel($entry));
    }

    function test__makes_entry_label_from_marked_column()
    {
        $res = $this->create('customers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('first_name');
            $table->string('last_name')->entryLabel();
        });

        $entry = $res->fake();

        $this->assertEquals($entry->getAttribute('last_name'), $res->getEntryLabel($entry));
    }

    function test__guesses_entry_label_from_string_columns()
    {
        $this->makeResource('A_users', ['name']);
        $this->assertEquals('{name}', ResourceFactory::make('platform::A_users')->getEntryLabelTemplate());

        $this->makeResource('B_users', ['address', 'age:integer', 'title']);
        $this->assertEquals('{title}', ResourceFactory::make('platform::B_users')->getEntryLabelTemplate());

        $this->makeResource('C_users', ['height:decimal', 'age:integer', 'address']);
        $this->assertEquals('{address}', ResourceFactory::make('platform::C_users')->getEntryLabelTemplate());

        $this->makeResource('customers', ['height:decimal', 'age:integer']);
        $this->assertEquals('Customer #{id}', ResourceFactory::make('platform::customers')->getEntryLabelTemplate());
    }

    function test__finds_by_resource_handle()
    {
        $res = $this->create('tmp_table', function (Blueprint $table, Config $config) {
            $table->increments('id');
            $config->keyName('some_key');
        });

        $config = Config::find($res->getIdentifier());
        $this->assertInstanceOf(Config::class, $config);
        $this->assertEquals('some_key', $config->getKeyName());
    }
}
