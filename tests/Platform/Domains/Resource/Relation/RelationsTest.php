<?php

namespace Tests\Platform\Domains\Resource\Relation;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\ResourceConfig;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use Tests\Platform\Domains\Resource\Fixtures\TestPost;
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

        $users = $this->create('tbl_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->belongsTo('sv.testing.groups', 'group');
        });

        $this->assertColumnNotExists('tbl_users', 'posts');

        $relation = $users->getRelation('group');
        $this->assertEquals('belongs_to', $relation->getType());

        $this->assertEquals([
            'related_resource' => 'sv.testing.groups',
            'foreign_key'      => 'group_id',
        ], $relation->getRelationConfig()->toArray());
    }

    function test__creates_has_many_relations()
    {
        $this->blueprints()->users(function (Blueprint $table) {
            $table->hasMany(TestPost::class, 'posts', 'user_id', 'post_id');
        });

        $users = ResourceFactory::make('sv.testing.users');
        $this->assertColumnNotExists('tbl_users', 'posts');

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
        $this->blueprints()->users();

        $users = ResourceFactory::make('sv.testing.users');

        $this->assertColumnNotExists('tbl_users', 'roles');
        $this->assertTableExists('tbl_assigned_roles');
        $this->assertColumnsExist('tbl_assigned_roles', ['id',
                                                         'user_id',
                                                         'role_id',
                                                         'status',
                                                         'created_at',
                                                         'updated_at']);

        $relation = $users->getRelation('roles');
        $this->assertEquals('belongs_to_many', $relation->getType());

        $this->assertEquals([
            'related_resource'  => 'sv.testing.roles',
            'pivot_table'       => 'tbl_assigned_roles',
            'pivot_foreign_key' => 'user_id',
            'pivot_related_key' => 'role_id',
            'pivot_columns'     => ['status'],
            'pivot_identifier'  => 'sv.testing.assigned_roles',
        ], $relation->getRelationConfig()->toArray());
    }

    function test__saves_pivot_columns_even_if_pivot_table_is_created_before()
    {
        $users = $this->blueprints()->users();

        $actions = $users->getRelation('actions');
        $this->assertEquals(['provision'], $actions->getRelationConfig()->getPivotColumns());

        $admins = $this->create('tbl_admins', function (Blueprint $table, ResourceConfig $config) {
            $config->setIdentifier('sv.testing.admins');

            $table->increments('id');
            $table->morphToMany('sv.testing.actions', 'actions', 'owner')
                  ->pivotTable('sv.testing.assigned_actions')
                  ->pivotRelatedKey('action_id')
                  ->pivotColumns(function (Blueprint $pivotTable) {
                      $pivotTable->string('provision');
                  });
        });

        $actions = $admins->getRelation('actions');
        $this->assertEquals(['provision'], $actions->getRelationConfig()->getPivotColumns());
    }
}
