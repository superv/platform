<?php

namespace SuperV\Platform\Domains\Media;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SuperV\Platform\Support\Collection;
use Symfony\Component\HttpFoundation\File\File;

class MediaBag
{
    /** @var \SuperV\Platform\Domains\Media\MediaOwner */
    protected $owner;

    protected $files = [];

    protected $bagConfig = [];

    protected $label;

    public function __construct(MediaOwner $owner, $label)
    {
        $this->owner = $owner;
        $this->label = $label;

        $this->files = new Collection();
    }

    public function first()
    {
        return $this->owner->media()->first();
    }

    public function get()
    {
        return $this->owner->media;
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
        $media->owner()->associate($this->owner);
        $media->save();

        return $media;
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
        $filename,
        $diskName = 'local',
        $visibility = 'private',
        $folder = ''
    ) {
        if (! preg_match('/^data:((\w+)\/(\w+));base64,/', $base64EncodedData, $type)) {
            return;
        }
        $data = substr($base64EncodedData, strpos($base64EncodedData, ',') + 1);
        $mimeType = strtolower($type[1]); // jpg, png, gif
        $extension = strtolower($type[3]); // jpg, png, gif
        $target = ($folder ? $folder.'/' : '').md5($filename.uniqid()).'.'.$extension;
//        $uploadSuccess = Storage::disk($diskName)->put($target, base64_decode($data));
        $uploadSuccess = Storage::disk($diskName)->put($target, base64_decode($data), $visibility);

        if ($uploadSuccess) {
            $media = new Media([
                'filename'   => $target,
                'disk'       => $diskName,
                'original'   => $filename,
                'owner_type' => get_class($this->owner),
                'owner_id'   => $this->owner->id,
                'label'      => $this->label,
                'mime_type'  => $mimeType,
                'extension'  => $extension,
                'size'       => Storage::disk($diskName)->size($target),
            ]);
            $media->owner()->associate($this->owner);
            $media->save();

            return $media;
        }
    }

    /**
     * @param \SuperV\Platform\Domains\Media\MediaOptions     $options
     * @param                                                 $file
     * @return \SuperV\Platform\Domains\Media\Media
     * @throws \Exception
     */
    protected function addFile($file, MediaOptions $options)
    {
        $fileHashName = $this->saveFileToDisk($options, $file);

        if ($fileHashName) {
            return $this->createEntry($file, $fileHashName, $options);
        } else {
            throw  new \Exception('Upload failed');
        }
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
        $extension = $file->getExtension();
        $mimeType = $file->getMimeType();
        if (! $extension && starts_with($mimeType, 'image/svg')) {
            $extension = 'svg';
        }

        return $extension;
    }
}