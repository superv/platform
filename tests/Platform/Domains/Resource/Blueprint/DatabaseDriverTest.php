<?php

namespace Tests\Platform\Domains\Resource\Blueprint;

use SuperV\Platform\Domains\Resource\Blueprint\Blueprint;
use SuperV\Platform\Domains\Resource\Blueprint\Builder;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class DatabaseDriverTest extends ResourceTestCase
{
    function test__run()
    {
        $blueprint = Builder::resource('core.posts', function (Blueprint $resource) { });

        $blueprint->getDriver()->run($blueprint);

        $this->assertTableExists('posts');
        $this->assertColumnExists('posts', 'id');
    }
}