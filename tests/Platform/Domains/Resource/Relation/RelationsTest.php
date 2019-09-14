<?php

namespace Tests\Platform\Domains\Resource\Relation;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use Tests\Platform\Domains\Resource\Fixtures\TestPost;
use Tests\Platform\Domains\Resource\Fixtures\TestRole;
use Tests\Platform\Domains\Resource\ResourceTestCase;

/**
 * Class RelationsTest
 *
 * @package Tests\Platform\Domains\Resource\Relation
 * @group   resource
 */
class RelationsTest extends ResourceTestCase
{
    function test__creates_belongs_to_relations()
    {
        $groups = $this->create('t_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->entryLabel();
        });

        $users = $this->create('t_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->belongsTo('platform::t_groups', 'group');
        });

        $this->assertColumnNotExists('t_users', 'posts');

        $relation = $users->getRelation('group');
        $this->assertEquals('belongs_to', $relation->getType());

        $this->assertEquals([
            'related_resource' => 'platform::t_groups',
            'foreign_key'      => 'group_id',
        ], $relation->getRelationConfig()->toArray());
    }

    function test__creates_has_many_relations()
    {
        $this->create('t_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->hasMany(TestPost::class, 'posts', 'user_id', 'post_id');
        });

        $users = ResourceFactory::make('platform::t_users');
        $this->assertColumnNotExists('t_users', 'posts');

        $relation = $users->getRelation('posts');
        $this->assertEquals('has_many', $relation->getType());

        $this->assertEquals([
            'related_model' => TestPost::class,
            'foreign_key'   => 'user_id',
            'local_key'     => 'post_id',
        ], $relation->getRelationConfig()->toArray());
    }

    function test__creates_belongs_to_many_relations()
    {
        $this->create('t_users', function (Blueprint $table) {
            $table->increments('id');
            $table->belongsToMany(TestRole::class, 'roles')
                  ->pivotTable('t_user_roles')
                  ->pivotForeignKey('user_id')
                  ->pivotRelatedKey('role_id')
                  ->pivotColumns(
                      function (Blueprint $pivotTable) {
                          $pivotTable->string('status');
                      });
        });

        $users = ResourceFactory::make('platform::t_users');

        $this->assertColumnNotExists('t_users', 'roles');
        $this->assertColumnsExist('t_user_roles', ['id', 'user_id', 'role_id', 'status', 'created_at', 'updated_at']);

        $relation = $users->getRelation('roles');
        $this->assertEquals('belongs_to_many', $relation->getType());

        $this->assertEquals([
            'related_model'     => TestRole::class,
            'pivot_table'       => 't_user_roles',
            'pivot_foreign_key' => 'user_id',
            'pivot_related_key' => 'role_id',
            'pivot_columns'     => ['status'],
        ], $relation->getRelationConfig()->toArray());
    }

    function test__saves_pivot_columns_even_if_pivot_table_is_created_before()
    {
        $this->create('t_users', function (Blueprint $table) {
            $table->increments('id');
            $pivotColumns = function (Blueprint $pivotTable) {
                $pivotTable->string('status');
            };
            $table->morphToMany(TestRole::class, 'roles', 'owner', 't_assigned_roles', 'role_id', $pivotColumns);
        });

        $users = ResourceFactory::make('platform::t_users');
        $roles = $users->getRelation('roles');
        $this->assertEquals(['status'], $roles->getRelationConfig()->getPivotColumns());

        $this->create('t_admins', function (Blueprint $table) {
            $table->increments('id');
            $pivotColumns = function (Blueprint $pivotTable) {
                $pivotTable->string('status');
            };
            $table->morphToMany(TestRole::class, 'roles', 'owner', 't_assigned_roles', 'role_id', $pivotColumns);
        });

        $admins = ResourceFactory::make('platform::t_admins');
        $roles = $admins->getRelation('roles');
        $this->assertEquals(['status'], $roles->getRelationConfig()->getPivotColumns());
    }
}
