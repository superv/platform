<?php

namespace Tests\Platform\Domains\Resource\Form;

use Illuminate\Http\Request;
use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Testing\HelperComponent;
use Tests\Platform\Domains\Resource\ResourceTestCase;

/**
 * Class RelationCreateTest
 *
 * @package Tests\Platform\Domains\Resource\Http\Controllers
 * @group   resource
 * @group   http
 */
class RelationCreateTest extends ResourceTestCase
{
    /**
     * @group http
     */
    function test__display_standard_form()
    {
        $this->withoutExceptionHandling();

        $users = $this->blueprints()->users();
        $this->blueprints()->comments();
        $user = $users->fake();
        $relation = $users->getRelation('comments');

        $response = $this->getJsonUser($relation->route('create', $user))->assertOk();
        $form = HelperComponent::fromArray($response->decodeResponseJson('data'));

        $this->assertEquals(2, count($form->getProp('fields')));
        $this->assertEquals(sv_url($relation->route('store', $user)), $form->getProp('url'));

        $this->assertEquals('comment', $form->getProp('fields.0.handle'));
        $this->assertEquals('status', $form->getProp('fields.1.handle'));
    }

    function test__post_standard_form()
    {
        $users = $this->blueprints()->users();

        $comments = $this->blueprints()->comments();
        $user = $users->fake();
        $relation = $users->getRelation('comments');

        $response = $this->postJsonUser(
            $relation->route('store', $user),
            ['comment' => 'abc 123', 'status' => 'approved']
        );

        $response->assertOk();

        $this->assertEquals($comments->first()->getId(), $response->decodeResponseJson('data.entry.id'));

        $comment = $user->comments()->first();
        $this->assertEquals($user->getId(), $comment->user_id);
        $this->assertEquals('abc 123', $comment->comment);
        $this->assertEquals('approved', $comment->status);
    }

    function test__display_extended_form()
    {
        $users = $this->blueprints()->users();
        $this->blueprints()->comments();
        ResourceFactory::wipe();

        Resource::extend('sv.testing.users', function (Resource $resource) {
            $resource->getRelation('comments')
                     ->on('create.displaying', function (Form $form) {
                         $form->fields()->hide('status');
                     });;
        });

        $user = $users->fake();
        $relation = $users->getRelation('comments');

        $response = $this->getJsonUser($relation->route('create', $user))->assertOk();
        $form = HelperComponent::fromArray($response->decodeResponseJson('data'));

        $this->assertEquals(1, count($form->getProp('fields')));
        $this->assertEquals(sv_url($relation->route('store', $user)), $form->getProp('url'));

        $this->assertEquals('comment', $form->getProp('fields.0.handle'));
    }

    function test__post_extended_form()
    {
        $users = $this->blueprints()->users();
        $comments = $this->blueprints()->comments();
        ResourceFactory::wipe();

        Resource::extend('sv.testing.users', function (Resource $resource) {
            $resource->getRelation('comments')
                     ->on('create.storing', function (Request $request, Form $form, $entry) {
                         $comment = $request->get('comment')." (by {$entry->name})";
                         $request->merge([
                             'comment' => $comment,
                             'status'  => 'pending',
                         ]);
                     });
        });

        $user = $users->fake(['name' => 'dali']);
        $relation = $users->getRelation('comments');

        $response = $this->postJsonUser(
            $relation->route('store', $user),
            ['comment' => 'abc 123', 'status' => 'approved']
        )->assertOk();

        $this->assertEquals($comments->first()->getId(), $response->decodeResponseJson('data.entry.id'));

        $comment = $user->comments()->first();
        $this->assertEquals($user->getId(), $comment->user_id);
        $this->assertEquals('abc 123 (by dali)', $comment->comment);
        $this->assertEquals('pending', $comment->status);
    }
}
