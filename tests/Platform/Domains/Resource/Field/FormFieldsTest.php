<?php

namespace Tests\Platform\Domains\Resource\Field;

use Illuminate\Foundation\Testing\Concerns\InteractsWithDatabase;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Assert;
use Storage;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Media\MediaBag;
use SuperV\Platform\Domains\Media\MediaOptions;
use SuperV\Platform\Domains\Resource\Fake;
use SuperV\Platform\Domains\Resource\Resource;
use Tests\Platform\Domains\Resource\ResourceTestCase;
use Tests\Platform\TestCase;

/**
 * Class FormFieldsTest
 * BelongsTo
 * Boolean *
 * Datetime
 * Email
 * File *
 * Number *
 * Select
 * Text *
 * Textarea
 */
class FormFieldsTest extends ResourceTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $groups = $this->create('t_groups',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('title');
            });

        $groups->fake([], 4);

        $users = $this->create('t_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->entryLabel();
            $table->unsignedInteger('age');
            $table->file('avatar')->config(['disk' => 'fakedisk']);
            $table->belongsTo('t_groups', 'group');
        });

        //upload
        Storage::fake('fakedisk');
    }

    function test__create_form_with_all_field_types()
    {
        $this->withoutExceptionHandling();

        $tester = (new FormTester(Resource::of('t_users')));
        $tester->testCreate($this);
    }

    function test__update_form_with_all_field_types()
    {
        $this->withoutExceptionHandling();

        $tester = (new FormTester(Resource::of('t_users')));
        $tester->testUpdate($this);
    }
}

class FormTester extends Assert
{
    use InteractsWithDatabase;

    /**
     * @var \SuperV\Platform\Domains\Resource\Resource
     */
    protected $resource;

    protected $app;

    protected $propsKeys = [
        'create' => 'data.props.page.blocks.0.props',
        'edit'   => 'data.props.page.blocks.0.props.tabs.0.block.props',
    ];

    public function __construct(Resource $resource)
    {
        $this->resource = $resource;

        $this->app = app();
    }

    public function testUpdate(TestCase $testCase)
    {
        $fake = $this->resource->fake();

        $avatarFile = (new MediaBag($fake->getEntry(), 'avatar'))->addFromUploadedFile(
            new UploadedFile($testCase->basePath('__fixtures__/square.png'), 'square.png'),
            MediaOptions::one('avatar')->disk('fakedisk')
        );

        $response = $testCase->getJsonUser($fake->route('edit'));
        $props = $response->decodeResponseJson($this->propsKeys['edit']);
        $testCase->assertEquals(['url', 'method', 'fields'], array_keys($props));

        $fields = $props['fields'];

        foreach ($fields as $field) {
            $value = $field['value'] ?? null;
            $fieldName = $field['name'];
            if ($fieldName === 'avatar') {
                $this->assertEquals([
                    'url' => $avatarFile->url()
                ], $field['config']);
            } elseif ($value !== $fake->{$fieldName}) {
                $testCase->fail('Failed to asset equals field value for: '.$fieldName);
            }
        }

        $otherFake = $this->resource->fake();
        $response = $testCase->postJsonUser($props['url'], $otherFake->toArray());
        $response->assertOk();

        $testCase->assertEquals(
            array_except($fake->fresh()->toArray(), 'id'),
            array_except($otherFake->toArray(), 'id')
        );
    }

    public function testCreate(TestCase $testCase)
    {
        $response = $testCase->getJsonUser($this->resource->route('create'));
        $response->assertOk();

        $props = $response->decodeResponseJson($this->propsKeys['create']);
        $testCase->assertEquals(['url', 'method', 'fields'], array_keys($props));

        $fake = Fake::make($this->resource);

        foreach ($props['fields'] as $field) {
            $name = $field['name'];
            $post[$name] = $fake[$name] ?? null;
        }

        $post['avatar'] = new UploadedFile($testCase->basePath('__fixtures__/square.png'), 'square.png');

        $response = $testCase->postJsonUser($props['url'], $post ?? []);
        $response->assertOk();

        $entry = $this->resource->first();

        $testCase->assertEquals(array_except($entry->toArray(), 'id'), $fake);

        $this->assertDatabaseHas('media', [
            'label'      => 'avatar',
            'owner_type' => 't_users',
            'owner_id'   => $entry->id(),
        ]);
    }
}

