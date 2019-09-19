<?php

namespace Tests\Platform\Domains\Resource\Field\Types;

use Closure;
use Illuminate\Http\UploadedFile;
use Storage;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Field\FieldComposer;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class FileTest extends ResourceTestCase
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
        $res = $this->create('tmp_tbl', function (Blueprint $table) {
            $table->increments('id');
            $table->file('avatar')->config(['disk' => 'fakedisk']);
        });

        $this->assertColumnNotExists('avatar', 'tmp_tbl');
        $this->assertFalse(in_array('avatar', \Schema::getColumnListing('tmp_tbl')));

        $fake = $res->fake();
        /** @var \SuperV\Platform\Domains\Resource\Field\Contracts\Field $field */
        $field = $fake->getField('avatar');

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

        $this->assertNotNull((new FieldComposer($field))->forView($fake)->get('image_url'));

        $this->assertFileExists($media->filePath());
    }
}
