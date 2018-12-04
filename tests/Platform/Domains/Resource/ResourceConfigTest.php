<?php

namespace Tests\Platform\Domains\Resource;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\ResourceBlueprint;
use SuperV\Platform\Domains\Resource\ResourceFactory;

class ResourceConfigTest extends ResourceTestCase
{
    function test__trashable()
    {
        $res = $this->create('t_users', function (Blueprint $table) {
            $table->increments('id');

            $table->restorable();
        });

        $this->assertTrue($res->isRestorable());
        $this->assertColumnExists('t_users', 'deleted_at');
    }

    function test__saves_resource_key()
    {
        $res = $this->create('t_users', function (Blueprint $table, ResourceBlueprint $resource) {
            $table->increments('id');

            // default resource key is singular table name
            $this->assertEquals('t_user', $resource->getResourceKey());

            // but we can override it
            $resource->resourceKey('user');
        });

        $this->assertEquals('user', $res->getResourceKey());
    }

    function test__builds_label_from_table_name()
    {
        $this->create('customers', function (Blueprint $table) {
            $table->increments('id');
        });

        $this->assertEquals('Customers', ResourceFactory::make('customers')->getLabel());
        $this->assertEquals('Customer', ResourceFactory::make('customers')->getSingularLabel());
    }

    function test__builds_label_from_given()
    {
        $this->create('customers', function (Blueprint $table, ResourceBlueprint $resource) {
            $table->increments('id');

            $resource->label('SuperV Customers');
            $resource->singularLabel('Customer');
        });

        $this->assertEquals('SuperV Customers', ResourceFactory::make('customers')->getLabel());
        $this->assertEquals('Customer', ResourceFactory::make('customers')->getSingularLabel());
    }

    function test__builds_label_for_resource_entry()
    {
        $res = $this->create('customers', function (Blueprint $table, ResourceBlueprint $resource) {
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
}