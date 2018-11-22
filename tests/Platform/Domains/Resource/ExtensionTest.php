<?php

namespace Tests\Platform\Domains\Resource;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Extension\Contracts\ResourceExtension;
use SuperV\Platform\Domains\Resource\Extension\Extension;
use SuperV\Platform\Domains\Resource\Extension\RegisterExtensionsInPath;
use SuperV\Platform\Domains\Resource\Field\Types\Number;
use SuperV\Platform\Domains\Resource\Field\Types\Text;
use SuperV\Platform\Domains\Resource\Field\Types\Textarea;
use SuperV\Platform\Domains\Resource\Model\ResourceEntry;
use SuperV\Platform\Domains\Resource\Resource;

class ExtensionTest extends ResourceTestCase
{
    /** @test */
    function overrides_fields()
    {
        $this->create('t_users',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->unsignedInteger('age');
            });

        Extension::register(TestUserResourceExtension::class);

        $extended = Resource::of('t_users');

        $nameField = $extended->getField('name');

        $this->assertTrue($nameField->getConfigValue('extended'));


    }

    /** @test */
    function gets_before_saving()
    {
        $res = $this->makeResource('t_users', ['name', 'age:integer']);

        Extension::register(TestUserResourceExtension::class);
        $ext = Resource::of('t_users');
        $user = $ext->createFake(['age => 40']); // rules set in extension

        TestUserResourceExtension::$callbacks['saving'] = function (ResourceEntry $entry) {
            $this->assertEquals(100, $entry->age);

            return $entry->age = $entry->age + 1;
        };
        TestUserResourceExtension::$callbacks['saved'] = function (ResourceEntry $entry) {
            return $entry->age = $entry->age + 1;
        };
        $user->age = 100;
        $user->save();

        // object at current pointer is incremented twice
        $this->assertEquals(102, $user->age);

        // since the last one was after saving, it is not persisted
        $this->assertEquals(101, $user->fresh()->age);
    }

    /** @test */
    function registers_extensions_from_path()
    {
        RegisterExtensionsInPath::dispatch(
            __DIR__.'/Fixtures/Extensions',
            'Tests\Platform\Domains\Resource\Fixtures\Extensions'
        );

        $this->assertNotNull(Extension::get('test_a'));
//        $this->assertNotNull(Extension::get('test_b'));
    }

    protected function tearDown()
    {
        parent::tearDown();

        Extension::unregister(TestUserResourceExtension::class);
    }
}

class TestUserResourceExtension implements ResourceExtension
{
    /** @var array */
    protected $called = [];

    /** @var array */
    public static $callbacks = [];

    public function extends(): string
    {
        return 't_users';
    }

    public function extend(Resource $resource)
    {
        $resource->getField('name')->setConfigValue('extended', true);
    }

    public function isCalled($event)
    {
        return array_has($this->called, $event);
    }

    public function saving(ResourceEntry $entry)
    {
        if ($saving = array_get(static::$callbacks, 'saving')) {
            $saving($entry);
            $this->called[] = 'saving';
        }
    }

    public function saved(ResourceEntry $entry)
    {
        if ($saved = array_get(static::$callbacks, 'saved')) {
            $saved($entry);
            $this->called[] = 'saved';
        }
    }
}
