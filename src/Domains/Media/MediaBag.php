<?php

namespace SuperV\Platform\Domains\Media;

use Exception;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use Symfony\Component\HttpFoundation\File\File;

class MediaBag
{
    /** @var \SuperV\Platform\Domains\Database\Model\Contracts\EntryContract */
    protected $owner;

    protected $files = [];

    protected $bagConfig = [];

    protected $label;

    public function __construct(EntryContract $owner, $label)
    {
        $this->owner = $owner;
        $this->label = $label;

        $this->files = new Collection();
    }

    public function first()
    {
        return $this->media()->first();
    }

    public function get()
    {
        return $this->media()->get();
    }

    public function media()
    {
        return new MorphMany(
            (new Media())->newQuery(),
            $this->owner,
            'owner_type',
            'owner_id',
            'id'
        );
    }

    public function addFile($file, MediaOptions $options)
    {
        $fileHashName = $this->saveFileToDisk($options, $file);

        if ($fileHashName) {
            return $this->createEntry($file, $fileHashName, $options);
        } else {
            throw  new \Exception('Upload failed');
        }
    }

    public function addFromPath($fullPath, MediaOptions $options)
    {
        if (! $file = new \Illuminate\Http\File($fullPath)) {
            throw new Exception('Can not retrieve file from '.$fullPath);
        }

        return $this->addFile($file, $options);
    }

    public function addFromUploadedFile(UploadedFile $file, MediaOptions $options)
    {
        return $this->addFile($file, $options);
    }

    public function addFromBase64(
        $base64EncodedData,
        $path,
        $filename,
        $diskName = 'local',
        $visibility = 'private'

    ) {
        if (! preg_match('/^data:((\w+)\/(\w+));base64,/', $base64EncodedData, $type)) {
            return;
        }

        return $this->addFile($this->reverseTransform($base64EncodedData, $filename), MediaOptions::one('photo')->disk($diskName)->visibility($visibility)->path($path));
    }

    /**
     * Convert from base64-encoded to UploadedFile
     *
     * @param $value
     * @param $filename
     * @return \Illuminate\Http\UploadedFile
     */
    public function reverseTransform($value, $filename)
    {
        $tmpFilePath = tempnam(sys_get_temp_dir(), 'vsuper');

        $tmp = fopen($tmpFilePath, 'wb+');

        $matches = [];
        preg_match('/^data:([\w-]+\/[\w-]+);base64,(.+)$/', $value, $matches);

        $size = fwrite($tmp, base64_decode($matches[2]));

        fclose($tmp);

        $mimeType = strtolower($matches[1]); // jpg, png, gif

        return new UploadedFile($tmpFilePath, $filename, $mimeType, $size, 0, true);
    }

    /**
     * @param array $bagConfig
     * @return MediaBag
     */
    public function setBagConfig(array $bagConfig): MediaBag
    {
        $this->bagConfig = $bagConfig;

        return $this;
    }

    protected function createEntry($file, $filename, MediaOptions $options)
    {
        $original = $file instanceof UploadedFile ? $file->getClientOriginalName() : $file->getFilename();

        $media = new Media([
            'filename'  => ($options->getPath() ? $options->getPath().'/' : '').$filename,
            'disk'      => $options->getDisk(),
            'original'  => $original,
            'label'     => $this->label,
            'mime_type' => $file->getMimeType(),
            'extension' => $this->getFileExtension($file),
            'size'      => $file->getSize(),
        ]);

        $media->associateOwner($this->owner)->save();

        return $media;
    }

    /**
     * @param \SuperV\Platform\Domains\Media\MediaOptions     $options
     * @param                                                 $file
     * @return false|string
     */
    protected function saveFileToDisk(MediaOptions $options, File $file)
    {
        $name = Str::random(40).'.'.$this->getFileExtension($file);
        Storage::disk($options->getDisk())
               ->putFileAs(
                   $options->getPath(),
                   $file,
                   $name,
                   $options->getVisibility()
               );

        return $name;
    }

    protected function getFileExtension(File $file)
    {
        $extension = $file instanceof UploadedFile ? $file->getClientOriginalExtension() : $file->getExtension();

        $mimeType = $file->getMimeType();

        if (! $extension && starts_with($mimeType, 'image/svg')) {
            $extension = 'svg';
        }

        return $extension;
    }
}
