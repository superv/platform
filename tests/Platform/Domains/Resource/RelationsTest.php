<?php

namespace Tests\Platform\Domains\Resource;

use Lakcom\Modules\Core\Domains\Address\Address;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Schema\Schema;
use SuperV\Platform\Domains\Resource\Relation\Table\RelationTableConfig;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\Table\Table;
use Tests\Platform\Domains\Resource\Fixtures\TestPost;
use Tests\Platform\Domains\Resource\Fixtures\TestRole;

class RelationsTest extends ResourceTestCase
{
    /** @test */
    function creates_belongs_to_relations()
    {
        Schema::create('t_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->belongsTo('t_groups', 'group');
        });

        $users = Resource::of('t_users');
        $this->assertColumnDoesNotExist('t_users', 'posts');

        $relation = $users->getRelation('group');
        $this->assertEquals('belongs_to', $relation->getType());

        $this->assertEquals([
            'related_resource' => 't_groups',
            'foreign_key'      => 'group_id',
        ], $relation->getConfig()->toArray());
    }

    /** @test */
    function create_has_one_relation()
    {
        Schema::create('t_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->hasOne('t_profiles', 'profile', 'user_id');
        });

        Schema::create('t_profiles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('address');
            $table->belongsTo('t_users', 'user', 'user_id');
        });

        $users = Resource::of('t_users');
        $this->assertColumnDoesNotExist('t_users', 'profile');
        $this->assertColumnDoesNotExist('t_users', 'user_id');

        $relation = $users->getRelation('profile');
        $this->assertEquals('has_one', $relation->getType());

        $this->assertEquals([
            'related_resource' => 't_profiles',
            'foreign_key'      => 'user_id',
        ], $relation->getConfig()->toArray());
    }

    /** @test */
    function creates_has_many_relations()
    {
        Schema::create('t_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->hasMany(TestPost::class, 'posts', 'user_id', 'post_id');
        });

        $users = Resource::of('t_users');
        $this->assertColumnDoesNotExist('t_users', 'posts');

        $relation = $users->getRelation('posts');
        $this->assertEquals('has_many', $relation->getType());

        $this->assertEquals([
            'related_model' => TestPost::class,
            'foreign_key'   => 'user_id',
            'local_key'     => 'post_id',
        ], $relation->getConfig()->toArray());
    }

    /** @test */
    function creates_belongs_to_many_relations()
    {
        /** @test */
        Schema::create('t_users', function (Blueprint $table) {
            $table->increments('id');
            $table->belongsToMany(
                TestRole::class, 'roles', 't_user_roles', 'user_id', 'role_id',
                function (Blueprint $pivotTable) {
                    $pivotTable->string('status');
                });
        });

        $users = Resource::of('t_users');

        $this->assertColumnDoesNotExist('t_users', 'roles');
        $this->assertColumnsExist('t_user_roles', ['id', 'user_id', 'role_id', 'status', 'created_at', 'updated_at']);

        $relation = $users->getRelation('roles');
        $this->assertEquals('belongs_to_many', $relation->getType());

        $this->assertEquals([
            'related_model'     => TestRole::class,
            'pivot_table'       => 't_user_roles',
            'pivot_foreign_key' => 'user_id',
            'pivot_related_key' => 'role_id',
            'pivot_columns'     => ['status'],
        ], $relation->getConfig()->toArray());
    }

    /** @test */
    function creates_morph_to_many_relations()
    {
        Schema::create('t_users', function (Blueprint $table) {
            $table->increments('id');
            $pivotColumns = function (Blueprint $pivotTable) {
                $pivotTable->string('status');
            };
            $table->morphToMany(TestRole::class, 'roles', 'owner', 't_assigned_roles', 'role_id', $pivotColumns);
        });

        $users = Resource::of('t_users');

        $this->assertColumnDoesNotExist('t_users', 'roles');
        $this->assertColumnsExist('t_assigned_roles', ['id',
            'owner_type',
            'owner_id',
            'role_id',
            'status',
            'created_at',
            'updated_at']);

        $relation = $users->getRelation('roles');
        $this->assertEquals('morph_to_many', $relation->getType());

        $this->assertEquals([
            'related_model'     => TestRole::class,
            'pivot_table'       => 't_assigned_roles',
            'pivot_foreign_key' => 'owner_id',
            'pivot_related_key' => 'role_id',
            'morph_name'        => 'owner',
            'pivot_columns'     => ['status'],
        ], $relation->getConfig()->toArray());
    }

    /** @test */
    function create_morph_one_relation()
    {
        Schema::create('t_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->morphOne(Address::class, 'address', 'owner');
        });

        $users = Resource::of('t_users');
        $this->assertColumnDoesNotExist('t_users', 'address');
        $this->assertColumnDoesNotExist('t_users', 'address_id');

        $relation = $users->getRelation('address');
        $this->assertEquals('morph_one', $relation->getType());

        $this->assertEquals([
            'related_model' => Address::class,
            'morph_name'      => 'owner',
        ], $relation->getConfig()->toArray());
    }

    /** @test */
    function creates_table_from_has_many()
    {
        Schema::create('t_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->hasMany('t_posts', 'posts', 't_user_id');
        });
        Schema::create('t_posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->belongsTo('t_users', 't_user');
        });

        $users = Resource::of('t_users');
        $posts = Resource::of('t_posts');

        $user = $users->loadFake();
        $posts->createFake(['t_user_id' => $user->getEntryId()], 5);
        $posts->createFake(['t_user_id' => 999], 3); // these should be excluded

        $relation = $user->getRelation('posts');

        $tableConfig = new RelationTableConfig($relation);
        $tableConfig->build();

        $table = Table::config($tableConfig)->build();

        $this->assertEquals(8, \DB::table('t_posts')->count());
        $this->assertEquals(5, $table->getRows()->count());
    }


    /** @test */
    function saves_pivot_columns_even_if_pivot_table_is_created_before()
    {
        Schema::create('t_users', function (Blueprint $table) {
            $table->increments('id');
            $pivotColumns = function (Blueprint $pivotTable) {
                $pivotTable->string('status');
            };
            $table->morphToMany(TestRole::class, 'roles', 'owner', 't_assigned_roles', 'role_id', $pivotColumns);
        });

        $users = Resource::of('t_users');
        $roles = $users->getRelation('roles');
        $this->assertEquals(['status'], $roles->getConfig()->getPivotColumns());

        Schema::create('t_admins', function (Blueprint $table) {
            $table->increments('id');
            $pivotColumns = function (Blueprint $pivotTable) {
                $pivotTable->string('status');
            };
            $table->morphToMany(TestRole::class, 'roles', 'owner', 't_assigned_roles', 'role_id', $pivotColumns);
        });

        $admins = Resource::of('t_admins');
        $roles = $admins->getRelation('roles');
        $this->assertEquals(['status'], $roles->getConfig()->getPivotColumns());
    }
}
