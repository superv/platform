<?php

namespace Tests\Platform\Domains\Resource\Form\Features;

use Illuminate\Http\UploadedFile;
use Storage;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Field\Types\FileField;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;
use SuperV\Platform\Domains\Resource\Form\Features\SubmitForm;
use SuperV\Platform\Exceptions\ValidationException;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class SubmitFormTest extends ResourceTestCase
{
    function test__submit_success()
    {
        $categories = $this->blueprints()->categories(function (Blueprint $table) {
            $table->image('photo', '/', 'fakedisk');
        });

        Storage::fake('fakedisk');

        $form = (new SubmitForm())
            ->setResource($categories)
            ->setFormData(['title' => 'Books', 'photo' => UploadedFile::fake()->image('img.png')])
            ->submit();

        $this->assertInstanceOf(FormInterface::class, $form);
        $entry = $categories->first();
        $this->assertEquals('Books', $entry->title);

        $media = FileField::getMedia($entry, 'photo');
        $this->assertNotNull($media);
        $this->assertFileExists($media->filePath());
    }

    function test__submit_with_entry_success()
    {
        $categories = $this->blueprints()->categories(function (Blueprint $table) {
            $table->image('photo', '/', 'fakedisk');
        });

        $entry = $categories->fake();

        Storage::fake('fakedisk');

        (new SubmitForm())
            ->setResource($categories)
            ->setFormData(['title' => 'Books', 'photo' => UploadedFile::fake()->image('img.png')])
            ->setEntry($entry)
            ->submit();

        $entry = $entry->fresh();
        $this->assertEquals('Books', $entry->title);

        $media = FileField::getMedia($entry, 'photo');
        $this->assertNotNull($media);
        $this->assertFileExists($media->filePath());
    }

    function test__submit_with_entry_without_file()
    {
        $categories = $this->blueprints()->categories(function (Blueprint $table) {
            $table->image('photo', '/', 'fakedisk');
        });

        $entry = $categories->fake();

        Storage::fake('fakedisk');

        (new SubmitForm())
            ->setResource($categories)
            ->setFormData(['title' => 'Books', 'photo' => 'null'])
            ->setEntry($entry)
            ->submit();

        $entry = $entry->fresh();
        $this->assertEquals('Books', $entry->title);

        $media = FileField::getMedia($entry, 'photo');
        $this->assertNull($media);
    }

    function test__validation()
    {
        $categories = $this->blueprints()->categories();

        $entry = $categories->fake();

        Storage::fake('fakedisk');

        $this->expectException(ValidationException::class);

        (new SubmitForm())
            ->setResource($categories)
            ->setFormData(['title' => null])
            ->setEntry($entry)
            ->submit();
    }
}