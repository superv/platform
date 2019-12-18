<?php

namespace Tests\Platform\Domains\Resource\Http\Controllers;

use Storage;
use SuperV\Platform\Domains\Media\Media;
use Tests\Platform\Domains\Resource\ResourceTestCase;

/**
 * Class ResourceViewTest
 *
 * @package Tests\Platform\Domains\Resource\Http\Controllers
 * @group   resource
 * @group   http
 */
class ResourceViewTest extends ResourceTestCase
{
    use ResponseHelper;

    function test__view_data()
    {
        $users = $this->blueprints()->users();
        $user = $users->fake(['name' => 'Ali Selcuk', 'age' => '40', 'group_id' => 1]);

        Storage::fake('fakedisk');
        $this->postJsonUser($user->router()->updateForm(), ['avatar' => $this->makeUploadedFile()]);

        $this->withoutExceptionHandling();
        $view = $this->getResourceView($user);
        $this->assertEquals(6, $view->countProp('fields'));

        $fields = $view->getProp('fields');

        $name = $fields['name'];
        $this->assertFalse(isset($name['config']));
        $this->assertEquals('text', $name['type']);
        $this->assertEquals('name', $name['handle']);
        $this->assertEquals('Name', $name['label']);
        $this->assertSame('Ali Selcuk', $name['value']);

        $group = $fields['group'];
        $this->assertEquals('belongs_to', $group['type']);
        $this->assertSame('Users', $group['value']);
        $this->assertNull(array_get($group, 'meta.options'));
        $this->assertNotNull(array_get($group, 'meta.link'));

        $age = $fields['age'];
        $this->assertEquals('number', $age['type']);
        $this->assertEquals('age', $age['handle']);
        $this->assertEquals('Age', $age['label']);
        $this->assertSame(40, $age['value']);

        $avatar = $fields['avatar'];
        $this->assertEquals('file', $avatar['type']);
        $this->assertNull($avatar['value'] ?? null);
        $this->assertEquals(Media::first()->getUrl(), $avatar['image_url'] ?? null);
    }
}


