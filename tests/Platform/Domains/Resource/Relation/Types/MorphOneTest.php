<?php

namespace Tests\Platform\Domains\Resource\Relation\Types;

use Illuminate\Database\Eloquent\Model;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Form\Form;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class MorphOneTest extends ResourceTestCase
{
    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $parentResource;

    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $relatedResource;

    protected function setUp()
    {
        parent::setUp();

        $this->parentResource = $this->create('t_users',function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->morphOne('t_tags', 'tag', 'owner');
        });

        $this->relatedResource = $this->create('t_tags',function (Blueprint $table) {
            $table->increments('id');
            $table->string('label');
        });
    }

    /** @test */
    function create_morph_one_relation()
    {
        $this->assertColumnDoesNotExist('t_users', 'address');
        $this->assertColumnDoesNotExist('t_users', 'address_id');

        $relation = $this->parentResource->getRelation('tag');
        $this->assertEquals('morph_one', $relation->getType());
        $this->assertEquals([
            'related_resource' => 't_tags',
            'morph_name'    => 'owner',
        ], $relation->getConfig()->toArray());
    }

    /** @test */
    function makes_form()
    {
        $relation = $this->parentResource->getRelation('tag');

        $form = $relation->makeForm();
        $this->assertInstanceOf(Form::class, $form);
    }

    public function makeResources(): \SuperV\Platform\Domains\Resource\Resource
    {



    }
}

class MorphOneTestClass extends Model
{
}