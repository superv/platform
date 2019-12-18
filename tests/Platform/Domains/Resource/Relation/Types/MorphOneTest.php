<?php

namespace Tests\Platform\Domains\Resource\Relation\Types;

use SuperV\Platform\Domains\Database\Model\Entry;
use SuperV\Platform\Domains\Database\Model\Repository;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Contracts\AcceptsParentEntry;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesForm;
use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Domains\Resource\ResourceConfig;
use SuperV\Platform\Domains\Resource\Testing\FormTester;
use Tests\Platform\Domains\Resource\ResourceTestCase;

/**
 * Class MorphOneTest
 *
 * @package Tests\Platform\Domains\Resource\Relation\Types
 * @group   resource
 */
class MorphOneTest extends ResourceTestCase
{
    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $parent;

    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $related;

    function test__create_morph_one_relation()
    {
        $this->assertColumnNotExists('tbl_users', 'address');
        $this->assertColumnNotExists('tbl_users', 'address_id');

        $relation = $this->parent->getRelation('tag');
        $this->assertEquals('morph_one', $relation->getType());
        $this->assertEquals([
            'related_resource' => 'testing.t_tags',
            'morph_name'       => 'owner',
        ], $relation->getRelationConfig()->toArray());
    }

    function test__makes_form()
    {
        $user = $this->parent->fake();

        $tag = $user->tag()->make(['label' => 'blue']);
        $this->assertEquals($user->getId(), $tag->owner_id);
        $this->assertEquals($user->getResourceIdentifier(), $tag->owner_type);

        /** @var \SuperV\Platform\Domains\Resource\Relation\Types\MorphOne $relation */
        $relation = $this->parent->getRelation('tag');
        $this->assertInstanceOf(ProvidesForm::class, $relation);
        $this->assertInstanceOf(AcceptsParentEntry::class, $relation);
        $relation->acceptParentEntry($user);

        /** @var Form $form */
        $form = $relation->makeForm()->resolve();
        $this->assertInstanceOf(Form::class, $form);
        $this->assertNull($form->getField('user'));
        $this->assertNull($form->getField('label')->getComposer()->toForm($form)->get('value'));
//        $this->assertNull((new FieldComposer($form->getField('label')))->forForm($form)->get('value'));

        $relatedEntry = $form->getEntry();
        $this->assertEquals($user->getId(), $relatedEntry->owner_id);
        $this->assertEquals($user->getResourceIdentifier(), $relatedEntry->owner_type);

        $this->withoutExceptionHandling();
        (new FormTester($this->basePath()))->test($form);
    }

    function test__makes_form_custom_model()
    {
        $user = $this->parent->fake();
        $relationQuery = $user->tac();

        $tac = $relationQuery->make(['label' => 'blue']);
        $this->assertInstanceOf(TestTac::class, $tac);

        /** @var \SuperV\Platform\Domains\Resource\Relation\Types\MorphOne $relation */
        $relation = $this->parent->getRelation('tac');
        $relation->acceptParentEntry($user);

        /** @var Form $form */
        $form = $relation->makeForm()->resolve();
        $relatedEntry = $form->getEntry();
        $this->assertInstanceOf(TestTac::class, $relatedEntry);

        $this->assertEquals($user->getId(), $relatedEntry->owner_id);
        $this->assertEquals($user->getResourceIdentifier(), $relatedEntry->owner_type);

        $this->withoutExceptionHandling();
        (new FormTester($this->basePath()))->test($form);
    }

    function test__return_none_eloquent_model_if_provided()
    {
        $this->create('t_profiles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->morphTo('owner');
        });

        $this->assertEquals(TestProfileRepository::class, $this->parent->getRelation('profile')->getRelationConfig()->getTargetModel());

        $user = $this->parent->create(['name' => 'some']);

        $user->profile()->make(['title' => 'Admin'])->save();

        $profile = $user->fresh()->getProfile();

        $this->assertInstanceOf(TestProfile::class, $profile);

        $this->assertEquals('Admin', $profile->entry->title);
        $this->assertEquals($user->getId(), $profile->entry->owner_id);
        $this->assertEquals($user->getMorphClass(), $profile->entry->owner_type);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->parent = $this->create('tbl_users', function (Blueprint $table, ResourceConfig $config) {
            $config->resourceKey('user');

            $table->increments('id');
            $table->string('name');
            $table->morphOne('testing.t_tags', 'tag', 'owner');
            $table->morphOne('testing.t_tacs', 'tac', 'owner');
            $table->morphOne('testing.t_profiles', 'profile', 'owner', TestProfileRepository::class);
        });

        $this->related = $this->create('t_tags', function (Blueprint $table) {
            $table->increments('id');
            $table->string('label');
            $table->morphTo('owner');
        });

        $this->create('t_tacs', function (Blueprint $table, ResourceConfig $config) {
            $config->model(TestTac::class);

            $table->increments('id');
            $table->string('label');
            $table->morphTo('owner');
        });
    }
}

class TestTac extends Entry
{
    protected $table = 't_tacs';

    public $timestamps = false;
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



























