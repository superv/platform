<?php

namespace Tests\Platform\Domains\Resource\Relation\Types;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Contracts\AcceptsParentEntry;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesForm;
use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Domains\Resource\ResourceBlueprint;
use SuperV\Platform\Domains\Resource\Testing\FormTester;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class HasOneTest extends ResourceTestCase
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
            $table->hasOne('t_profiles', 'profile', 'user_id');
        });

        $this->related = $this->create('t_profiles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('address');
            $table->belongsTo('t_users', 'user', 'user_id');
        });
    }

    function test__creates_relation()
    {
        $this->assertColumnDoesNotExist('t_users', 'profile');
        $this->assertColumnDoesNotExist('t_users', 'user_id');

        /** @var \SuperV\Platform\Domains\Resource\Relation\Types\HasOne $relation */
        $relation = $this->parent->getRelation('profile');
        $this->assertEquals('has_one', $relation->getType());

        $this->assertEquals([
            'related_resource' => 't_profiles',
            'foreign_key'      => 'user_id',
        ], $relation->getConfig()->toArray());
    }

    function test__makes_form()
    {
        $user = $this->parent->fake();
        $profile = $user->profile()->make();
        $this->assertNotNull($profile);

        /** @var \SuperV\Platform\Domains\Resource\Relation\Types\HasOne $relation */
        $relation = $this->parent->getRelation('profile', $user);
        $this->assertInstanceOf(ProvidesForm::class, $relation);
        $this->assertInstanceOf(AcceptsParentEntry::class, $relation);
        $relation->acceptParentEntry($user);

        /** @var Form $form */
        $form = $relation->makeForm();
        $this->assertInstanceOf(Form::class, $form);
        $this->assertEquals(2, $form->getFields()->count());
        $this->assertFalse($form->getField('user')->isVisible());

        $relatedEntry = $form->getWatcher();
        $this->assertEquals($user->id, $relatedEntry->user_id);

        $this->withoutExceptionHandling();
        (new FormTester($this->basePath()))->test($form);

    }
}




























