<?php

namespace Tests\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Database\Blueprint;
use SuperV\Platform\Domains\Database\Schema;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class FileFieldTest extends ResourceTestCase
{
    /** @test */
    function creates_field_from_migration()
    {

        Schema::create('test_companies', function (Blueprint $table) {
            $table->string('name');
            $table->file('logo')->config(['*config*']);
        });

        $resource = ResourceFactory::make('test_companies');
        $resource->build();

        $this->assertEquals(['name'], \Schema::getColumnListing('test_companies'));
        $this->assertEquals(2, $resource->getFields()->count());
        $this->assertEquals(['*config*'], $resource->getField('logo')->getConfig());
    }
}