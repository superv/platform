<?php

namespace Tests\Platform\Domains\Resource\Features;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\ResourceConfig as Config;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class EntryLabelTest extends ResourceTestCase
{
    function test__builds_label_for_resource_entry()
    {
        $res = $this->create('customers', function (Blueprint $table, Config $config) {
            $table->increments('id');
            $table->string('first_name');
            $table->string('last_name');

            $config->entryLabel('{last_name}, {first_name}');
        });

        $entry = $res->create(['first_name' => 'Nicola', 'last_name' => 'Tesla']);

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
        $this->assertEquals('{name}', ResourceFactory::make('sv.testing.A_users')->getEntryLabelTemplate());

        $this->makeResource('B_users', ['address', 'age:integer', 'title']);
        $this->assertEquals('{title}', ResourceFactory::make('sv.testing.B_users')->getEntryLabelTemplate());

        $this->makeResource('C_users', ['height:decimal', 'age:integer', 'address']);
        $this->assertEquals('{address}', ResourceFactory::make('sv.testing.C_users')->getEntryLabelTemplate());

        $this->makeResource('customers', ['height:decimal', 'age:integer']);
        $this->assertEquals('Customer #{id}', ResourceFactory::make('sv.testing.customers')->getEntryLabelTemplate());
    }
}