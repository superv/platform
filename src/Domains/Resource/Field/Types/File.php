<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Media\Media;
use SuperV\Platform\Domains\Media\MediaBag;
use SuperV\Platform\Domains\Media\MediaOptions;
use SuperV\Platform\Domains\Resource\Field\DoesNotInteractWithTable;
use SuperV\Platform\Support\Composer\Composition;

class File extends FieldType implements DoesNotInteractWithTable
{
    protected $requestFile;

    public function getMedia(EntryContract $entry, $label): ?Media
    {
        $bag = new MediaBag($entry, $label);

        return $bag->media()->where('label', $label)->latest()->first();
    }

    protected function composer()
    {
        return function (Composition $composition, EntryContract $entry) {
            if ($media = $this->getMedia($entry, $this->getName())) {
                $composition->set('image_url', $media->getUrl());
                $composition->set('config', null);
            }
        };
    }

    protected function mutator()
    {
        return function ($requestFile, EntryContract $entry) {
            $this->requestFile = $requestFile;

            return function () use ($entry) {
                if (! $this->requestFile || ! $entry) {
                    return null;
                }

                $bag = new MediaBag($entry, $this->getName());

                $media = $bag->addFromUploadedFile($this->requestFile, $this->getConfigAsMediaOptions());

                return $media;
            };
        };
    }

    protected function getConfigAsMediaOptions()
    {
        return MediaOptions::one()
                           ->disk($this->getConfigValue('disk', 'local'))
                           ->path($this->getConfigValue('path'))
                           ->visibility($this->getConfigValue('visibility', 'private'));
    }

    protected function boot()
    {
        $this->on('form.composing', $this->composer());
        $this->on('form.mutating', $this->mutator());

        $this->on('view.composing', $this->composer());

        $this->on('table.composing', $this->composer());
    }
}