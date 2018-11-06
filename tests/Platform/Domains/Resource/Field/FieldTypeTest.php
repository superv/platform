<?php

namespace Tests\Platform\Domains\Resource\Field;

use Closure;
use Illuminate\Http\UploadedFile;
use Storage;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Schema\Schema;
use SuperV\Platform\Domains\Resource\Field\Types\BelongsTo;
use SuperV\Platform\Domains\Resource\Field\Types\Boolean;
use SuperV\Platform\Domains\Resource\Field\Types\File;
use SuperV\Platform\Domains\Resource\Field\Types\Number;
use SuperV\Platform\Domains\Resource\Field\Types\Text;
use SuperV\Platform\Domains\Resource\Resource;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class FieldTypeTest extends ResourceTestCase
{
    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $resource;

    protected function setUp()
    {
        parent::setUp();

        Schema::create('t_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->entryLabel();
        });

        Schema::create('t_roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('role')->entryLabel();
        });

        Schema::create('t_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->entryLabel();
            $table->unsignedInteger('age');
            $table->decimal('height', 3, 2);
            $table->text('bio');
            $table->email('email');
            $table->date('birthday');
            $table->boolean('employed');

            $table->file('avatar')->config(['test-123']);
            $table->belongsTo('t_groups', 'group');
            $table->hasMany('t_posts', 'posts', 'user_id');
            $table->belongsToMany('t_roles', 'roles', 't_user_roles', 'user_id', 'role_id');
        });

        Schema::create('t_posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->belongsTo('t_users', 'user', 'user_id', 'post_id');
        });

        $this->resource = Resource::of('t_posts');
    }

    /** @test */
    function type_text()
    {
        $this->assertColumnExists('t_users', 'name');
        $field = Resource::of('t_users')->getField('name');

        $this->assertInstanceOf(Text::class, $field);
        $this->assertEquals('text', $field->getType());
    }

    /** @test */
    function type_number_integer()
    {
        $this->assertColumnExists('t_users', 'age');
        $resource = Resource::of('t_users');

        $field = $resource->getField('age');
        $field->setResource($resource->loadFake(['age' => '10', 'group_id' => 1]));

        $this->assertInstanceOf(Number::class, $field);
        $this->assertEquals('number', $field->getType());

        $this->assertSame(10, $field->getValue());
    }

    /** @test */
    function type_number_decimal()
    {
        $this->assertColumnExists('t_users', 'height');
        $resource = Resource::of('t_users');

        $field = $resource->getField('height');
        $field->setResource($resource->loadFake(['height' => '1.754234', 'group_id' => 1]));

        $this->assertInstanceOf(Number::class, $field);
        $this->assertEquals('number', $field->getType());
        $this->assertEquals('decimal', $field->getConfigValue('type'));
        $this->assertEquals(3, $field->getConfigValue('total'));
        $this->assertEquals(2, $field->getConfigValue('places'));

        $this->assertSame(1.75, $field->getValue());
    }

    /** @test */
    function type_boolean()
    {
        $this->assertColumnExists('t_users', 'employed');
        $resource = Resource::of('t_users');

        $field = $resource->getField('employed');
        $field->setResource($resource->loadFake(['employed' => 1, 'group_id' => 1]));

        $this->assertInstanceOf(Boolean::class, $field);
        $this->assertEquals('boolean', $field->getType());

        $this->assertSame(true, $field->getValue());
    }

    /** @test */
    function type_belongs_to()
    {
        $groups = Resource::of('t_groups');
        $groups->create(['id' => 100, 'title' => 'Users']);
        $adminsGroup = $groups->create(['id' => 110, 'title' => 'Admins']);

        $this->assertColumnExists('t_users', 'group_id');
        $users = Resource::of('t_users');
        $users->loadFake(['group_id' => 100]);

        $field = $users->getField('group');
        $this->assertEquals('t_groups', $field->getConfigValue('related_resource'));
        $this->assertEquals('group_id', $field->getConfigValue('foreign_key'));
        $field->build();

        $this->assertTrue($field->isBuilt());

        $this->assertEquals([
            ['value' => 100, 'text' => 'Users'],
            ['value' => 110, 'text' => 'Admins'],
        ], $field->getConfigValue('options'));

        $this->assertInstanceOf(BelongsTo::class, $field);
        $this->assertEquals('belongs_to', $field->getType());
        $this->assertEquals(100, $field->getValue());

        $field->setValue($adminsGroup);
        $this->assertEquals(110, $field->getValue());
    }

    /** @test */
    function type_file()
    {
        $this->assertFalse(in_array('avatar', \Schema::getColumnListing('t_users')));

        $users = Resource::of('t_users');
        $users->loadFake();

        $field = $users->getField('avatar');

        $this->assertInstanceOf(File::class, $field);
        $this->assertEquals('file', $field->getType());
        $this->assertEquals(['test-123'], $field->getConfig());
        $this->assertNull($field->getValue());

        //upload
        Storage::fake('fakedisk');
        $field->setConfig([
            'disk' => 'fakedisk',
        ]);
        $callback = $field->setValue(new UploadedFile($this->basePath('__fixtures__/square.png'), 'square.png'));
        $this->assertInstanceOf(Closure::class, $callback);

        /** @var \SuperV\Platform\Domains\Media\Media $media */
        $media = $callback();
        $this->assertNotNull($media);
        $this->assertNotNull($field->getConfigValue('url'));

        $this->assertFileExists($media->filePath());

    }
}