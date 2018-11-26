<?php

namespace Tests\Platform\Domains\Resource\Http\Controllers;

use Storage;
use Tests\Platform\Domains\Resource\Fixtures\HelperComponent;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class ResourceUpdateTest extends ResourceTestCase
{
    use ResponseHelper;

    function test__updates_all_required()
    {
        $user = $this->schema()->users()->fake(['group_id' => 1]);

        $post = [
            'name'     => 'Ali',
            'email'    => 'ali@superv.io',
            'group_id' => 2,
        ];
        $response = $this->postJsonUser($user->route('update'), $post);
        $response->assertOk();

        $user = $user->fresh();

        $this->assertEquals('Ali', $user->name);
        $this->assertEquals('ali@superv.io', $user->email);
        $this->assertEquals(2, $user->group_id);
    }

    function test__updates_some_required()
    {
        $user = $this->schema()->users()->fake();

        $this->postJsonUser($user->route('update'), ['name' => 'Ali'])->assertOk();
        $this->postJsonUser($user->route('update'), ['email' => 'ali@superv.io'])->assertOk();
    }

    function test__fails_on_unique_validation()
    {
        $this->withExceptionHandling();
        $users = $this->schema()->users();
        $users->fake(['email' => 'ali@superv.io']);

        $user = $users->fake();
        $response = $this->postJsonUser($user->route('update'), ['email' => 'ali@superv.io']);
        $response->assertStatus(422);

        $this->assertEquals(['email'], array_keys($response->decodeResponseJson('errors')));
    }

    function test__fails_on_nullable_validation()
    {
        $users = $this->schema()->users();
        $user = $users->fake();

        $response = $this->postJsonUser($user->route('update'), ['name' => null]);
        $response->assertStatus(422);

        $this->assertEquals(['name'], array_keys($response->decodeResponseJson('errors')));
    }

    function test__uploads_files()
    {
        Storage::fake('fakedisk');
        $users = $this->schema()->users();
        $user = $users->fake();

        $this->withoutExceptionHandling();
        $response = $this->postJsonUser($user->route('update'), ['avatar' => $this->makeUploadedFile()]);
        $response->assertOk();


        $view = $this->getResourceView($user);
        $avatar = $view->getProp('fields.avatar');

        $this->assertNotNull($avatar['config']['url']);
    }
}

