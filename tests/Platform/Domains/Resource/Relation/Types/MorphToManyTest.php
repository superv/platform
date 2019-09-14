<?php

namespace Tests\Platform\Domains\Resource\Relation\Types;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use Tests\Platform\Domains\Resource\Fixtures\TestRole;
use Tests\Platform\Domains\Resource\ResourceTestCase;

/**
 * Class MorphToManyTest
 *
 * @package Tests\Platform\Domains\Resource\Relation\Types
 * @group   resource
 */
class MorphToManyTest extends ResourceTestCase
{
    function test__create_morph_to_many_relation()
    {
        $this->create('t_users', function (Blueprint $table) {
            $table->increments('id');
            $pivotColumns = function (Blueprint $pivotTable) {
                $pivotTable->string('status');
            };
            $table->morphToMany(TestRole::class, 'roles', 'owner', 't_assigned_roles', 'role_id', $pivotColumns);
        });

        $users = ResourceFactory::make('platform::t_users');

        $this->assertColumnNotExists('t_users', 'roles');
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
        ], $relation->getRelationConfig()->toArray());
    }
}




























