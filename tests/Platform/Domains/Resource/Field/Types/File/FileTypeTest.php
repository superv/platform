<?php

namespace Tests\Platform\Domains\Resource\Field\Types\File;

use Closure;
use Illuminate\Http\UploadedFile;
use Storage;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Media\Media;
use SuperV\Platform\Domains\Resource\Builder\Blueprint as ResourceBlueprint;
use SuperV\Platform\Domains\Resource\Builder\Builder;
use SuperV\Platform\Domains\Resource\Field\Types\File\Blueprint as FileTypeBlueprint;
use SuperV\Platform\Domains\Resource\Field\Types\File\Composer;
use SuperV\Platform\Domains\Resource\Field\Types\File\FileType;
use SuperV\Platform\Domains\Resource\Field\Types\File\Repository;
use SuperV\Platform\Domains\Resource\Field\Types\File\Uploader;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceConfig;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class FileTypeTest extends ResourceTestCase
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Resource
     */
    protected $resource;

    /**
     * @var \Closure
     */
    protected $blueprintCallback;

    function test__blueprint()
    {
        $blueprint = Builder::blueprint('testing.posts', $this->blueprintCallback);

        $contractBlueprint = $blueprint->getField('contract');
        $this->assertInstanceOf(FileTypeBlueprint::class, $contractBlueprint);
        $this->assertEquals('file', $contractBlueprint->getField()->getType());
        $this->assertEquals('test-disk', $contractBlueprint->getDisk());
        $this->assertEquals('test-path', $contractBlueprint->getPath());
        $this->assertEquals(['pdf', 'docx'], $contractBlueprint->getAllowedTypes());
        $this->assertTrue($contractBlueprint->isPublic());
    }

    function test__builder()
    {
        $resource = Builder::create('testing.posts', $this->blueprintCallback);

        $contractField = $resource->getField('contract');
        $this->assertNotNull($contractField);
        $this->assertEquals('file', $contractField->getType());

        $this->assertEquals([
            'disk'          => 'test-disk',
            'path'          => 'test-path',
            'public'        => true,
            'allowed_types' => ['pdf', 'docx'],
        ], $contractField->getConfig());
    }

    function test__value_resolver()
    {
        $file = UploadedFile::fake()->image('square.png');
        $entryMock = $this->makeEntryMock();
        $mediaMock = $this->makeMock(Media::class);

        $field = $this->makeField('avatar', FileType::class, ['disk' => 'fake-disk']);

        $mediaBag = $this->bindMock(Uploader::class);
        $mediaBag->expects('setEntry')->with($entryMock)->andReturnSelf();
        $mediaBag->expects('setLabel')->with('avatar')->andReturnSelf();
        $mediaBag->expects('setUploadedFile')->with($file)->andReturnSelf();
        $mediaBag->expects('setOptions')->andReturnSelf();
        $mediaBag->expects('save')->andReturn($mediaMock);

        $request = $this->makePostRequest('', ['avatar' => $file]);
        $callback = $field->value()
                          ->setRequest($request)
                          ->resolve()->get();
        $this->assertInstanceOf(Closure::class, $callback);

        $this->assertEquals($mediaMock, $callback($entryMock));
    }

    function test__composer()
    {
        $entryMock = $this->makeEntryMock();
        $mediaMock = $this->makeMock(Media::class);
        $mediaMock->expects('getUrl')->andReturn('media-url');
        $field = $this->makeField('avatar', FileType::class, ['disk' => 'fake-disk']);

        $repoMock = $this->bindMock(Repository::class);
        $repoMock->expects('setOwner')->with($entryMock)->andReturnSelf();
        $repoMock->expects('withLabel')->with('avatar')->andReturn($mediaMock);

        $composer = $field->getComposer();
        $this->assertInstanceOf(Composer::class, $composer);
        $this->assertEquals('media-url', $composer->toView($entryMock)->get('image_url'));
    }

    function test_type_file_is_not_required_by_default()
    {
        $avatar = $this->resource->getField('avatar');
        $this->assertFalse($avatar->isRequired());
        $this->assertTrue($avatar->hasFlag('nullable'));
    }

    function test__type_file()
    {
        $this->assertColumnNotExists('avatar', 'tmp_table');

        $entry = $this->resource->fake();
        /** @var \SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface $field */
        $field = $entry->getResource()->getField('avatar');

        $this->assertEquals('file', $field->getType());
        $this->assertEquals(['disk' => 'fakedisk'], $field->getConfig());

        $uploadedFile = UploadedFile::fake()->image('square.png');
        $request = $this->makePostRequest('', ['avatar' => $uploadedFile]);
        $callback = $field->getValue()->setRequest($request)->setEntry($entry)->resolve()->get();

        $this->assertInstanceOf(Closure::class, $callback);

        /** @var \SuperV\Platform\Domains\Media\Media $media */
        $media = $callback($entry, $request);
        $this->assertNotNull($media);
        $this->assertEquals('testing.tbl', $media->owner_type);
        $this->assertNotNull($field->getComposer()->toView($entry)->get('image_url'));

        $this->assertFileExists($media->filePath());
        $this->assertEquals('square.png', $media->getOriginalFilename());
    }

    function test__allowed_file_types()
    {
        $response = $this->uploadFile($this->resource, UploadedFile::fake()->image('square.jpg'));
        $response->assertStatus(422);
    }

    function test__max_size_fail()
    {
        $response = $this->uploadFile($this->resource, UploadedFile::fake()->image('square.png')->size(301));
        $response->assertStatus(422);
    }

    function test__max_size_success()
    {
        $this->withoutExceptionHandling();
        $response = $this->uploadFile($this->resource, UploadedFile::fake()->image('square.png')->size(300));
        $response->assertOk();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->resource = $this->create('tmp_table',
            function (Blueprint $table, ResourceConfig $config) {
                $config->setIdentifier('testing.tbl');
                $table->increments('id');
                $table->file('avatar')
                      ->config(['disk' => 'fakedisk'])
                      ->rules(['image', 'max:300', 'mimes:png']);
            });

        Storage::fake('fakedisk');

        $this->blueprintCallback = function (ResourceBlueprint $resource) {
            $resource->file('contract')
                     ->disk('test-disk')
                     ->path('test-path')
                     ->allowedTypes('pdf', 'docx')
                     ->public();
        };
    }

    protected function uploadFile(Resource $resource, $file)
    {
        $response = $this->postJsonUser($resource->router()->createForm(), ['avatar' => $file]);

        return $response;
    }
}
