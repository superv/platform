<?php

namespace Tests\Platform\Domains\Resource\Relation\Types;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesTable;
use SuperV\Platform\Domains\Resource\Relation\Types\HasMany;
use SuperV\Platform\Domains\Resource\Table\Table;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class HasManyTest extends ResourceTestCase
{
    /** @test */
    function creates_table_from_has_many()
    {
        $users = $this->create('t_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->hasMany('t_posts', 'posts', 't_user_id');
        });
        $posts = $this->create('t_posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->belongsTo('t_users', 't_user');
        });

//        $userEntry = ResourceEntry::fake($users);
        $userEntry = $users->fake();

        $posts->fake(['t_user_id' => $userEntry->getId()], 5);
        $posts->fake(['t_user_id' => 999], 3); // these should be excluded

        /** @var \SuperV\Platform\Domains\Resource\Relation\Types\HasMany $relation */
        $relation = $users->getRelation('posts', $userEntry);
        $this->assertInstanceOf(ProvidesTable::class, $relation);
        $this->assertInstanceOf(HasMany::class, $relation);

        /** @var \SuperV\Platform\Domains\Resource\Table\TableConfig $tableConfig */
        $tableConfig = $relation->makeTableConfig();
        // t_user column is not needed there
        $this->assertEquals(1, $tableConfig->getFields()->count());

        $table = Table::config($tableConfig)->build();
        $allPost = \DB::table('t_posts')->get();

        $this->assertEquals(8, $allPost->count());
        $this->assertEquals(5, $table->getRows()->count());
    }
}