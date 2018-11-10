<?php

namespace SuperV\Platform\Domains\Media;

use SuperV\Platform\Domains\Database\Model\Entry;

class Media extends Entry
{
    protected $visible = ['id', 'filename', 'size', 'original', 'mime_type', 'created_at'];

    public function getBasename()
    {
        return ! str_contains($this->filename, '.') ? $this->filename.'.'.$this->extension
            : $this->filename;
    }

    public function owner()
    {
        return $this->morphTo();
    }

    public function url()
    {
        if ($this->disk === 's3') {
            return 'https://'.config('filesystems.disks.s3.bucket').'.s3.amazonaws.com/'.$this->filename;
        }

        return url('storage/'.$this->filename);
    }

    public function filePath()
    {
        return \Storage::disk($this->disk)->path($this->getBasename());
    }

    public function associateOwner($owner): self
    {
        $this->owner()->associate($owner);

        return $this;
    }
}