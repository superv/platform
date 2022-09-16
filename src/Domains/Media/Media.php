<?php

namespace SuperV\Platform\Domains\Media;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Database\Entry\ResourceEntry;

class Media extends ResourceEntry
{
    protected $table = 'sv_media';

    protected $visible = ['id', 'filename', 'size', 'original', 'mime_type', 'created_at'];

    protected static function boot()
    {
        parent::boot();

        parent::deleted(function (Media $entry) {
            \Storage::disk($entry->getDiskName())->delete($entry->getBasename());
        });
    }

    public function getBasename()
    {
        return ! \Str::contains($this->filename, '.') ? $this->filename.'.'.$this->extension
            : $this->filename;
    }

    public function getDiskName()
    {
        return $this->disk;
    }

    public function getOriginalFilename()
    {
        return $this->getAttribute('original');
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

        return url('storage/'.ltrim($this->filename, '/'));
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
                     ->orderBy('id', 'DESC')
                     ->first();
    }
}