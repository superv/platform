<?php

namespace Tests\Platform\Domains\Resource\Builder;

use SuperV\Platform\Domains\Database\Schema\SchemaService;
use SuperV\Platform\Domains\Resource\Builder\Blueprint;
use SuperV\Platform\Domains\Resource\Builder\Builder;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class DatabaseDriverTest extends ResourceTestCase
{
    function test__primary_keys()
    {
        Builder::create('sv.testing.posts', function (Blueprint $resource) {
            $resource->primaryKey('title')->text();
        });
        $this->assertColumnExists('posts', 'title');
    }

    function test__column_options()
    {
        Builder::create('sv.testing.posts', function (Blueprint $resource) {
            $resource->text('title')->nullable();
            $resource->number('views')->default(3);
        });

        $column = SchemaService::resolve()->getColumn('posts', 'title');
        $this->assertTrue($column->isNullable());

        $column = SchemaService::resolve()->getColumn('posts', 'views');
        $this->assertEquals(3, $column->getDefaultValue());
    }

    function test__run()
    {
        Builder::create('sv.testing.posts', function (Blueprint $resource) {
            $resource->id();
        });

        $this->assertTableExists('posts');
        $this->assertColumnExists('posts', 'id');

        Builder::create('sv.testing.authors', function (Blueprint $resource) {
            $resource->id('author_id');
        });

        $this->assertColumnExists('authors', 'author_id');
        $this->assertColumnNotExists('authors', 'id');
    }
}