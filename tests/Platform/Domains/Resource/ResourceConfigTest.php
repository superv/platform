<?php

namespace Tests\Platform\Domains\Resource;

use Closure;
use Event;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Events\ResourceConfigResolvedEvent as ResolvedEvent;
use SuperV\Platform\Domains\Resource\ResourceConfig;
use SuperV\Platform\Domains\Resource\ResourceConfig as Config;

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
        $resolvedEventName = 'sv.testing.any_table.events:config_resolved';
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
        $res = $this->create('tbl_users', function (Blueprint $table) {
            $table->increments('id');
            $table->sortable();
        });

        $this->assertTrue($res->config()->isSortable());
        $this->assertColumnExists('tbl_users', 'sort_order');
    }

    function test__id_column()
    {
        $res = $this->create('tbl_users', function (Blueprint $table) {
            $table->id();
        });

        $this->assertColumnExists('tbl_users', 'id');
    }


    function test__saves_resource_key()
    {
        $res = $this->create('tbl_users', function (Blueprint $table, Config $config) {
            $table->increments('id');

            // default resource key is singular name
            $this->assertEquals('tbl_user', $config->getResourceKey());

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
        $assertKeySaved(function (Blueprint $table, Config $config) {
            $config->keyName('entry_id');
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
        $this->assertEquals('Customer', $customers->getSingularLabel());
    }

    function test__builds_label_from_given()
    {
        $customers = $this->create('customers', function (Blueprint $table, Config $config) {
            $table->increments('id');

            $config->label('SuperV Customers');
            $config->singularLabel('Customer');
        });

        $this->assertEquals('SuperV Customers', $customers->getLabel());
        $this->assertEquals('Customer', $customers->getSingularLabel());
    }

    function test__can_update_database_record()
    {
        $customers = $this->create('customers', function (Blueprint $table, Config $config) {
            $table->increments('id');

            $config->label('Customers');
        });

        $customers->config()->label('Clients');
        $customers->config()->save();

        $this->assertEquals('Clients', ResourceConfig::find('sv.testing.customers')->getLabel());
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
