<?php

namespace SuperV\Platform\Domains\Resource\Testing;

use Illuminate\Foundation\Testing\Concerns\InteractsWithDatabase;
use Illuminate\Foundation\Testing\Concerns\MakesHttpRequests;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Assert;
use SuperV\Platform\Domains\Media\MediaBag;
use SuperV\Platform\Domains\Media\MediaOptions;
use SuperV\Platform\Domains\Resource\Fake;
use SuperV\Platform\Domains\Resource\Form\EntryForm;
use SuperV\Platform\Testing\TestHelpers;

class FormTester extends Assert
{
    use MakesHttpRequests;
    use InteractsWithDatabase;
    use TestHelpers;

    /**
     * @var \SuperV\Platform\Domains\Resource\Resource
     */
    protected $resource;

    protected $app;

    protected $propsKeys = [
        'create' => 'data.props.blocks.0.props',
        'edit'   => 'data.props.blocks.0.props.tabs.0.block.props',
    ];

    /**
     * @var string
     */
    protected $basePath;

    public function __construct(string $basePath)
    {
        $this->app = app();

        $this->basePath = $basePath;
    }

    protected function boot() { }

    protected function basePath($path = null)
    {
        return $this->basePath.str_prefix($path, '/', '');
    }

    public function test(EntryForm $form)
    {
        $entryForHandle = $form->getEntry();
        $original = $entryForHandle->toArray();
        $fields = [];
        foreach ($form->getFields() as $field) {
            if ($field->isHidden() || $field->doesNotInteractWithTable()) {
                continue;
            }

            $fields[] = $form->composeField($field);
            $post[$field->getColumnName()] = Fake::field($field);
        }

//        dd($form->getWatcher()->toArray(), $fields, $post);
//        $response = $this->postJsonUser($form->getUrl(), $post);
//        $response->assertOk();

        $form->setRequest($this->makePostRequest('', $post))->save();

        $entry = $form->getEntry()->newQuery()->first();

        static::assertTrue($entry->exists());

        $expectedAttributes = array_merge($original, $post);
        $this->assertArrayContains($expectedAttributes, array_except($entry->toArray(), 'id'));

        // create
        // loop fields
        // fake according to type
        // submit

        // update
        // make fake
        // fill
        // submit

    }

    public function testUpdate()
    {
        $fake = $this->resource->fake();

        $avatarFile = $this->addTestFileToFake($fake);

        $props = $this->getUpdateForm($fake);

        $fields = $props['fields'];

        foreach ($fields as $field) {
            $value = $field['value'] ?? null;
            $fieldName = $field['name'];
            if ($fieldName === 'avatar') {
                $this->assertEquals(['url' => $avatarFile->url(),], $field['config']);
            } elseif ($value !== $fake->{$fieldName}) {
                $this->fail('Failed to asset equals field value for: '.$fieldName);
            }
        }

        $otherFake = $this->resource->fake();
        $response = $this->postJsonUser($props['url'], $otherFake->toArray());
        $response->assertOk();

        $this->assertEquals(
            array_except($fake->fresh()->toArray(), 'id'),
            array_except($otherFake->toArray(), 'id')
        );
    }

    public function testCreate()
    {
        $props = $this->getCreateForm();

        $fake = Fake::make($this->resource);

        foreach ($props['fields'] as $field) {
            $name = $field['name'];
            $post[$name] = $fake[$name] ?? null;
        }

        $post['avatar'] = $this->getTestFile();
        $this->postJsonUser($props['url'], $post)->assertOk();

        $entry = $this->resource->first();

        $this->assertEquals(array_except($entry->toArray(), 'id'), $fake);

        $this->assertDatabaseHas('sv_media', [
            'label'      => 'avatar',
            'owner_type' => 't_users',
            'owner_id'   => $entry->getId(),
        ]);
    }

    /**
     * @return \Illuminate\Http\UploadedFile
     */
    protected function getTestFile(): UploadedFile
    {
        return new UploadedFile($this->basePath('__fixtures__/square.png'), 'square.png');
    }

    /**
     * @param $fake
     * @return \SuperV\Platform\Domains\Media\Media
     */
    protected function addTestFileToFake($fake): \SuperV\Platform\Domains\Media\Media
    {
        $avatarFile = (new MediaBag($fake->getEntry(), 'avatar'))->addFromUploadedFile(
            $this->getTestFile(),
            MediaOptions::one('avatar')->disk('fakedisk')
        );

        return $avatarFile;
    }

    /**
     * @param $fake
     * @return array
     */
    protected function getUpdateForm($fake): array
    {
        $response = $this->getJsonUser($fake->route('edit'));
        $props = $response->decodeResponseJson($this->propsKeys['edit']);
        $this->assertEquals(['url', 'method', 'fields'], array_keys($props));

        return $props;
    }
}
