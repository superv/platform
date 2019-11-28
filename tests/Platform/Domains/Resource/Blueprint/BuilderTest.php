<?php

namespace Tests\Platform\Domains\Resource\Blueprint;

use SuperV\Platform\Domains\Resource\Blueprint\Blueprint;
use SuperV\Platform\Domains\Resource\Blueprint\Builder;
use SuperV\Platform\Domains\Resource\Driver\DatabaseDriver;
use SuperV\Platform\Domains\Resource\Driver\DriverInterface;
use SuperV\Platform\Domains\Resource\ResourceModel;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class BuilderTest extends ResourceTestCase
{
    function test__creates_resource()
    {
        Builder::resolve()
               ->build('core.posts', function (Blueprint $resource) {
                   $resource->databaseDriver()
                            ->table('tbl_posts', 'default')
                            ->primaryKey('post_id');
               });

        $resource = ResourceModel::withIdentifier('core.posts');
        $this->assertNotNull($resource);

        $this->assertEquals('posts', $resource->getHandle());
        $this->assertEquals('database@default://tbl_posts', $resource->getDsn());

        $this->assertTableExists('tbl_posts');
    }

    function test__invokes_driver()
    {
        $blueprint = Builder::blueprint('core.posts');

        $driverMock = $this->bindPartialMock(DriverInterface::class, DatabaseDriver::resolve());
        $driverMock->expects('run')->with($blueprint);

        $blueprint->driver($driverMock);

        Builder::resolve()->save($blueprint);
    }
}