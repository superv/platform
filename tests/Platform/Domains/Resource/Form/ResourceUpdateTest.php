<?php

namespace Tests\Platform\Domains\Resource\Form;

use Storage;
use SuperV\Platform\Domains\Media\Media;
use Tests\Platform\Domains\Resource\Http\Controllers\ResponseHelper;
use Tests\Platform\Domains\Resource\ResourceTestCase;

/**
 * Class ResourceUpdateTest
 *
 * @package Tests\Platform\Domains\Resource\Http\Controllers
 * @group   resource
 * @group   http
 */
class ResourceUpdateTest extends ResourceTestCase
{
    use ResponseHelper;

    function test__updates_all_required()
    {
        $users = $this->blueprints()->users();
        $user = $users->fake(['group_id' => 1]);

        $post = [
            'name'  => 'Ali',
            'email' => 'ali@superv.io',
            'group' => 2,
        ];
        $response = $this->postJsonUser($user->router()->updateForm(), $post);
//        dd($response->decodeResponseJson());
        $response->assertOk();

        $user = $user->fresh();

        $this->assertEquals('Ali', $user->name);
        $this->assertEquals('ali@superv.io', $user->email);
        $this->assertEquals(2, $user->group_id);
    }

    function test__updates_some_required()
    {
        $users = $this->blueprints()->users();
        $user = $users->fake();

        $this->postJsonUser($user->router()->updateForm(), ['name' => 'Ali'])->assertOk();
        $this->postJsonUser($user->router()->updateForm(), ['email' => 'ali@superv.io'])->assertOk();
    }

    function test__fails_on_unique_validation()
    {
        $this->withExceptionHandling();
        $users = $this->blueprints()->users();
        $users->fake(['email' => 'ali@superv.io']);

        $user = $users->fake();
        $response = $this->postJsonUser($user->router()->updateForm(), ['email' => 'ali@superv.io']);
        $response->assertStatus(422);

        $this->assertEquals(['email'], array_keys($response->decodeResponseJson('errors')));
    }

    function test__fails_on_nullable_validation()
    {
        $users = $this->blueprints()->users();
        $user = $users->fake();

        $response = $this->postJsonUser($user->router()->updateForm(), ['name' => null]);
        $response->assertStatus(422);

        $this->assertEquals(['name'], array_keys($response->decodeResponseJson('errors')));
    }

    function test__uploads_files()
    {
        Storage::fake('fakedisk');
        $users = $this->blueprints()->users();
        $user = $users->fake();

        $this->withoutExceptionHandling();
        $response = $this->postJsonUser($user->router()->updateForm(), ['avatar' => $this->makeUploadedFile()]);
        $response->assertOk();

        $this->assertNotNull(Media::first());

//        $view = $this->getResourceView($user);
//        $avatar = $view->getProp('fields.avatar');
//        $this->assertNull($avatar);

    }
}

