<?php

namespace SuperV\Platform\Domains\Media;

trait HasMedia
{
    public function mediaBag($label): MediaBag
    {
        return new MediaBag($this, $label);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\MorphMany */
    public function media()
    {
        return $this->morphMany(Media::class, 'owner');
    }

    public function addMedia($filePath, MediaOptions $options)
    {
        return $this->mediaBag($options->getLabel())
                    ->addFromPath($filePath, $options);
    }
}