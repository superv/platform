<?php

namespace Tests\Platform\Domains\Resource\Relation\Types;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Contracts\AcceptsParentEntry;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesForm;
use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Domains\Resource\Testing\FormTester;
use Tests\Platform\Domains\Resource\ResourceTestCase;

/**
 * Class HasOneTest
 *
 * @package Tests\Platform\Domains\Resource\Relation\Types
 * @group   resource
 */
class HasOneTest extends ResourceTestCase
{
    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $parent;

    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $related;

    function test__creates_relation()
    {
        $this->assertColumnNotExists('tbl_users', 'profile');
        $this->assertColumnNotExists('tbl_users', 'user_id');

        /** @var \SuperV\Platform\Domains\Resource\Relation\Types\HasOne $relation */
        $relation = $this->parent->getRelation('profile');
        $this->assertEquals('has_one', $relation->getType());

        $this->assertEquals([
            'related_resource' => 'testing.profiles',
            'foreign_key'      => 'user_id',
        ], $relation->getRelationConfig()->toArray());
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
        $form = $relation->makeForm()->resolve();
        $this->assertInstanceOf(Form::class, $form);
        $this->assertEquals(2, $form->fields()->count());
        $this->assertFalse($form->getField('user')->isVisible());

        $relatedEntry = $form->getEntry();
        $this->assertEquals($user->id, $relatedEntry->user_id);

        $this->withoutExceptionHandling();
        (new FormTester($this->basePath()))->test($form);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->parent = $this->blueprints()->users(function (Blueprint $table) {
            $table->hasOne('testing.profiles', 'profile', 'user_id');
        });

        $this->related = $this->create('tbl_profiles', function (Blueprint $table) {
            $table->resourceConfig()->setIdentifier('testing.profiles');

            $table->increments('id');
            $table->string('address');
            $table->belongsTo('testing.users', 'user', 'user_id');
        });
    }
}




























