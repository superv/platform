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
        $this->create('sv.testing.abusers', function (Blueprint $table) {
            $table->increments('id');

            $table->morphToMany(TestRole::class, 'roles', 'owner')
                  ->pivotTable('sv.testing.assigned_roles')
                  ->pivotRelatedKey('role_id')
                  ->pivotColumns(function (Blueprint $pivotTable) {
                      $pivotTable->string('status');
                  });
        });

        $abusers = ResourceFactory::make('sv.testing.abusers');

        $this->assertColumnNotExists('abusers', 'roles');
        $this->assertColumnsExist('assigned_roles', ['id',
                                                       'owner_type',
                                                       'owner_id',
                                                       'role_id',
                                                       'status',
                                                       'created_at',
                                                       'updated_at']);

        $relation = $abusers->getRelation('roles');
        $this->assertEquals('morph_to_many', $relation->getType());

        $this->assertEquals([
            'related_model'     => TestRole::class,
            'pivot_table'       => 'assigned_roles',
            'pivot_foreign_key' => 'owner_id',
            'pivot_related_key' => 'role_id',
            'morph_name'        => 'owner',
            'pivot_columns'     => ['status'],
            'pivot_namespace'   => 'sv.testing',
            'pivot_identifier'  => 'sv.testing.assigned_roles',
        ], $relation->getRelationConfig()->toArray());
    }
}




























