<?php

namespace Tests\Platform\Domains\Resource\Http\Controllers;

use Illuminate\Http\Request;
use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Domains\Resource\Resource;
use Tests\Platform\Domains\Resource\Fixtures\HelperComponent;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class RelationCreateTest extends ResourceTestCase
{
    function test__display_standard_form()
    {
        $users = $this->schema()->users();
        $this->schema()->comments();
        $user = $users->fake();
        $relation = $users->getRelation('comments');

        $response = $this->getJsonUser($relation->route('create', $user))->assertOk();
        $form = HelperComponent::from($response->decodeResponseJson('data'));

        $this->assertEquals(2, count($form->getProp('fields')));
        $this->assertEquals(sv_url($relation->route('store', $user)), $form->getProp('url'));

        $this->assertEquals('comment', $form->getProp('fields.0.name'));
        $this->assertEquals('status', $form->getProp('fields.1.name'));
    }

    function test__post_standard_form()
    {
        $users = $this->schema()->users();
        $this->schema()->comments();
        $user = $users->fake();
        $relation = $users->getRelation('comments');

        $response = $this->postJsonUser(
            $relation->route('store', $user),
            ['comment' => 'abc 123', 'status' => 'approved']
        )->assertOk();

        $this->assertEquals('ok', $response->decodeResponseJson('status'));

        $comment = $user->comments()->first();
        $this->assertEquals($user->getId(), $comment->user_id);
        $this->assertEquals('abc 123', $comment->comment);
        $this->assertEquals('approved', $comment->status);
    }

    function test__display_extended_form()
    {
        $users = $this->schema()->users();
        $this->schema()->comments();

        Resource::extend('t_users')->with(function (Resource $resource) {
            $resource->getRelation('comments')
                     ->on('create.displaying', function (Form $form) {
                         $form->hideField('status');
                     });;
        });

        $user = $users->fake();
        $relation = $users->getRelation('comments');

        $response = $this->getJsonUser($relation->route('create', $user))->assertOk();
        $form = HelperComponent::from($response->decodeResponseJson('data'));

        $this->assertEquals(1, count($form->getProp('fields')));
        $this->assertEquals(sv_url($relation->route('store', $user)), $form->getProp('url'));

        $this->assertEquals('comment', $form->getProp('fields.0.name'));
    }

    function test__post_extended_form()
    {
        $users = $this->schema()->users();
        $this->schema()->comments();

        Resource::extend('t_users')->with(function (Resource $resource) {
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

        $this->assertEquals('ok', $response->decodeResponseJson('status'));

        $comment = $user->comments()->first();
        $this->assertEquals($user->getId(), $comment->user_id);
        $this->assertEquals('abc 123 (by dali)', $comment->comment);
        $this->assertEquals('pending', $comment->status);
    }
}