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
}
