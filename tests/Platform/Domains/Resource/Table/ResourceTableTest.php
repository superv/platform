<?php

namespace Tests\Platform\Domains\Resource\Table;

use Illuminate\Http\Request;
use SuperV\Platform\Domains\Resource\Field\FieldQuerySorter;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class ResourceTableTest extends ResourceTestCase
{
    function test__relational_order_by()
    {
        $posts = $this->schema()->posts();
        $table = $posts->resolveTable();

        $sorter = $this->bindMock(FieldQuerySorter::class);
        $sorter->shouldReceive('setQuery')->with($table->getQuery())->once();
        $sorter->shouldReceive('setField')->with($posts->getField('user'))->once();
        $sorter->shouldReceive('sort')->with('desc')->once();

        $table->setRequest(new Request(['order_by' => 'user:desc']));
        $table->build();
    }

    function test__default_from_entry_label_field()
    {
        $posts = $this->schema()->posts();
        $table = $posts->resolveTable();
        $table->setRequest(new Request());
        $table->build();

        $this->assertEquals([
            'column'    => 'title',
            'direction' => 'asc',
        ], $table->getQuery()->getQuery()->orders[0]);
    }

    function test__override_from_table_options()
    {
        $posts = $this->schema()->posts();
        $table = $posts->resolveTable();
        $table->setOption('order_by', ['created_at' => 'ASC']);

        $table->setRequest(new Request());
        $table->build();

        $this->assertEquals([
            'column'    => 't_posts.created_at',
            'direction' => 'asc',
        ], $table->getQuery()->getQuery()->orders[0]);
    }
}
