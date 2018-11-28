<?php

namespace Tests\Platform\Domains\Resource\Http\Controllers;

use Storage;
use SuperV\Platform\Domains\Media\Media;
use SuperV\Platform\Domains\Resource\Extension\Extension;
use Tests\Platform\Domains\Resource\Fixtures\Extension\TestUserResourceExtension;
use Tests\Platform\Domains\Resource\Fixtures\HelperComponent;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class ResourceViewTest extends ResourceTestCase
{
    use ResponseHelper;

    function test__view_data()
    {
        $users = $this->schema()->users();
        $user = $users->fake(['name' => 'Ali Selcuk', 'age' => '40', 'group_id' => 1]);

        Storage::fake('fakedisk');
        $this->postJsonUser($user->route('update'), ['avatar' => $this->makeUploadedFile()]);

        $this->withoutExceptionHandling();
        $response = $this->getJsonUser($users->route('view', $user));
        $response->assertOk();

        $page = HelperComponent::from($response->decodeResponseJson('data'));
        $view = HelperComponent::from($page->getProp('blocks.0'));

        $this->assertNotNull($fields = $view->getProp('fields'));

        $name = $fields['name'];
        $this->assertNotNull($name['uuid']);
        $this->assertFalse(isset($name['config']));
        $this->assertEquals('text', $name['type']);
        $this->assertEquals('name', $name['name']);
        $this->assertEquals('Name', $name['label']);
        $this->assertSame('Ali Selcuk', $name['value']);

        $group = $fields['group'];
        $this->assertEquals('belongs_to', $group['type']);
        $this->assertSame('Users', $group['value']);

        $age = $fields['age'];
        $this->assertEquals('number', $age['type']);
        $this->assertEquals('age', $age['name']);
        $this->assertEquals('Age', $age['label']);
        $this->assertSame(40, $age['value']);

        $avatar = $fields['avatar'];
        $this->assertEquals('file', $avatar['type']);
        $this->assertNull($avatar['value']);
        $this->assertEquals(Media::first()->getUrl(), $avatar['image_url'] ?? null);
    }

    protected function tearDown()
    {
        Extension::unregister(TestUserResourceExtension::class);
        parent::tearDown();
    }
}


