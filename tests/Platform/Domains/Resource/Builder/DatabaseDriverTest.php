<?php

namespace Tests\Platform\Domains\Resource\Builder;

use SuperV\Platform\Domains\Resource\Builder\Blueprint;
use SuperV\Platform\Domains\Resource\Builder\Builder;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class DatabaseDriverTest extends ResourceTestCase
{
    function test__primary_keys()
    {
        $blueprint = Builder::blueprint('core.posts', function (Blueprint $resource) {
            $resource->primaryKey('title', 'string');
        });
        $blueprint->getDriver()->run($blueprint);
        $this->assertColumnExists('posts', 'title');
    }

    function test__run()
    {
        Builder::create('core.posts', function (Blueprint $resource) {
            $resource->id();

            $resource->text('title');

            $resource->belongsTo('testing.users', 'user')
                     ->foreignKey('user_id')
                     ->ownerKey('id');
        });

//        $blueprint->getDriver()->run($blueprint);

        $this->assertTableExists('posts');
        $this->assertColumnExists('posts', 'id');
        $this->assertColumnExists('posts', 'title');
        $this->assertColumnExists('posts', 'user_id');

    }
}