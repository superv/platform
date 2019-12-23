<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\File;

use SplFileInfo;
use SuperV\Platform\Domains\Media\MediaBag;
use SuperV\Platform\Domains\Media\MediaOptions;

class Uploader
{
    /** @var \SuperV\Platform\Domains\Database\Model\Contracts\EntryContract */
    protected $entry;

    /** @var string */
    protected $label;

    /** @var \SplFileInfo */
    protected $uploadedFile;

    /** @var MediaOptions */
    protected $options;

    public function save()
    {
        $bag = new MediaBag($this->entry, $this->label);

        $media = $bag->addFile($this->uploadedFile, $this->options);

        return $media;
    }

    public function setEntry(\SuperV\Platform\Domains\Database\Model\Contracts\EntryContract $entry): Uploader
    {
        $this->entry = $entry;

        return $this;
    }

    public function setLabel(string $label): Uploader
    {
        $this->label = $label;

        return $this;
    }

    public function setUploadedFile(SplFileInfo $file): Uploader
    {
        $this->uploadedFile = $file;

        return $this;
    }

    public function setOptions(MediaOptions $options): Uploader
    {
        $this->options = $options;

        return $this;
    }
}