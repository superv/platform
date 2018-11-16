<?php

namespace Tests\Platform\Domains\Resource\Field\Types;

use Closure;
use Illuminate\Http\UploadedFile;
use Storage;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Field\Types\File;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class FileTest extends ResourceTestCase
{

    function type_file_is_not_required_by_default()
    {
        $res = $this->create(null, function (Blueprint $table) {
            $table->increments('id');
            $table->file('avatar');
        });

        $avatar = $res->getFieldType('avatar');
        $this->assertFalse($avatar->isRequired());
    }

    /** @test */
    function type_file()
    {
        $res = $this->create(function (Blueprint $table) {
            $table->increments('id');
            $table->file('avatar')->config(['disk' => 'fakedisk']);
        });

        $this->assertColumnDoesNotExist('avatar', $res->getHandle());
        $this->assertFalse(in_array('avatar', \Schema::getColumnListing($res->getHandle())));

        $fake = $res->fake();
        $field = $fake->getField('avatar');
        $field->build();

        $fieldType = $field->fieldType();

        $this->assertInstanceOf(File::class, $fieldType);
        $this->assertEquals('file', $fieldType->getType());
        $this->assertEquals(['disk' => 'fakedisk'], $field->getConfig());
        $this->assertNull($field->getValue());

        //upload
        Storage::fake('fakedisk');

        $uploadedFile = new UploadedFile($this->basePath('__fixtures__/square.png'), 'square.png');
        $callback = $field->setValue($uploadedFile);
        $this->assertInstanceOf(Closure::class, $callback);

//        $this->assertEquals($uploadedFile, $field->getValueForValidation());

        /** @var \SuperV\Platform\Domains\Media\Media $media */
        $media = $callback();
        $this->assertNotNull($media);

        $this->assertNotNull( $field->compose()->get('config.url'));

        $this->assertFileExists($media->filePath());
    }
}