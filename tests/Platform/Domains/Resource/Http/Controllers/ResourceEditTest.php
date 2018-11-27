<?php

namespace Tests\Platform\Domains\Resource\Http\Controllers;

use Storage;
use SuperV\Platform\Domains\Media\Media;
use Tests\Platform\Domains\Resource\Fixtures\HelperComponent;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class ResourceEditTest extends ResourceTestCase
{
    use ResponseHelper;

    function test__form()
    {
        $this->withoutExceptionHandling();
        $user = $this->schema()->users()->fake(['group_id' => 1]);

        Storage::fake('fakedisk');
        $this->postJsonUser($user->route('update'), ['avatar' => $this->makeUploadedFile()]);

        $response = $this->getJsonUser($user->route('edit'))->assertOk();
        $form = HelperComponent::from($response->decodeResponseJson('data'));

        $this->assertEquals($user->route('update'), $form->getProp('url'));
        $this->assertEquals('post', $form->getProp('method'));

        // make sure fields is an array, not an object
        //
        $this->assertNotNull($form->getProp('fields.0'));

        $fields = collect($form->getProp('fields'))->keyBy('name');
        $this->assertEquals(6, $fields->count());

        $name = $fields->get('name');
        $this->assertNotNull($name['uuid']);
        $this->assertEquals('text', $name['type']);
        $this->assertEquals('name', $name['name']);
        $this->assertEquals('Name', $name['label']);
        $this->assertSame($user->name, $name['value']);

        $avatar = $fields->get('avatar');
        $this->assertEquals('file', $avatar['type']);
        $this->assertNull($avatar['value'] ?? null);
        $this->assertEquals(Media::first()->getUrl(), $avatar['image_url'] ?? null);
        $this->assertFalse(isset($avatar['config']));


    }
}

