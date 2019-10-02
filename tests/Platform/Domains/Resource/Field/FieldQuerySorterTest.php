<?php

namespace Tests\Platform\Domains\Resource\Field;

use SuperV\Platform\Domains\Resource\Field\FieldQuerySorter;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class FieldQuerySorterTest extends ResourceTestCase
{
    function test__regular()
    {
        $posts = $this->blueprints()->posts();

        /** @var \Illuminate\Database\Eloquent\Builder $postsQuery */
        $postsQuery = $posts->newQuery();

        $sorter = new FieldQuerySorter();
        $sorter->setQuery($postsQuery);
        $sorter->setField($titleField = $posts->getField('title'));
        $sorter->sort('desc');
        $this->assertEmpty($postsQuery->getQuery()->joins);

        $this->assertNotNull($postsQuery->getQuery()->orders);

        $this->assertEquals([
            "column"    => 'title',
            "direction" => "desc",
        ], $postsQuery->getQuery()->orders[0]);
    }

    function test__belongs_to()
    {
        $users = $this->blueprints()->users();
        $posts = $this->blueprints()->posts();

        /** @var \Illuminate\Database\Eloquent\Builder $postsQuery */
        $postsQuery = $posts->newQuery();

        $sorter = new FieldQuerySorter();
        $sorter->setQuery($postsQuery);
        $sorter->setField($userField = $posts->getField('user'));
        $sorter->sort('asc');

        /** @var \Illuminate\Database\Query\JoinClause $join */
        $join = $postsQuery->getQuery()->joins[0];
        $this->assertNotNull($join);
        $this->assertEquals('tbl_users AS tbl_users_1', $join->table);
        $this->assertEquals([[
                                 'type'     => 'Column',
                                 'first'    => 'tbl_users_1.id',
                                 'operator' => '=',
                                 'second'   => 'tbl_posts.user_id',
                                 'boolean'  => 'and',
                             ]], $join->wheres);

        $usersTable = $users->config()->getTable();
        $this->assertEquals([
            "column"    => $usersTable.'_1.'.$users->fields()->getEntryLabelField()->getColumnName(),
            "direction" => "asc",
        ], $postsQuery->getQuery()->orders[0]);
    }
}
