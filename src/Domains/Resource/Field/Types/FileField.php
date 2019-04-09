<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Media\Media;
use SuperV\Platform\Domains\Media\MediaBag;
use SuperV\Platform\Domains\Media\MediaOptions;
use SuperV\Platform\Domains\Resource\Field\DoesNotInteractWithTable;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Support\Composer\Payload;

class FileField extends Field implements DoesNotInteractWithTable
{
    protected $requestFile;

    public function getMedia(EntryContract $entry, $label): ?Media
    {
        $bag = new MediaBag($entry, $label);

        $query = $bag->media()->where('label', $label)->latest();

        return $query->first();
    }

    protected function composer()
    {
        return function (Payload $payload, EntryContract $entry) {
            if ($media = $this->getMedia($entry, $this->getName())) {
                $payload->set('image_url', $media->getUrl());
                $payload->set('config', null);
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