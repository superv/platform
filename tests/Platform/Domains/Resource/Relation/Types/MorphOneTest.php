<?php

namespace Tests\Platform\Domains\Resource\Relation\Types;

use SuperV\Platform\Domains\Database\Model\Repository;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class MorphOneTest extends ResourceTestCase
{
    /** @test */
    function create_morph_one_relation()
    {
        $parentResource = $this->create('t_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->morphOne('t_tags', 'tag', 'owner');
        });

        $this->create('t_tags', function (Blueprint $table) {
            $table->increments('id');
            $table->string('label');
        });

        $this->assertColumnDoesNotExist('t_users', 'address');
        $this->assertColumnDoesNotExist('t_users', 'address_id');

        $relation = $parentResource->getRelation('tag');
        $this->assertEquals('morph_one', $relation->getType());
        $this->assertEquals([
            'related_resource' => 't_tags',
            'morph_name'       => 'owner',
        ], $relation->getConfig()->toArray());
    }

    /** @test */
    function return_none_eloquent_model_if_provided()
    {
        $users = $this->create('t_users', function (Blueprint $table) {
            $table->increments('id');
            $table->morphOne('t_profiles', 'profile', 'owner', TestProfileRepository::class);
        });

        $this->create('t_profiles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->morphTo('owner');
        });

        $this->assertEquals(TestProfileRepository::class, $users->getRelation('profile')->getConfig()->getTargetModel());

        $user = $users->create();

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



























