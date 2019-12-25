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
        Builder::create('tst.posts', function (Blueprint $resource) {
            $resource->id();
        });

        $this->assertTableExists('posts');
        $this->assertColumnExists('posts', 'id');

        Builder::create('tst.authors', function (Blueprint $resource) {
            $resource->id('author_id');
        });

        $this->assertColumnExists('authors', 'author_id');
        $this->assertColumnNotExists('authors', 'id');
    }
}