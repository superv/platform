<?php

namespace Tests\Platform\Domains\Resource;

use Closure;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\ResourceConfig;
use SuperV\Platform\Domains\Resource\ResourceFactory;

/**
 * Class ResourceConfigTest
 *
 * @package Tests\Platform\Domains\Resource
 * @group   resource
 */
class ResourceConfigTest extends ResourceTestCase
{
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
        $res = $this->create('t_users', function (Blueprint $table, ResourceConfig $config) {
            $table->increments('id');

            // default resource key is singular table name
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
        $assertKeySaved(function (Blueprint $table, ResourceConfig $resource) {
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
        $this->assertEquals('platform::resources.customers.singular', $customers->getSingularLabel());
    }

    function test__builds_label_from_given()
    {
        $customers = $this->create('customers', function (Blueprint $table, ResourceConfig $config) {
            $table->increments('id');

            $config->label('SuperV Customers');
            $config->singularLabel('Customer');
        });

        $this->assertEquals('SuperV Customers', $customers->getLabel());
        $this->assertEquals('platform::resources.customers.singular', $customers->getSingularLabel());
    }

    function test__builds_label_for_resource_entry()
    {
        $res = $this->create('customers', function (Blueprint $table, ResourceConfig $resource) {
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
        $this->assertEquals('{name}', ResourceFactory::make('A_users')->getEntryLabelTemplate());

        $this->makeResource('B_users', ['address', 'age:integer', 'title']);
        $this->assertEquals('{title}', ResourceFactory::make('B_users')->getEntryLabelTemplate());

        $this->makeResource('C_users', ['height:decimal', 'age:integer', 'address']);
        $this->assertEquals('{address}', ResourceFactory::make('C_users')->getEntryLabelTemplate());

        $this->makeResource('customers', ['height:decimal', 'age:integer']);
        $this->assertEquals('Customer #{id}', ResourceFactory::make('customers')->getEntryLabelTemplate());
    }

    function test__finds_by_resource_handle()
    {
        $res = $this->create(function (Blueprint $table, ResourceConfig $config) {
            $table->increments('id');
            $config->keyName('some_key');
        });

        $config = ResourceConfig::find($res->getIdentifier());
        $this->assertInstanceOf(ResourceConfig::class, $config);
        $this->assertEquals('some_key', $config->getKeyName());
    }
}
