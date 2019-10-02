<?php

namespace Tests\Platform\Domains\Resource\Field\Types;

use Closure;
use Illuminate\Http\UploadedFile;
use Storage;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Field\FieldComposer;
use SuperV\Platform\Domains\Resource\ResourceConfig;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class FileFieldTest extends ResourceTestCase
{
    function test_type_file_is_not_required_by_default()
    {
        $res = $this->create('tmp_table', function (Blueprint $table) {
            $table->increments('id');
            $table->file('avatar');
        });

        $avatar = $res->getField('avatar');
        $this->assertFalse($avatar->isRequired());
    }

    function test__type_file()
    {
        $res = $this->create('tmp_table', function (Blueprint $table, ResourceConfig $config) {
            $config->setIdentifier('testing.tbl');
            $table->increments('id');
            $table->file('avatar')->config(['disk' => 'fakedisk']);
        });

        $this->assertColumnNotExists('avatar', 'tmp_table');
        $this->assertFalse(in_array('avatar', \Schema::getColumnListing('tmp_table')));

        $fake = $res->fake();
        /** @var \SuperV\Platform\Domains\Resource\Field\Contracts\Field $field */
        $field = $fake->getResource()->getField('avatar');

        $this->assertEquals('file', $field->getType());
        $this->assertEquals(['disk' => 'fakedisk'], $field->getConfig());

        //upload
        Storage::fake('fakedisk');

        $uploadedFile = new UploadedFile($this->basePath('__fixtures__/square.png'), 'square.png');
        $callback = $field->resolveRequest($this->makePostRequest('', ['avatar' => $uploadedFile]), $fake);
        $this->assertInstanceOf(Closure::class, $callback);

//        $this->assertEquals($uploadedFile, $field->getValueForValidation());

        /** @var \SuperV\Platform\Domains\Media\Media $media */
        $media = $callback();
        $this->assertNotNull($media);

        $this->assertEquals('testing.tbl', $media->owner_type);

        $this->assertNotNull((new FieldComposer($field))->forView($fake)->get('image_url'));

        $this->assertFileExists($media->filePath());
    }

    function __allowed_file_types()
    {
        $res = $this->create('tmp_table', function (Blueprint $table, ResourceConfig $config) {
            $table->increments('id');
            $table->file('avatar')->config(['disk' => 'fakedisk'])->rules(['image']);
        });

        Storage::fake('fakedisk');
        $uploadedFile = new UploadedFile($this->basePath('__fixtures__/square.png'), 'square.png');
        $entry = $this->postCreateResource($res->getIdentifier(), ['avatar' => $uploadedFile]);
    }
}
