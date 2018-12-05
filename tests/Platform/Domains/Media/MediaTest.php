<?php

namespace Tests\Lakcom\Core\Domains\Media;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Media\HasMedia;
use SuperV\Platform\Domains\Media\Media;
use SuperV\Platform\Domains\Media\MediaBag;
use SuperV\Platform\Domains\Media\MediaOptions;
use SuperV\Platform\Domains\Media\MediaOwner;
use Tests\Platform\TestCase;

class MediaTest extends TestCase
{
    use RefreshDatabase;

    /** @var MediaOwner */
    protected $owner;

    protected $data;

    protected function setUp()
    {
        parent::setUp();

        \Schema::create('__media_owner', function (Blueprint $table) {
            $table->increments('id');
        });

        $this->owner = OwnerMock::create(['id' => rand(111, 999)]);
    }

    /** @test */
    function can_add_media_from_path()
    {
        Storage::fake('fakedisk');
        $options = MediaOptions::one('photos')->disk('fakedisk');

//        $pngFile = $this->owner->mediaBag('photos')->addFromPath(realpath(__DIR__.'/fixtures/image-a.png'), $options);
//        $this->assertMediaSaved($pngFile, 'image-a.png', 'image/png', 'png');

        $pngFile = $this->owner->mediaBag('photos')->addFromPath(realpath(__DIR__.'/fixtures/icon.svg'), $options);
        $this->assertMediaSaved($pngFile, 'icon.svg', 'image/svg+xml', 'svg');

    }

    protected function assertMediaSaved(Media $media, $original, $mimeType, $extension)
    {
        Storage::disk('fakedisk')->assertExists($media->filename);
        $this->assertEquals(get_class($this->owner), $media->owner_type);
        $this->assertEquals($this->owner->id, $media->owner_id);
        $this->assertEquals('fakedisk', $media->disk);
        $this->assertEquals($original, $media->original);
        $this->assertNotNull($media->filename);
        $this->assertEquals('photos', $media->label);
        $this->assertEquals($mimeType, $media->mime_type);
        $this->assertEquals($extension, $media->extension);
        $this->assertEquals(Storage::disk($media->disk)->size($media->filename), $media->size);
    }


    function can_add_media_from_base64()
    {
        Storage::fake('fakedisk');

        $this->owner->mediaBag('photos')->addFromBase64($this->fixtureBase64('image-a', 'image/png'), 'image-a', 'fakedisk');

        Storage::disk('fakedisk')->assertExists(md5('image-a').'.png');

        $this->assertMediaSaved($this->owner->mediaBag('photos')->first());
    }

    function can_only_add_allowed_mime_types()
    {
        MediaBag::setConfig('photos', ['mime_types' => ['image/png']]);

        $this->owner->mediaBag('photos')->addFromBase64($this->fixtureBase64('image-a.png', 'image/png'), 'image-a.png', 'fakedisk');
    }

    /** @test */
    function retrieves_files_of_a_label()
    {
        Storage::fake('fakedisk');

        $this->addMediaForOwner($this->owner, 'image-a', 'image/png');
        $this->addMediaForOwner($this->owner, 'image-b', 'image/png');

        $photos = $this->owner->mediaBag('photos')->get();
        $this->assertEquals(2, $photos->count());
        $this->assertArraySubset([
            ['original' => 'image-a'],
            ['original' => 'image-b'],
        ], $photos->toArray());

        // check label
        $photos->map(function(Media $media) {
            $this->assertEquals('photos', $media->label);
            $this->assertEquals('fakedisk', $media->disk);
        });
    }

    protected function addMediaForOwner(
        MediaOwner $owner,
        $filename,
        $mimeType = 'image/png',
        $label = 'photos',
        $diskName = 'fakedisk'
    ) {
        $owner->mediaBag($label)->addFromBase64($this->fixtureBase64($filename, $mimeType), $filename, $diskName);
    }

    protected function fixtureBase64($filename, $mimeType)
    {
        list($type, $extension) = explode('/', $mimeType);

        return "data:{$mimeType};base64,".base64_encode(file_get_contents(__DIR__.'/fixtures/'.$filename.'.'.$extension));
    }

}

class OwnerMock extends Model implements MediaOwner, EntryContract
{
    public $timestamps = false;

    protected $guarded = [];

    protected $table = '__media_owner';

    use HasMedia;

    public function getId()
    {
        return $this->id;
    }
}