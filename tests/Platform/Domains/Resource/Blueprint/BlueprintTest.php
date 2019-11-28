<?php

namespace Tests\Platform\Domains\Resource\Blueprint;

use SuperV\Platform\Domains\Resource\Blueprint\Blueprint;
use SuperV\Platform\Domains\Resource\Blueprint\Builder;
use SuperV\Platform\Domains\Resource\Driver\DriverInterface;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class BlueprintTest extends ResourceTestCase
{
    function test__creates_blueprint()
    {
        $blueprint = Builder::resource('core.posts', function (Blueprint $resource) {
            $resource->databaseDriver()
                     ->table('tbl_posts', 'default')
                     ->primaryKey('post_id');
        });

        $this->assertInstanceOf(Blueprint::class, $blueprint);
        $this->assertInstanceOf(DriverInterface::class, $blueprint->getDriver());

        $this->assertEquals('core.posts', $blueprint->getIdentifier());
        $this->assertEquals('tbl_posts', $blueprint->getDriver()->getParam('table'));
        $this->assertEquals([
            'name'          => 'post_id',
            'type'          => 'integer',
            'autoincrement' => true,
        ], $blueprint->getDriver()->getParam('primary_keys')[0]);
    }

    function test__defaults()
    {
        $blueprint = Builder::resource('core.posts', function (Blueprint $resource) { });

        $this->assertEquals('posts', $blueprint->getDriver()->getParam('table'));
        $this->assertEquals([
            'name'          => 'id',
            'type'          => 'integer',
            'autoincrement' => true,
        ], $blueprint->getDriver()->getParam('primary_keys')[0]);
    }
}