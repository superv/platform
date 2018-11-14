<?php

namespace Tests\Platform\Domains\Resource\Relation\Types;

use SuperV\Platform\Domains\Database\Model\Repository;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Contracts\NeedsEntry;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesForm;
use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Domains\Resource\ResourceBlueprint;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class MorphOneTest extends ResourceTestCase
{
    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $parent;

    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $related;

    protected function setUp()
    {
        parent::setUp();

        $this->parent = $this->create('t_users', function (Blueprint $table, ResourceBlueprint $resource) {
            $resource->resourceKey('user');

            $table->increments('id');
            $table->string('name');
            $table->morphOne('t_tags', 'tag', 'owner');
            $table->morphOne('t_profiles', 'profile', 'owner', TestProfileRepository::class);
        });

        $this->related = $this->create('t_tags', function (Blueprint $table) {
            $table->increments('id');
            $table->string('label');
            $table->morphTo('owner');
        });
    }

    /** @test */
    function create_morph_one_relation()
    {
        $this->assertColumnDoesNotExist('t_users', 'address');
        $this->assertColumnDoesNotExist('t_users', 'address_id');

        $relation = $this->parent->getRelation('tag');
        $this->assertEquals('morph_one', $relation->getType());
        $this->assertEquals([
            'related_resource' => 't_tags',
            'morph_name'       => 'owner',
        ], $relation->getConfig()->toArray());
    }

    function test__makes_form()
    {
        $user = $this->parent->fake();
        $tag = $user->tag()->make(['label' => 'blue']);
        $this->assertNotNull($tag);
        $tag->save();

        $relation = $this->parent->getRelation('tag');
        $this->assertInstanceOf(ProvidesForm::class, $relation);
        $this->assertInstanceOf(NeedsEntry::class, $relation);
        $relation->setEntry($user);

        /** @var Form $form */
        $form = $relation->makeForm();
        $this->assertInstanceOf(Form::class, $form);
        $this->assertNull($form->getField('user'));

    }

    /** @test */
    function return_none_eloquent_model_if_provided()
    {
        $this->create('t_profiles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->morphTo('owner');
        });

        $this->assertEquals(TestProfileRepository::class, $this->parent->getRelation('profile')->getConfig()->getTargetModel());

        $user = $this->parent->create(['name' => 'some']);

        $user->profile()->make(['title' => 'Admin'])->save();

        $profile = $user->fresh()->getProfile();

        $this->assertInstanceOf(TestProfile::class, $profile);

        $this->assertEquals('Admin', $profile->entry->title);
        $this->assertEquals($user->id(), $profile->entry->owner_id);
        $this->assertEquals($user->getEntry()->getMorphClass(), $profile->entry->owner_type);
    }
}

class TestProfile
{
    public $entry;

    public function __construct($entry)
    {
        $this->entry = $entry;
    }
}

class TestProfileRepository implements Repository
{
    public function make($entry, $owner)
    {
//        return new TestProfile($entry);
    }

    public function resolve($entry, $owner)
    {
        return new TestProfile($entry);
    }
}



























