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
    /** @test */
    function type_file_is_not_required_by_default()
    {
        $res = $this->create(null, function (Blueprint $table) {
            $table->increments('id');
            $table->file('avatar');
        });

        $this->assertFalse($res->getField('avatar')->isRequired());
    }

    /** @test */
    function type_file()
    {
        $res = $this->create(null, function (Blueprint $table) {
            $table->increments('id');
            $table->file('avatar')->config(['test-123']);
        });

        $this->assertColumnDoesNotExist('avatar', $res->handle());
        $this->assertFalse(in_array('avatar', \Schema::getColumnListing($res->handle())));

        $field = $res->freshWithFake()->build()->getField('avatar');

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