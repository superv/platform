<?php

namespace SuperV\Platform\Domains\Media;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
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

    public function getUrl()
    {
        return $this->url();
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

    public static function for(EntryContract $owner, $label = null)
    {
        return static::query()->where('owner_type', $owner->getMorphClass())
                     ->where('owner_id', $owner->getId())
                     ->when($label, function ($query, $label) { $query->where('label', $label); })
                     ->first();
    }
}