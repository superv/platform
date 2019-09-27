<?php

namespace Tests\Platform\Domains\Resource\Relation\Types;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use Tests\Platform\Domains\Resource\ResourceTestCase;

/**
 * Class MorphToTest
 *
 * @package Tests\Platform\Domains\Resource\Relation\Types
 * @group   resource
 */
class MorphToTest extends ResourceTestCase
{
    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $parentResource;

    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $relatedResource;

    function test__create_morph_to_relation()
    {
        $this->assertColumnExists('t_files', 'owner_type');
        $this->assertColumnExists('t_files', 'owner_id');

        $relation = $this->relatedResource->getRelation('owner');
        $this->assertEquals('morph_to', $relation->getType());
        $this->assertEquals(['morph_name' => 'owner'], $relation->getRelationConfig()->toArray());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->parentResource = $this->create('tbl_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->morphOne('t_files', 'avatar', 'owner');
        });

        $this->relatedResource = $this->create('t_files', function (Blueprint $table) {
            $table->increments('id');
            $table->morphTo('owner');
        });
    }
}
